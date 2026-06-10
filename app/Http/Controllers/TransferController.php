<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\TransferFile;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class TransferController extends Controller
{
    public function __construct(private readonly TransferService $transfers)
    {
    }

    public function home(): View
    {
        return view('transfers.create', [
            'maxUploadMb' => config('transfer.max_upload_mb'),
            'chunkSizeMb' => config('transfer.chunk_size_mb'),
            'retentionDays' => config('transfer.retention_days'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxBytes = config('transfer.max_upload_mb') * 1024 * 1024;
        $data = $request->validate([
            'recipient_email' => ['required', 'email:rfc', 'max:254'],
            'sender_email' => ['nullable', 'email:rfc', 'max:254'],
            'message' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8', 'max:128'],
            'max_downloads' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:'.config('transfer.retention_days')],
            'files' => ['required', 'array', 'min:1', 'max:50'],
            'files.*.name' => ['required', 'string', 'max:255'],
            'files.*.size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
            'files.*.type' => ['nullable', 'string', 'max:255'],
        ]);

        $allowed = config('transfer.allowed_mime_types');
        if ($allowed !== []) {
            foreach ($data['files'] as $file) {
                abort_unless(in_array($file['type'] ?? '', $allowed, true), 422, 'A file type is not allowed.');
            }
        }

        $transfer = $this->transfers->create($data);

        return response()->json([
            'token' => $transfer->public_token,
            'expires_at' => $transfer->expires_at->toIso8601String(),
            'files' => $transfer->files->map(fn (TransferFile $file) => [
                'id' => $file->id,
                'name' => $file->original_name,
                'uploaded_size' => $file->uploaded_size,
            ]),
        ], 201);
    }

    public function uploadChunk(Request $request, TransferFile $file): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file', 'max:'.(config('transfer.chunk_size_mb') * 1024)],
        ]);

        abort_unless(hash_equals($file->transfer->public_token, $data['token']), 404);

        try {
            $file = $this->transfers->appendChunk($file, $data['chunk'], (int) $data['offset']);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage(), 'uploaded_size' => $file->fresh()->uploaded_size], 409);
        }

        return response()->json([
            'uploaded_size' => $file->uploaded_size,
            'complete' => $file->isComplete(),
        ]);
    }

    public function status(string $token): JsonResponse
    {
        $transfer = Transfer::with('files')->where('public_token', $token)->firstOrFail();

        return response()->json([
            'status' => $transfer->status,
            'completed' => $transfer->completed_at !== null,
            'files' => $transfer->files->map(fn (TransferFile $file) => [
                'id' => $file->id,
                'uploaded_size' => $file->uploaded_size,
                'complete' => $file->isComplete(),
            ]),
        ]);
    }

    public function show(string $token): View
    {
        $transfer = Transfer::with('files')->where('public_token', $token)->firstOrFail();

        return view('transfers.show', [
            'transfer' => $transfer,
            'locked' => $transfer->isPasswordProtected() && session('transfer_'.$transfer->id) !== true,
        ]);
    }

    public function sent(string $token): View
    {
        $transfer = Transfer::with('files')->where('public_token', $token)->firstOrFail();

        return view('transfers.sent', ['transfer' => $transfer]);
    }

    public function unlock(Request $request, string $token): RedirectResponse
    {
        $transfer = Transfer::where('public_token', $token)->firstOrFail();
        $data = $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($data['password'], (string) $transfer->password_hash)) {
            return back()->withErrors(['password' => 'The password is not correct.']);
        }

        session(['transfer_'.$transfer->id => true]);

        return back();
    }

    public function downloadFile(string $token, TransferFile $file): Response
    {
        $transfer = $this->downloadableTransfer($token, $file);

        $transfer->increment('download_count');

        if (Storage::disk(config('filesystems.default'))->getDriver()->getAdapter() instanceof \League\Flysystem\Local\LocalFilesystemAdapter) {
            return response()->download(Storage::disk(config('filesystems.default'))->path($file->storage_path), $file->original_name);
        }

        return response()->streamDownload(function () use ($file): void {
            $stream = Storage::disk(config('filesystems.default'))->readStream($file->storage_path);
            fpassthru($stream);
            is_resource($stream) && fclose($stream);
        }, $file->original_name);
    }

    public function downloadZip(string $token): StreamedResponse
    {
        $transfer = Transfer::with('files')->where('public_token', $token)->firstOrFail();
        abort_unless($transfer->isAvailable(), 404);
        abort_if($transfer->isPasswordProtected() && session('transfer_'.$transfer->id) !== true, 403);

        return response()->streamDownload(function () use ($transfer): void {
            $zipPath = tempnam(sys_get_temp_dir(), 'transfer-zip-');
            $tmpFiles = [];
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::OVERWRITE);

            foreach ($transfer->files as $file) {
                $stream = Storage::disk(config('filesystems.default'))->readStream($file->storage_path);
                $tmpFile = tempnam(sys_get_temp_dir(), 'transfer-file-');
                $tmpFiles[] = $tmpFile;
                file_put_contents($tmpFile, stream_get_contents($stream));
                is_resource($stream) && fclose($stream);
                $zip->addFile($tmpFile, $file->original_name);
            }

            $zip->close();
            readfile($zipPath);
            @unlink($zipPath);
            foreach ($tmpFiles as $tmpFile) {
                @unlink($tmpFile);
            }
        }, 'transfer-'.$transfer->public_token.'.zip');
    }

    private function downloadableTransfer(string $token, TransferFile $file): Transfer
    {
        $transfer = Transfer::where('public_token', $token)->firstOrFail();
        abort_unless($file->transfer_id === $transfer->id && $transfer->isAvailable(), 404);
        abort_if($transfer->isPasswordProtected() && session('transfer_'.$transfer->id) !== true, 403);
        abort_unless($file->storage_path && Storage::disk(config('filesystems.default'))->exists($file->storage_path), 404);

        return $transfer;
    }
}
