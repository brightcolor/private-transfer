<?php

namespace App\Mail;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferReadyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Transfer $transfer,
        public bool $copyForSender = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: config('app.name').' download link');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.transfer-ready');
    }
}
