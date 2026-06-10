<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <main class="mx-auto min-h-screen w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="brand-mark">PT</span>
                <span>
                    <span class="block text-sm font-bold">Private Transfer</span>
                    <span class="block text-xs text-slate-500">self-hosted secure file delivery</span>
                </span>
            </a>
            <div class="hidden gap-2 sm:flex">
                <span class="status-chip">No tracking</span>
                <span class="status-chip">Private storage</span>
                <span class="status-chip">7 day expiry</span>
            </div>
        </nav>

        <section class="grid w-full items-start gap-6 py-6 lg:min-h-[calc(100vh-5rem)] lg:grid-cols-[1fr_27rem] lg:items-center lg:gap-8 lg:py-8">
            <div class="max-w-3xl">
                <div class="inline-flex rounded-full border border-teal-200 bg-white/70 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800 shadow-sm">
                    Privacy-first transfer workspace
                </div>
                <h1 class="mt-4 text-4xl font-black leading-tight text-slate-950 sm:text-5xl lg:text-6xl">Send files with color, clarity, and control.</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                    Chunked uploads, automatic email delivery, private downloads, and a clean expiry policy. Keep this tab open while your upload runs.
                </p>

                <div class="mt-8 hidden gap-3 sm:grid sm:grid-cols-3">
                    <div class="rounded-lg border border-white/70 bg-white/70 p-4 shadow-sm">
                        <p class="text-2xl font-black text-teal-700">01</p>
                        <p class="mt-1 text-sm font-semibold">Drop files</p>
                        <p class="mt-1 text-xs text-slate-500">Large uploads are split into resumable chunks.</p>
                    </div>
                    <div class="rounded-lg border border-white/70 bg-white/70 p-4 shadow-sm">
                        <p class="text-2xl font-black text-rose-600">02</p>
                        <p class="mt-1 text-sm font-semibold">Add recipient</p>
                        <p class="mt-1 text-xs text-slate-500">Only required transfer metadata is stored.</p>
                    </div>
                    <div class="rounded-lg border border-white/70 bg-white/70 p-4 shadow-sm">
                        <p class="text-2xl font-black text-indigo-600">03</p>
                        <p class="mt-1 text-sm font-semibold">Mail sends itself</p>
                        <p class="mt-1 text-xs text-slate-500">The queue sends links after completion.</p>
                    </div>
                </div>

                <div data-resume-panel class="mt-5 hidden rounded-md border border-amber-200 bg-amber-50/90 p-4 text-sm text-amber-950 shadow-sm">
                    A previous upload was found. Select the same files again and start a new upload session to continue from the server progress.
                </div>
            </div>

            <form data-upload-form data-chunk-size="{{ $chunkSizeMb * 1024 * 1024 }}" class="soft-panel p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black">New transfer</h2>
                        <p class="text-sm text-slate-500">No accounts. No public file URLs.</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">Ready</span>
                </div>

                <div data-dropzone class="dropzone">
                    <input data-files class="sr-only" type="file" multiple>
                    <span class="grid size-14 place-items-center rounded-full bg-slate-950 text-3xl font-light text-white shadow-xl shadow-slate-900/20">+</span>
                    <p class="mt-3 font-bold">Choose or drop files</p>
                    <p class="mt-1 text-xs text-slate-500">Max. {{ $maxUploadMb }} MB per file</p>
                </div>

                <ul data-file-list class="mt-4 space-y-3"></ul>

                <label class="mt-4 block text-sm font-medium" for="recipient_email">Recipient email</label>
                <input id="recipient_email" name="recipient_email" type="email" required class="field" placeholder="recipient@example.com">

                <label class="mt-3 block text-sm font-medium" for="sender_email">Sender email optional</label>
                <input id="sender_email" name="sender_email" type="email" class="field" placeholder="you@example.com">

                <label class="mt-3 block text-sm font-medium" for="message">Message optional</label>
                <textarea id="message" name="message" rows="3" class="field resize-none" placeholder="A short note for the recipient"></textarea>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium" for="password">Password optional</label>
                        <input id="password" name="password" type="password" minlength="8" class="field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="max_downloads">Download limit optional</label>
                        <input id="max_downloads" name="max_downloads" type="number" min="1" class="field">
                    </div>
                </div>

                <div class="mt-5 h-3 rounded-full bg-slate-100 shadow-inner">
                    <div data-total-bar class="h-3 rounded-full bg-gradient-to-r from-teal-400 via-cyan-500 to-rose-400 transition-all" style="width:0%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span data-status>Ready.</span>
                    <span data-total-text>0%</span>
                </div>

                <button type="submit" class="primary-button mt-5">
                    Start transfer
                </button>
                <p class="mt-3 text-xs leading-5 text-slate-500">
                    Uploads continue while this tab remains open, even in the background. Browsers cannot guarantee progress after the tab or browser is closed.
                </p>
            </form>
        </section>
    </main>
</body>
</html>
