const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const uploadForm = document.querySelector('[data-upload-form]');
const translations = {
    de: {
        availableUntil: 'Verfuegbar bis',
        brandSubtitle: 'selbst gehostete sichere Dateiuebertragung',
        browserLimit: 'Uploads laufen weiter, solange dieser Tab offen bleibt, auch im Hintergrund. Browser koennen keinen Fortschritt garantieren, wenn Tab oder Browser geschlossen werden.',
        chipExpiry: '7 Tage Ablauf',
        chipStorage: 'Privater Speicher',
        chipTracking: 'Kein Tracking',
        complete: 'Fertig',
        dark: 'Dark',
        downloadFile: 'Download',
        downloadLimitLabel: 'Download-Limit optional',
        downloadTitle: 'Download',
        downloadZip: 'Alles als ZIP herunterladen',
        dropLimitPrefix: 'Max.',
        dropLimitSuffix: 'pro Datei',
        dropTitle: 'Dateien auswaehlen oder ablegen',
        emptyFiles: 'Bitte waehle mindestens eine Datei aus.',
        eyebrow: 'Datenschutzfreundlicher Transfer-Workspace',
        featureFilesCopy: 'Grosse Uploads werden in fortsetzbare Chunks geteilt.',
        featureFilesTitle: 'Dateien ablegen',
        featureMailCopy: 'Der Queue-Worker sendet Links nach Abschluss.',
        featureMailTitle: 'Mail geht automatisch raus',
        featureRecipientCopy: 'Gespeichert werden nur notwendige Transferdaten.',
        featureRecipientTitle: 'Empfaenger eintragen',
        fileBadge: 'DATEI',
        formSubtitle: 'Keine Accounts. Keine oeffentlichen Datei-URLs.',
        formTitle: 'Neuer Transfer',
        heroCopy: 'Chunked Uploads, automatischer Mailversand, private Downloads und eine klare Ablaufzeit. Lass diesen Tab offen, waehrend der Upload laeuft.',
        heroTitle: 'Dateien senden mit Farbe, Klarheit und Kontrolle.',
        idle: 'Wartet',
        light: 'Light',
        maxDownloadsPlaceholder: '',
        messageLabel: 'Nachricht optional',
        messagePlaceholder: 'Eine kurze Nachricht fuer den Empfaenger',
        metricEta: 'Restzeit',
        metricProgress: 'Fortschritt',
        metricSpeed: 'Speed',
        miniPrivateText: 'Links',
        miniPrivateTitle: 'Privat',
        miniQueuedText: 'Mail',
        miniQueuedTitle: 'Queue',
        miniResumeText: 'Chunks',
        miniResumeTitle: 'Fortsetzen',
        notAvailable: 'Dieser Transfer ist nicht mehr verfuegbar.',
        password: 'Passwort',
        passwordLabel: 'Passwort optional',
        preparing: 'Transfer wird vorbereitet...',
        ready: 'Bereit',
        readyStatus: 'Bereit.',
        recipientLabel: 'Empfaenger-E-Mail',
        recipientPlaceholder: 'empfaenger@example.com',
        resumePanel: 'Ein vorheriger Upload wurde gefunden. Waehle dieselben Dateien erneut aus und starte die Session, um ab dem Server-Fortschritt fortzufahren.',
        resumeStatus: 'Gespeicherter Upload-Fortschritt wird geprueft...',
        senderLabel: 'Absender-E-Mail optional',
        senderPlaceholder: 'du@example.com',
        startTransfer: 'Transfer starten',
        starting: 'Startet',
        unlock: 'Entsperren',
        uploadComplete: 'Upload fertig. Die E-Mail wird automatisch versendet.',
        uploadedIdle: '0 B hochgeladen',
        uploadedOf: 'hochgeladen',
        uploading: 'Upload laeuft. Lass diesen Tab offen; der Upload kann im Hintergrund weiterlaufen, solange der Tab offen bleibt.',
        waiting: 'Wartet',
    },
    en: {
        availableUntil: 'Available until',
        brandSubtitle: 'self-hosted secure file delivery',
        browserLimit: 'Uploads continue while this tab remains open, even in the background. Browsers cannot guarantee progress after the tab or browser is closed.',
        chipExpiry: '7 day expiry',
        chipStorage: 'Private storage',
        chipTracking: 'No tracking',
        complete: 'Complete',
        dark: 'Dark',
        downloadFile: 'Download',
        downloadLimitLabel: 'Download limit optional',
        downloadTitle: 'Download',
        downloadZip: 'Download all as ZIP',
        dropLimitPrefix: 'Max.',
        dropLimitSuffix: 'per file',
        dropTitle: 'Choose or drop files',
        emptyFiles: 'Please choose at least one file.',
        eyebrow: 'Privacy-first transfer workspace',
        featureFilesCopy: 'Large uploads are split into resumable chunks.',
        featureFilesTitle: 'Drop files',
        featureMailCopy: 'The queue sends links after completion.',
        featureMailTitle: 'Mail sends itself',
        featureRecipientCopy: 'Only required transfer metadata is stored.',
        featureRecipientTitle: 'Add recipient',
        fileBadge: 'FILE',
        formSubtitle: 'No accounts. No public file URLs.',
        formTitle: 'New transfer',
        heroCopy: 'Chunked uploads, automatic email delivery, private downloads, and a clean expiry policy. Keep this tab open while your upload runs.',
        heroTitle: 'Send files with color, clarity, and control.',
        idle: 'Idle',
        light: 'Light',
        messageLabel: 'Message optional',
        messagePlaceholder: 'A short note for the recipient',
        metricEta: 'ETA',
        metricProgress: 'Progress',
        metricSpeed: 'Speed',
        miniPrivateText: 'links',
        miniPrivateTitle: 'Private',
        miniQueuedText: 'mail',
        miniQueuedTitle: 'Queued',
        miniResumeText: 'chunks',
        miniResumeTitle: 'Resume',
        notAvailable: 'This transfer is no longer available.',
        password: 'Password',
        passwordLabel: 'Password optional',
        preparing: 'Preparing transfer...',
        ready: 'Ready',
        readyStatus: 'Ready.',
        recipientLabel: 'Recipient email',
        recipientPlaceholder: 'recipient@example.com',
        resumePanel: 'A previous upload was found. Select the same files again and start a new upload session to continue from the server progress.',
        resumeStatus: 'Checking saved upload progress...',
        senderLabel: 'Sender email optional',
        senderPlaceholder: 'you@example.com',
        startTransfer: 'Start transfer',
        starting: 'Starting',
        unlock: 'Unlock',
        uploadComplete: 'Upload complete. The email is being sent automatically.',
        uploadedIdle: '0 B uploaded',
        uploadedOf: 'uploaded',
        uploading: 'Uploading. Keep this tab open; the upload can continue in the background while the tab remains open.',
        waiting: 'Waiting',
    },
};

