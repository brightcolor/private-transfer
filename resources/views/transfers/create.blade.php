<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Private Transfer') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-8">
        <section class="grid w-full gap-6 lg:grid-cols-[1fr_22rem]">
            <div class="flex flex-col justify-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Private Transfer</p>
                <h1 class="mt-3 max-w-3xl text-4xl font-semibold text-slate-950 sm:text-5xl">Send files privately, without tracking.</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                    Files are uploaded in resumable chunks and sent by email after the upload is complete. Keep the browser tab open while the transfer runs.
                </p>
                <div data-resume-panel class="mt-5 hidden rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    A previous upload was found. Select the same files again and start a new upload session to continue safely where the server reports progress.
                </div>
            </div>

            <form data-upload-form data-chunk-size="{{ $chunkSizeMb * 1024 * 1024 }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div data-dropzone class="flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition">
                    <input data-files class="sr-only" type="file" multiple>
                    <span class="text-3xl">+</span>
                    <p class="mt-2 font-medium">Choose or drop files</p>
                    <p class="mt-1 text-xs text-slate-500">Max. {{ $maxUploadMb }} MB pro Datei</p>
                </div>

                <ul data-file-list class="mt-4 space-y-3"></ul>

                <label class="mt-4 block text-sm font-medium" for="recipient_email">Recipient email</label>
                <input id="recipient_email" name="recipient_email" type="email" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-teal-500 focus:outline-none">

                <label class="mt-3 block text-sm font-medium" for="sender_email">Sender email optional</label>
                <input id="sender_email" name="sender_email" type="email" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-teal-500 focus:outline-none">

                <label class="mt-3 block text-sm font-medium" for="message">Message optional</label>
                <textarea id="message" name="message" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-teal-500 focus:outline-none"></textarea>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium" for="password">Password optional</label>
                        <input id="password" name="password" type="password" minlength="8" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="max_downloads">Download limit optional</label>
                        <input id="max_downloads" name="max_downloads" type="number" min="1" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div class="mt-5 h-2 rounded-full bg-slate-100">
                    <div data-total-bar class="h-2 rounded-full bg-teal-500 transition-all" style="width:0%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span data-status>Ready.</span>
                    <span data-total-text>0%</span>
                </div>

                <button type="submit" class="mt-5 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400">
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
