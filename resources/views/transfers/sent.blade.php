<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transfer bereit - {{ config('app.name', 'Private Transfer') }}</title>
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
            <p class="inline-flex rounded-full border border-teal-200 bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800" data-i18n="sentBadge">Transfer bereit</p>
            <h1 class="hero-gradient mt-4 text-4xl font-black" data-i18n="sentTitle">Upload abgeschlossen.</h1>
            <p class="mt-3 max-w-2xl text-sm text-slate-600" data-i18n="sentCopy">Die E-Mail wird automatisch versendet. Du kannst den Download-Link auch direkt kopieren.</p>

            <div class="mt-6 rounded-lg border border-white/70 bg-white/75 p-4 shadow-sm">
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400" for="download_link" data-i18n="downloadLink">Download-Link</label>
                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                    <input id="download_link" class="field mt-0" readonly value="{{ route('transfers.show', $transfer->public_token) }}">
                    <button type="button" class="toggle-bar justify-center px-4 py-3 text-sm font-bold" data-copy-link="{{ route('transfers.show', $transfer->public_token) }}" data-i18n="copyLink">Kopieren</button>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="recipientLabel">Empfaenger-E-Mail</p>
                    <p class="mt-1 truncate text-sm font-black">{{ $transfer->recipient_email }}</p>
                </div>
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="availableUntil">Verfuegbar bis</p>
                    <p class="mt-1 text-sm font-black">{{ $transfer->expires_at->format('d.m.Y') }}</p>
                </div>
                <div class="metric-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="downloadLimitLabel">Download-Limit optional</p>
                    <p class="mt-1 text-sm font-black">{{ $transfer->max_downloads ?? '∞' }}</p>
                </div>
            </div>

            <ul class="mt-6 divide-y divide-slate-100">
                @foreach ($transfer->files as $file)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate font-bold">{{ $file->original_name }}</p>
                            <p class="text-sm text-slate-500">{{ \Illuminate\Support\Number::fileSize($file->size) }}</p>
                        </div>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800" data-i18n="complete">Fertig</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </main>
</body>
</html>
