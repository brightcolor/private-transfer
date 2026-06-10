<?php

namespace App\Console\Commands;

use App\Models\Transfer;
use App\Models\TransferFile;
use Illuminate\Console\Command;

class TransferStorageStats extends Command
{
    protected $signature = 'transfers:stats';
    protected $description = 'Show transfer and storage statistics.';

    public function handle(): int
    {
        $bytes = (int) TransferFile::whereNotNull('upload_completed_at')->sum('size');

        $this->table(['Metric', 'Value'], [
            ['Transfers', Transfer::count()],
            ['Completed transfers', Transfer::where('status', Transfer::STATUS_COMPLETED)->count()],
            ['Files', TransferFile::count()],
            ['Stored bytes', $bytes],
            ['Stored MiB', number_format($bytes / 1024 / 1024, 2)],
        ]);

        return self::SUCCESS;
    }
}