let currentLanguage = localStorage.getItem('private-transfer-language') || 'de';
let currentTheme = localStorage.getItem('private-transfer-theme') || 'light';

const t = (key) => translations[currentLanguage]?.[key] || translations.de[key] || key;

const revealText = (element, value) => {
    if (!element || element.textContent === value) {
        return;
    }

    element.textContent = value;
    element.classList.remove('reveal');
    void element.offsetWidth;
    element.classList.add('reveal');
};

const applyLanguage = (language) => {
    currentLanguage = translations[language] ? language : 'de';
    localStorage.setItem('private-transfer-language', currentLanguage);
    document.documentElement.lang = currentLanguage;

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        element.textContent = t(element.dataset.i18n);
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
        element.setAttribute('placeholder', t(element.dataset.i18nPlaceholder));
    });

    document.querySelectorAll('[data-language-option]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.languageOption === currentLanguage);
    });

    document.querySelector('[data-theme-label]') && (document.querySelector('[data-theme-label]').textContent = document.documentElement.classList.contains('dark') ? t('light') : t('dark'));
};

const applyTheme = (theme) => {
    currentTheme = theme === 'dark' ? 'dark' : 'light';
    localStorage.setItem('private-transfer-theme', currentTheme);
    document.documentElement.classList.toggle('dark', currentTheme === 'dark');
    document.querySelector('[data-theme-label]') && (document.querySelector('[data-theme-label]').textContent = currentTheme === 'dark' ? t('light') : t('dark'));
};

