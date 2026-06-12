<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download - {{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <main class="mx-auto min-h-screen max-w-4xl px-4 py-8">
        <nav class="nav-shell flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="brand-mark">PT</span>
                <span class="text-sm font-bold">Private Transfer</span>
            </a>
            <div class="flex items-center gap-2">
                <div class="toggle-bar" aria-label="Language">
                    <button type="button" class="toggle-button" data-language-option="de">DE</button>
                    <button type="button" class="toggle-button" data-language-option="en">EN</button>
                </div>
                <button type="button" class="toggle-button toggle-bar px-3 py-2" data-theme-toggle aria-label="Toggle dark mode">
                    <span data-theme-label>Dark</span>
                </button>
            </div>
        </nav>

        <section class="soft-panel relative mt-8 overflow-hidden p-6 sm:p-8">
            <span class="orbital-accent"></span>
            <div class="hero-media mb-6 h-36">
                <img src="{{ asset('images/transfer-hero.png') }}" alt="Abstract secure file transfer visual">
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-4xl font-black" data-i18n="downloadTitle">Download</h1>
                    <p class="mt-2 text-sm text-slate-600"><span data-i18n="availableUntil">Verfuegbar bis</span> {{ $transfer->expires_at->toDayDateTimeString() }}.</p>
                </div>
                <span class="pill-success">{{ $transfer->status }}</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricFiles">Dateien</p>
                    <p class="mt-1 text-sm font-black">{{ $transfer->files->count() }}</p>
                </div>
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricSize">Groesse</p>
                    <p class="mt-1 text-sm font-black">{{ \Illuminate\Support\Number::fileSize($transfer->files->sum('size')) }}</p>
                </div>
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricDownloads">Downloads</p>
                    <p class="mt-1 text-sm font-black">{{ $transfer->download_count }} / {{ $transfer->max_downloads ?? '∞' }}</p>
                </div>
            </div>

            @if (! $transfer->isAvailable())
                <p class="notice-danger mt-6" data-i18n="notAvailable">Dieser Transfer ist nicht mehr verfuegbar.</p>
            @elseif ($locked)
                <form method="post" action="{{ route('transfers.unlock', $transfer->public_token) }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium" for="password" data-i18n="password">Passwort</label>
                    <input id="password" name="password" type="password" required class="field">
                    @error('password')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <button class="primary-button mt-4 max-w-xs" data-i18n="unlock">Entsperren</button>
                </form>
            @else
                @if ($transfer->message)
                    <blockquote class="notice-info mt-6">{{ $transfer->message }}</blockquote>
                @endif

                <ul class="mt-6 space-y-3">
                    @foreach ($transfer->files as $file)
                        <li class="file-row">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="file-badge">FILE</span>
                                <div class="min-w-0">
                                <p class="truncate font-bold">{{ $file->original_name }}</p>
                                <p class="text-sm text-slate-500">{{ \Illuminate\Support\Number::fileSize($file->size) }}</p>
                                </div>
                            </div>
                            <a class="secondary-button" href="{{ route('transfers.files.download', [$transfer->public_token, $file]) }}" data-i18n="downloadFile">Download</a>
                        </li>
                    @endforeach
                </ul>

                <a class="primary-button mt-6" href="{{ route('transfers.zip', $transfer->public_token) }}" data-i18n="downloadZip">
                    Alles als ZIP herunterladen
                </a>
            @endif
        </section>
    </main>
</body>
</html>
