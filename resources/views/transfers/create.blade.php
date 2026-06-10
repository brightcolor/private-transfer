<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <main class="mx-auto min-h-screen w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <nav class="nav-shell flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="brand-mark">PT</span>
                <span>
                    <span class="block text-sm font-bold">Private Transfer</span>
                    <span class="block text-xs text-slate-500" data-i18n="brandSubtitle">selbst gehostete sichere Dateiuebertragung</span>
                </span>
            </a>
            <div class="flex items-center gap-2">
                <div class="hidden gap-2 lg:flex">
                    <span class="status-chip" data-i18n="chipTracking">Kein Tracking</span>
                    <span class="status-chip" data-i18n="chipStorage">Privater Speicher</span>
                    <span class="status-chip" data-i18n="chipExpiry">7 Tage Ablauf</span>
                </div>
                <div class="toggle-bar" aria-label="Language">
                    <button type="button" class="toggle-button" data-language-option="de">DE</button>
                    <button type="button" class="toggle-button" data-language-option="en">EN</button>
                </div>
                <button type="button" class="toggle-button toggle-bar px-3 py-2" data-theme-toggle aria-label="Toggle dark mode">
                    <span data-theme-label>Dark</span>
                </button>
            </div>
        </nav>

        <section class="grid w-full items-start gap-6 py-6 lg:min-h-[calc(100vh-5rem)] lg:grid-cols-[1fr_27rem] lg:items-center lg:gap-8 lg:py-8">
            <div class="max-w-3xl">
                <div class="inline-flex rounded-full border border-teal-200 bg-white/70 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800 shadow-sm" data-i18n="eyebrow">
                    Datenschutzfreundlicher Transfer-Workspace
                </div>
                <h1 class="hero-gradient mt-4 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl" data-i18n="heroTitle">Dateien einfach und privat versenden.</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8" data-i18n="heroCopy">
                    Lade Dateien hoch, teile den Download-Link automatisch per E-Mail und behalte Ablaufzeit, Passwort und Download-Limit im Griff.
                </p>

                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="mini-stat"><span class="block text-teal-700" data-i18n="miniResumeTitle">Fortsetzen</span><span data-i18n="miniResumeText">Chunks</span></div>
                    <div class="mini-stat"><span class="block text-rose-600" data-i18n="miniPrivateTitle">Privat</span><span data-i18n="miniPrivateText">Links</span></div>
                    <div class="mini-stat"><span class="block text-indigo-600" data-i18n="miniQueuedTitle">Queue</span><span data-i18n="miniQueuedText">Mail</span></div>
                </div>

                <div class="hero-media mt-5 h-28 sm:h-40">
                    <img src="{{ asset('images/transfer-hero.png') }}" alt="Abstract secure file transfer visual">
                </div>

                <div class="mt-8 hidden gap-3 sm:grid sm:grid-cols-3">
                    <div class="feature-card">
                        <p class="text-2xl font-black text-teal-700">01</p>
                        <p class="mt-1 text-sm font-semibold" data-i18n="featureFilesTitle">Dateien ablegen</p>
                        <p class="mt-1 text-xs text-slate-500" data-i18n="featureFilesCopy">Grosse Uploads werden in fortsetzbare Chunks geteilt.</p>
                    </div>
                    <div class="feature-card">
                        <p class="text-2xl font-black text-rose-600">02</p>
                        <p class="mt-1 text-sm font-semibold" data-i18n="featureRecipientTitle">Empfaenger eintragen</p>
                        <p class="mt-1 text-xs text-slate-500" data-i18n="featureRecipientCopy">Gespeichert werden nur notwendige Transferdaten.</p>
                    </div>
                    <div class="feature-card">
                        <p class="text-2xl font-black text-indigo-600">03</p>
                        <p class="mt-1 text-sm font-semibold" data-i18n="featureMailTitle">Mail geht automatisch raus</p>
                        <p class="mt-1 text-xs text-slate-500" data-i18n="featureMailCopy">Der Queue-Worker sendet Links nach Abschluss.</p>
                    </div>
                </div>

                <div data-resume-panel class="mt-5 hidden rounded-md border border-amber-200 bg-amber-50/90 p-4 text-sm text-amber-950 shadow-sm" data-i18n="resumePanel">
                    Ein vorheriger Upload wurde gefunden. Waehle dieselben Dateien erneut aus und starte die Session, um ab dem Server-Fortschritt fortzufahren.
                </div>
            </div>

            <form data-upload-form data-chunk-size="{{ $chunkSizeMb * 1024 * 1024 }}" class="soft-panel transfer-panel relative overflow-hidden p-5">
                <span class="orbital-accent"></span>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black" data-i18n="formTitle">Neuer Transfer</h2>
                        <p class="text-sm text-slate-500" data-i18n="formSubtitle">Keine Accounts. Keine oeffentlichen Datei-URLs.</p>
                    </div>
                    <span class="rounded-full border border-teal-200 bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800 shadow-sm" data-i18n="ready">Bereit</span>
                </div>

                <div class="premium-divider my-4"></div>

                <div data-dropzone class="dropzone">
                    <input data-files class="sr-only" type="file" multiple>
                    <span class="grid size-14 place-items-center rounded-full bg-slate-950 text-3xl font-light text-white shadow-xl shadow-slate-900/20">+</span>
                    <p class="mt-3 font-bold" data-i18n="dropTitle">Dateien auswaehlen oder ablegen</p>
                    <p class="mt-1 text-xs text-slate-500"><span data-i18n="dropLimitPrefix">Max.</span> {{ $maxUploadMb }} MB <span data-i18n="dropLimitSuffix">pro Datei</span></p>
                </div>

                <ul data-file-list class="mt-4 space-y-3"></ul>

                <label class="mt-4 block text-sm font-medium" for="recipient_email" data-i18n="recipientLabel">Empfaenger-E-Mail</label>
                <input id="recipient_email" name="recipient_email" type="email" required class="field" placeholder="empfaenger@example.com" data-i18n-placeholder="recipientPlaceholder">

                <label class="mt-3 block text-sm font-medium" for="sender_email" data-i18n="senderLabel">Absender-E-Mail optional</label>
                <input id="sender_email" name="sender_email" type="email" class="field" placeholder="du@example.com" data-i18n-placeholder="senderPlaceholder">

                <label class="mt-3 block text-sm font-medium" for="message" data-i18n="messageLabel">Nachricht optional</label>
                <textarea id="message" name="message" rows="3" class="field resize-none" placeholder="Eine kurze Nachricht fuer den Empfaenger" data-i18n-placeholder="messagePlaceholder"></textarea>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium" for="password" data-i18n="passwordLabel">Passwort optional</label>
                        <input id="password" name="password" type="password" minlength="8" class="field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="max_downloads" data-i18n="downloadLimitLabel">Download-Limit optional</label>
                        <input id="max_downloads" name="max_downloads" type="number" min="1" class="field">
                    </div>
                </div>

                <label class="mt-3 block text-sm font-medium" for="retention_days" data-i18n="retentionLabel">Aufbewahrung</label>
                <select id="retention_days" name="retention_days" class="field">
                    @foreach ([1, 3, 7, 14, 30] as $days)
                        @if ($days <= $retentionDays)
                            <option value="{{ $days }}" @selected($days === min(7, $retentionDays))>{{ $days }} {{ $days === 1 ? 'Tag' : 'Tage' }}</option>
                        @endif
                    @endforeach
                </select>

                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="metric-card">
                        <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricProgress">Fortschritt</p>
                        <p data-total-text class="metric-value text-slate-900">0%</p>
                    </div>
                    <div class="metric-card">
                        <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricSpeed">Speed</p>
                        <p data-speed-text class="metric-value text-teal-700" data-i18n="idle">Wartet</p>
                    </div>
                    <div class="metric-card">
                        <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400" data-i18n="metricEta">Restzeit</p>
                        <p data-eta-text class="metric-value text-rose-700">--</p>
                    </div>
                </div>

                <div class="progress-track mt-3 ring-1 ring-white/70 dark:ring-white/10">
                    <div data-total-bar class="progress-fill" style="width:0%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span data-status data-i18n="readyStatus">Bereit.</span>
                    <span data-uploaded-text data-i18n="uploadedIdle">0 B hochgeladen</span>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-[1fr_auto]">
                    <button type="submit" class="primary-button" data-i18n="startTransfer">
                        Transfer starten
                    </button>
                    <button type="button" class="toggle-bar hidden justify-center px-4 py-3 text-sm font-bold" data-pause-upload data-i18n="pauseUpload">
                        Pausieren
                    </button>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500" data-i18n="browserLimit">
                    Uploads laufen weiter, solange dieser Tab offen bleibt, auch im Hintergrund. Browser koennen keinen Fortschritt garantieren, wenn Tab oder Browser geschlossen werden.
                </p>
            </form>
        </section>
    </main>
</body>
</html>
