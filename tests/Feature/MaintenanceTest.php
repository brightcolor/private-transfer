<?php

namespace Tests\Feature;

use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_marks_expired_transfer_deleted(): void
    {
        Storage::fake('local');

        Transfer::create([
            'public_token' => 'expired-token',
            'recipient_email' => 'recipient@example.com',
            'expires_at' => now()->subDay(),
            'status' => Transfer::STATUS_COMPLETED,
        ]);

        $this->artisan('transfers:cleanup')->assertSuccessful();

        $this->assertDatabaseMissing('transfers', ['public_token' => 'expired-token']);
    }
}