document.querySelectorAll('[data-language-option]').forEach((button) => {
    button.addEventListener('click', () => applyLanguage(button.dataset.languageOption));
});

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
});

applyTheme(currentTheme);
applyLanguage(currentLanguage);

if (uploadForm) {
    const fileInput = uploadForm.querySelector('[data-files]');
    const dropzone = uploadForm.querySelector('[data-dropzone]');
    const list = uploadForm.querySelector('[data-file-list]');
    const totalBar = uploadForm.querySelector('[data-total-bar]');
    const totalText = uploadForm.querySelector('[data-total-text]');
    const uploadedText = uploadForm.querySelector('[data-uploaded-text]');
    const speedText = uploadForm.querySelector('[data-speed-text]');
    const etaText = uploadForm.querySelector('[data-eta-text]');
    const statusText = uploadForm.querySelector('[data-status]');
    const resumePanel = document.querySelector('[data-resume-panel]');
    const chunkSize = Number(uploadForm.dataset.chunkSize);
    const stateKey = 'private-transfer-active';
    let selectedFiles = [];
    let transfer = null;
    let savedTransfer = null;
    let progressSample = null;
    let smoothedBytesPerSecond = 0;

    const formatSize = (bytes) => {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unit = 0;
        while (size >= 1024 && unit < units.length - 1) {
            size /= 1024;
            unit += 1;
        }
        return `${size.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`;
    };

    const formatDuration = (seconds) => {
        if (!Number.isFinite(seconds) || seconds <= 0) {
            return '--';
        }

        if (seconds < 60) {
            return `${Math.ceil(seconds)}s`;
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.ceil(seconds % 60);

        if (minutes < 60) {
            return `${minutes}m ${remainingSeconds}s`;
        }

        return `${Math.floor(minutes / 60)}h ${minutes % 60}m`;
    };

    const escapeHtml = (value) => value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const renderFiles = () => {
        list.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const row = document.createElement('li');
            row.className = 'rounded-md border border-white/80 bg-white/90 p-3 shadow-sm';
            row.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-md bg-gradient-to-br from-teal-100 to-rose-100 text-xs font-black text-slate-700">${t('fileBadge')}</span>
                        <div class="min-w-0">
                        <p class="truncate text-sm font-bold">${escapeHtml(file.name)}</p>
                        <p class="text-xs text-slate-500">${formatSize(file.size)}</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500" data-file-status="${index}">${t('waiting')}</span>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-gradient-to-r from-teal-400 via-cyan-500 to-rose-400 transition-all" style="width:0%" data-file-bar="${index}"></div>
                </div>`;
            list.appendChild(row);
        });
    };

    const setFiles = (files) => {
        selectedFiles = Array.from(files);
        renderFiles();
    };

    const updateProgress = () => {
        if (!transfer) {
            return;
        }
        const total = transfer.files.reduce((sum, item) => sum + item.size, 0);
        const uploaded = transfer.files.reduce((sum, item) => sum + item.uploadedSize, 0);
        const percent = total > 0 ? Math.round((uploaded / total) * 100) : 0;
        const now = Date.now();

        if (progressSample && uploaded > progressSample.uploaded) {
            const seconds = Math.max((now - progressSample.time) / 1000, 0.001);
            const currentBytesPerSecond = (uploaded - progressSample.uploaded) / seconds;
            smoothedBytesPerSecond = smoothedBytesPerSecond === 0
                ? currentBytesPerSecond
                : (smoothedBytesPerSecond * 0.65) + (currentBytesPerSecond * 0.35);
        }

        progressSample = {time: now, uploaded};
        totalBar.style.width = `${percent}%`;
        revealText(totalText, `${percent}%`);
        uploadedText.textContent = `${formatSize(uploaded)} / ${formatSize(total)} ${t('uploadedOf')}`;
        revealText(speedText, smoothedBytesPerSecond > 0 ? `${formatSize(smoothedBytesPerSecond)}/s` : t('starting'));
        revealText(etaText, smoothedBytesPerSecond > 0 ? formatDuration((total - uploaded) / smoothedBytesPerSecond) : '--');
        transfer.files.forEach((item, index) => {
            const filePercent = item.size > 0 ? Math.round((item.uploadedSize / item.size) * 100) : 0;
            document.querySelector(`[data-file-bar="${index}"]`).style.width = `${filePercent}%`;
            document.querySelector(`[data-file-status="${index}"]`).textContent = item.complete ? t('complete') : `${filePercent}%`;
        });
        localStorage.setItem(stateKey, JSON.stringify(transfer));
    };

    const postJson = async (url, body) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json'},
            body: JSON.stringify(body),
        });
        if (!response.ok) {
            throw new Error((await response.json()).message || 'Request failed');
        }
        return response.json();
    };

    const uploadFile = async (item, file) => {
        while (item.uploadedSize < file.size) {
            const chunk = file.slice(item.uploadedSize, item.uploadedSize + chunkSize);
            const body = new FormData();
            body.append('token', transfer.token);
            body.append('offset', item.uploadedSize);
            body.append('chunk', chunk, file.name);

            const response = await fetch(`/uploads/${item.id}/chunks`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrfToken, Accept: 'application/json'},
                body,
            });
            const payload = await response.json();
            if (!response.ok) {
                if (response.status === 409 && payload.uploaded_size >= 0) {
                    item.uploadedSize = payload.uploaded_size;
                    updateProgress();
                    continue;
                }
                throw new Error(payload.message || 'Upload failed');
            }
            item.uploadedSize = payload.uploaded_size;
            item.complete = payload.complete;
            updateProgress();
        }
    };

    const startUpload = async (event) => {
        event.preventDefault();
        progressSample = null;
        smoothedBytesPerSecond = 0;
        if (selectedFiles.length === 0) {
            statusText.textContent = t('emptyFiles');
            return;
        }

        uploadForm.querySelector('button[type="submit"]').disabled = true;
        totalBar.classList.add('is-uploading');
        statusText.textContent = t('preparing');
        const form = new FormData(uploadForm);
        const payload = {
            recipient_email: form.get('recipient_email'),
            sender_email: form.get('sender_email') || null,
            message: form.get('message') || null,
            password: form.get('password') || null,
            max_downloads: form.get('max_downloads') || null,
            files: selectedFiles.map((file) => ({name: file.name, size: file.size, type: file.type})),
        };

        const canResume = savedTransfer && savedTransfer.files.length === selectedFiles.length
            && savedTransfer.files.every((item, index) => item.name === selectedFiles[index].name && item.size === selectedFiles[index].size);

        if (canResume) {
            statusText.textContent = t('resumeStatus');
            const status = await fetch(`/transfers/${savedTransfer.token}/status`, {headers: {Accept: 'application/json'}}).then((response) => response.json());
            transfer = {
                token: savedTransfer.token,
                files: savedTransfer.files.map((item) => {
                    const serverFile = status.files.find((file) => file.id === item.id);
                    return {...item, uploadedSize: serverFile?.uploaded_size || 0, complete: serverFile?.complete || false};
                }),
            };
        } else {
            const created = await postJson('/transfers', payload);
            transfer = {
                token: created.token,
                files: created.files.map((file, index) => ({
                    id: file.id,
                    name: file.name,
                    size: selectedFiles[index].size,
                    uploadedSize: file.uploaded_size,
                    complete: false,
                })),
            };
        }

        statusText.textContent = t('uploading');
        for (let index = 0; index < selectedFiles.length; index += 1) {
            await uploadFile(transfer.files[index], selectedFiles[index]);
        }

        localStorage.removeItem(stateKey);
        totalBar.classList.remove('is-uploading');
        statusText.textContent = t('uploadComplete');
        revealText(speedText, t('complete'));
        revealText(etaText, '0s');
        window.location.href = `/t/${transfer.token}`;
    };

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('border-teal-500');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-teal-500'));
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('border-teal-500');
        setFiles(event.dataTransfer.files);
    });
    fileInput.addEventListener('change', () => setFiles(fileInput.files));
    uploadForm.addEventListener('submit', (event) => startUpload(event).catch((error) => {
        statusText.textContent = error.message;
        totalBar.classList.remove('is-uploading');
        uploadForm.querySelector('button[type="submit"]').disabled = false;
    }));

    const saved = localStorage.getItem(stateKey);
    if (saved && resumePanel) {
        savedTransfer = JSON.parse(saved);
        resumePanel.classList.remove('hidden');
    }
}
