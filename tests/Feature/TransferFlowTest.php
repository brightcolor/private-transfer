<?php

namespace Tests\Feature;

use App\Jobs\SendTransferNotification;
use App\Jobs\SendDownloadNotification;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransferFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_can_be_created_and_completed_with_chunks(): void
    {
        Queue::fake();
        Storage::fake('local');

        $create = $this->postJson('/transfers', [
            'recipient_email' => 'recipient@example.com',
            'retention_days' => 1,
            'files' => [
                ['name' => 'report.txt', 'size' => 11, 'type' => 'text/plain'],
            ],
        ])->assertCreated();

        $fileId = $create->json('files.0.id');
        $token = $create->json('token');

        $this->post('/uploads/'.$fileId.'/chunks', [
            'token' => $token,
            'offset' => 0,
            'chunk' => UploadedFile::fake()->createWithContent('chunk.txt', 'hello '),
        ])->assertOk()->assertJson(['complete' => false]);

        $this->post('/uploads/'.$fileId.'/chunks', [
            'token' => $token,
            'offset' => 6,
            'chunk' => UploadedFile::fake()->createWithContent('chunk.txt', 'world'),
        ])->assertOk()->assertJson(['complete' => true]);

        $transfer = Transfer::with('files')->where('public_token', $token)->firstOrFail();

        $this->assertSame(Transfer::STATUS_COMPLETED, $transfer->status);
        $this->assertTrue($transfer->files->first()->isComplete());
        $this->assertTrue($transfer->expires_at->isBefore(now()->addDays(2)));
        $this->get('/t/'.$token.'/sent')->assertOk()->assertSee('Upload abgeschlossen');
        Queue::assertPushed(SendTransferNotification::class);
    }

    public function test_stale_chunk_offset_is_rejected_with_current_offset(): void
    {
        Storage::fake('local');

        $create = $this->postJson('/transfers', [
            'recipient_email' => 'recipient@example.com',
            'files' => [
                ['name' => 'report.txt', 'size' => 10, 'type' => 'text/plain'],
            ],
        ]);

        $fileId = $create->json('files.0.id');
        $token = $create->json('token');

        $this->post('/uploads/'.$fileId.'/chunks', [
            'token' => $token,
            'offset' => 0,
            'chunk' => UploadedFile::fake()->createWithContent('chunk.txt', 'hello'),
        ])->assertOk();

        $this->post('/uploads/'.$fileId.'/chunks', [
            'token' => $token,
            'offset' => 0,
            'chunk' => UploadedFile::fake()->createWithContent('chunk.txt', 'again'),
        ])->assertStatus(409)->assertJson(['uploaded_size' => 5]);
    }

    public function test_password_protected_transfer_requires_unlock(): void
    {
        Storage::fake('local');

        $transfer = Transfer::create([
            'public_token' => 'protected-token',
            'recipient_email' => 'recipient@example.com',
            'password_hash' => Hash::make('correct-password'),
            'expires_at' => now()->addDay(),
            'completed_at' => now(),
            'status' => Transfer::STATUS_COMPLETED,
        ]);

        $this->get('/t/'.$transfer->public_token)->assertOk()->assertSee('Passwort');
        $this->post('/t/'.$transfer->public_token.'/unlock', ['password' => 'wrong-password'])->assertSessionHasErrors('password');
    }

    public function test_sender_can_request_download_notification(): void
    {
        Queue::fake();
        Storage::fake('local');

        $transfer = Transfer::create([
            'public_token' => 'download-notify-token',
            'sender_email' => 'sender@example.com',
            'recipient_email' => 'recipient@example.com',
            'notify_sender_on_download' => true,
            'expires_at' => now()->addDay(),
            'completed_at' => now(),
            'status' => Transfer::STATUS_COMPLETED,
        ]);

        $file = $transfer->files()->create([
            'original_name' => 'report.txt',
            'storage_path' => 'transfers/download-notify-token/report.txt',
            'mime_type' => 'text/plain',
            'size' => 5,
            'uploaded_size' => 5,
            'upload_completed_at' => now(),
        ]);

        Storage::disk('local')->put($file->storage_path, 'hello');

        $this->get('/t/'.$transfer->public_token.'/files/'.$file->id)->assertOk();

        Queue::assertPushed(SendDownloadNotification::class);
    }
}
