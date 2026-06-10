<?php

namespace App\Jobs;

use App\Mail\TransferDownloadedMail;
use App\Models\Transfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDownloadNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $transferId)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $transfer = Transfer::with('files')->findOrFail($this->transferId);

        if (
            ! $transfer->notify_sender_on_download
            || blank($transfer->sender_email)
            || $transfer->download_notification_sent_at !== null
        ) {
            return;
        }

        Mail::to($transfer->sender_email)->send(new TransferDownloadedMail($transfer));

        $transfer->forceFill(['download_notification_sent_at' => now()])->save();
    }
}
