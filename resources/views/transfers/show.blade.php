<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download - {{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <main class="mx-auto min-h-screen max-w-4xl px-4 py-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <span class="brand-mark">PT</span>
            <span class="text-sm font-bold">Private Transfer</span>
        </a>

        <section class="soft-panel mt-8 p-6 sm:p-8">
            <div class="hero-media mb-6 h-36">
                <img src="{{ asset('images/transfer-hero.png') }}" alt="Abstract secure file transfer visual">
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-4xl font-black">Download</h1>
                    <p class="mt-2 text-sm text-slate-600">Available until {{ $transfer->expires_at->toDayDateTimeString() }}.</p>
                </div>
                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">{{ $transfer->status }}</span>
            </div>

            @if (! $transfer->isAvailable())
                <p class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">This transfer is no longer available.</p>
            @elseif ($locked)
                <form method="post" action="{{ route('transfers.unlock', $transfer->public_token) }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium" for="password">Password</label>
                    <input id="password" name="password" type="password" required class="field">
                    @error('password')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <button class="primary-button mt-4 max-w-xs">Unlock</button>
                </form>
            @else
                @if ($transfer->message)
                    <blockquote class="mt-6 rounded-md border border-teal-100 bg-teal-50/70 p-4 text-sm text-slate-700">{{ $transfer->message }}</blockquote>
                @endif

                <ul class="mt-6 divide-y divide-slate-100">
                    @foreach ($transfer->files as $file)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $file->original_name }}</p>
                                <p class="text-sm text-slate-500">{{ \Illuminate\Support\Number::fileSize($file->size) }}</p>
                            </div>
                            <a class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-bold shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md" href="{{ route('transfers.files.download', [$transfer->public_token, $file]) }}">Download</a>
                        </li>
                    @endforeach
                </ul>

                <a class="primary-button mt-6" href="{{ route('transfers.zip', $transfer->public_token) }}">
                    Download all as ZIP
                </a>
            @endif
        </section>
    </main>
</body>
</html>
