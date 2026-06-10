<?php

namespace App\Mail;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferDownloadedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Transfer $transfer)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: config('app.name').' transfer downloaded');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.transfer-downloaded');
    }
}
