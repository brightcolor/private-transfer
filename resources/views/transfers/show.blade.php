<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download - {{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="mx-auto min-h-screen max-w-3xl px-4 py-10">
        <a href="{{ route('home') }}" class="text-sm font-medium text-teal-700">Private Transfer</a>
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Download</h1>
                    <p class="mt-2 text-sm text-slate-600">Available until {{ $transfer->expires_at->toDayDateTimeString() }}.</p>
                </div>
                <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700">{{ $transfer->status }}</span>
            </div>

            @if (! $transfer->isAvailable())
                <p class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">This transfer is no longer available.</p>
            @elseif ($locked)
                <form method="post" action="{{ route('transfers.unlock', $transfer->public_token) }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium" for="password">Password</label>
                    <input id="password" name="password" type="password" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    @error('password')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <button class="mt-4 rounded-md bg-slate-950 px-4 py-2 font-medium text-white">Unlock</button>
                </form>
            @else
                @if ($transfer->message)
                    <blockquote class="mt-6 rounded-md bg-slate-50 p-4 text-sm text-slate-700">{{ $transfer->message }}</blockquote>
                @endif

                <ul class="mt-6 divide-y divide-slate-100">
                    @foreach ($transfer->files as $file)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $file->original_name }}</p>
                                <p class="text-sm text-slate-500">{{ \Illuminate\Support\Number::fileSize($file->size) }}</p>
                            </div>
                            <a class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50" href="{{ route('transfers.files.download', [$transfer->public_token, $file]) }}">Download</a>
                        </li>
                    @endforeach
                </ul>

                <a class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-slate-950 px-4 py-3 font-medium text-white hover:bg-slate-800" href="{{ route('transfers.zip', $transfer->public_token) }}">
                    Download all as ZIP
                </a>
            @endif
        </section>
    </main>
</body>
</html>
