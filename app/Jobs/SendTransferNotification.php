<?php

namespace App\Jobs;

use App\Mail\TransferReadyMail;
use App\Models\Transfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendTransferNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $transferId)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $transfer = Transfer::with('files')->findOrFail($this->transferId);

        if ($transfer->notification_sent_at !== null || $transfer->status !== Transfer::STATUS_COMPLETED) {
            return;
        }

        Mail::to($transfer->recipient_email)->send(new TransferReadyMail($transfer));

        if (filled($transfer->sender_email)) {
            Mail::to($transfer->sender_email)->send(new TransferReadyMail($transfer, true));
        }

        $transfer->forceFill(['notification_sent_at' => now()])->save();
    }
}
