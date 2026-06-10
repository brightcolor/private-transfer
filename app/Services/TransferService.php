<?php

namespace App\Services;

use App\Jobs\SendTransferNotification;
use App\Models\Transfer;
use App\Models\TransferFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class TransferService
{
    public function create(array $data): Transfer
    {
        return DB::transaction(function () use ($data): Transfer {
            $transfer = Transfer::create([
                'public_token' => Str::random(48),
                'sender_email' => $data['sender_email'] ?? null,
                'recipient_email' => $data['recipient_email'],
                'message' => $data['message'] ?? null,
                'password_hash' => filled($data['password'] ?? null) ? Hash::make($data['password']) : null,
                'max_downloads' => $data['max_downloads'] ?? null,
                'expires_at' => now()->addDays(config('transfer.retention_days')),
                'status' => Transfer::STATUS_PENDING,
            ]);

            foreach ($data['files'] as $file) {
                $transfer->files()->create([
                    'original_name' => basename($file['name']),
                    'mime_type' => $file['type'] ?? null,
                    'size' => (int) $file['size'],
                ]);
            }

            return $transfer->load('files');
        });
    }

    public function appendChunk(TransferFile $file, UploadedFile $chunk, int $offset): TransferFile
    {
        return DB::transaction(function () use ($file, $chunk, $offset): TransferFile {
            $file->refresh();
            $transfer = $file->transfer()->lockForUpdate()->firstOrFail();

            if (! in_array($transfer->status, [Transfer::STATUS_PENDING, Transfer::STATUS_UPLOADING], true)) {
                throw new RuntimeException('This transfer does not accept more chunks.');
            }

            if ($offset !== (int) $file->uploaded_size) {
                throw new RuntimeException('Upload offset does not match the server state.');
            }

        $tmpDisk = Storage::disk('local');
        $tmpPath = $this->temporaryPath($file);
        $tmpDisk->makeDirectory(dirname($tmpPath));

        $read = fopen($chunk->getRealPath(), 'rb');
        $write = fopen($tmpDisk->path($tmpPath), $offset === 0 ? 'wb' : 'ab');

            if ($read === false || $write === false) {
                throw new RuntimeException('Could not write upload chunk.');
            }

            stream_copy_to_stream($read, $write);
            fclose($read);
            fclose($write);

            $uploadedSize = min($file->size, $offset + $chunk->getSize());
            $file->forceFill(['uploaded_size' => $uploadedSize])->save();
            $transfer->forceFill(['status' => Transfer::STATUS_UPLOADING])->save();

            if ($uploadedSize >= $file->size) {
                $this->completeFile($file);
                $this->completeTransferIfReady($transfer);
            }

            return $file->fresh();
        });
    }

    public function completeTransferIfReady(Transfer $transfer): void
    {
        $transfer->load('files');

        if ($transfer->files->every(fn (TransferFile $file): bool => $file->isComplete())) {
            $transfer->forceFill([
                'completed_at' => now(),
                'status' => Transfer::STATUS_COMPLETED,
            ])->save();

            SendTransferNotification::dispatch($transfer->id);
        }
    }

    private function completeFile(TransferFile $file): void
    {
        $tmpDisk = Storage::disk('local');
        $finalDisk = Storage::disk(config('filesystems.default'));
        $tmpPath = $this->temporaryPath($file);
        $finalPath = 'transfers/'.$file->transfer->public_token.'/'.Str::uuid()->toString();

        if (! $tmpDisk->exists($tmpPath)) {
            throw new RuntimeException('Uploaded chunks are missing.');
        }

        $stream = fopen($tmpDisk->path($tmpPath), 'rb');
        $finalDisk->put($finalPath, $stream);
        is_resource($stream) && fclose($stream);

        $file->forceFill([
            'storage_path' => $finalPath,
            'checksum' => hash_file('sha256', $tmpDisk->path($tmpPath)),
            'upload_completed_at' => now(),
        ])->save();

        $tmpDisk->delete($tmpPath);
    }

    private function temporaryPath(TransferFile $file): string
    {
        return 'chunks/'.$file->transfer->public_token.'/'.$file->id.'.part';
    }
}
