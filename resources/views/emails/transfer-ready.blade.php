<x-mail::message>
# Your transfer is ready

@if ($copyForSender)
This is your copy of the transfer notification.
@else
Someone sent you files through {{ config('app.name') }}.
@endif

@if ($transfer->message)
> {{ $transfer->message }}
@endif

<x-mail::button :url="route('transfers.show', $transfer->public_token)">
Open download page
</x-mail::button>

Expires: {{ $transfer->expires_at->toDayDateTimeString() }}

@foreach ($transfer->files as $file)
- {{ $file->original_name }} ({{ \Illuminate\Support\Number::fileSize($file->size) }})
@endforeach

No account is required. The operator of this instance controls retention and privacy notices.
</x-mail::message>
