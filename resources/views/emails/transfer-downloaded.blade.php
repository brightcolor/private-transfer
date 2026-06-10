<x-mail::message>
# Your transfer was downloaded

A transfer you sent through {{ config('app.name') }} has been downloaded.

<x-mail::panel>
Recipient: {{ $transfer->recipient_email }}

Downloads: {{ $transfer->download_count }}

Expires: {{ $transfer->expires_at->toDayDateTimeString() }}
</x-mail::panel>

@foreach ($transfer->files as $file)
- {{ $file->original_name }} ({{ \Illuminate\Support\Number::fileSize($file->size) }})
@endforeach

This notification contains no IP address or tracking data.
</x-mail::message>
