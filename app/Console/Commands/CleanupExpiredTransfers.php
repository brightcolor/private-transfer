<?php

namespace App\Console\Commands;

use App\Models\Transfer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredTransfers extends Command
{
    protected $signature = 'transfers:cleanup {--dry-run}';
    protected $description = 'Delete expired transfers and stale incomplete upload chunks.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;

        Transfer::with('files')
            ->where('expires_at', '<', now())
            ->orWhere(fn ($query) => $query
                ->whereIn('status', [Transfer::STATUS_PENDING, Transfer::STATUS_UPLOADING])
                ->where('updated_at', '<', now()->subHours(config('transfer.cleanup_incomplete_hours'))))
            ->chunkById(50, function ($transfers) use ($dryRun, &$deleted): void {
                foreach ($transfers as $transfer) {
                    if (! $dryRun) {
                        Storage::disk(config('filesystems.default'))->deleteDirectory('transfers/'.$transfer->public_token);
                        Storage::disk('local')->deleteDirectory('chunks/'.$transfer->public_token);
                        $transfer->forceFill(['status' => Transfer::STATUS_DELETED])->save();
                        $transfer->delete();
                    }

                    $deleted++;
                }
            });

        $this->info(($dryRun ? 'Would delete ' : 'Deleted ').$deleted.' transfer(s).');

        return self::SUCCESS;
    }
}
