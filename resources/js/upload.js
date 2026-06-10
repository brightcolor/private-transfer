export const initUploadForm = ({csrfToken, revealText, t}) => {
    const uploadForm = document.querySelector('[data-upload-form]');

    if (!uploadForm) {
        return;
    }

    const fileInput = uploadForm.querySelector('[data-files]');
    const dropzone = uploadForm.querySelector('[data-dropzone]');
    const list = uploadForm.querySelector('[data-file-list]');
    const totalBar = uploadForm.querySelector('[data-total-bar]');
    const totalText = uploadForm.querySelector('[data-total-text]');
    const uploadedText = uploadForm.querySelector('[data-uploaded-text]');
    const speedText = uploadForm.querySelector('[data-speed-text]');
    const etaText = uploadForm.querySelector('[data-eta-text]');
    const statusText = uploadForm.querySelector('[data-status]');
    const pauseButton = uploadForm.querySelector('[data-pause-upload]');
    const resumePanel = document.querySelector('[data-resume-panel]');
    const chunkSize = Number(uploadForm.dataset.chunkSize);
    const stateKey = 'private-transfer-active';
    let selectedFiles = [];
    let transfer = null;
    let savedTransfer = null;
    let progressSample = null;
    let smoothedBytesPerSecond = 0;
    let paused = false;

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
        if (!Number.isFinite(seconds) || seconds <= 0) return '--';
        if (seconds < 60) return `${Math.ceil(seconds)}s`;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.ceil(seconds % 60);
        return minutes < 60 ? `${minutes}m ${remainingSeconds}s` : `${Math.floor(minutes / 60)}h ${minutes % 60}m`;
    };

    const escapeHtml = (value) => value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const waitWhilePaused = async () => {
        while (paused) {
            statusText.textContent = t('paused');
            await new Promise((resolve) => setTimeout(resolve, 250));
        }
    };

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

    const updateProgress = () => {
        const total = transfer.files.reduce((sum, item) => sum + item.size, 0);
        const uploaded = transfer.files.reduce((sum, item) => sum + item.uploadedSize, 0);
        const percent = total > 0 ? Math.round((uploaded / total) * 100) : 0;
        const now = Date.now();

        if (progressSample && uploaded > progressSample.uploaded) {
            const seconds = Math.max((now - progressSample.time) / 1000, 0.001);
            const currentSpeed = (uploaded - progressSample.uploaded) / seconds;
            smoothedBytesPerSecond = smoothedBytesPerSecond === 0 ? currentSpeed : (smoothedBytesPerSecond * 0.65) + (currentSpeed * 0.35);
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
        if (!response.ok) throw new Error((await response.json()).message || 'Request failed');
        return response.json();
    };

    const uploadFile = async (item, file) => {
        while (item.uploadedSize < file.size) {
            await waitWhilePaused();
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
        pauseButton.classList.remove('hidden');
        uploadForm.classList.add('is-uploading');
        totalBar.classList.add('is-uploading');
        statusText.textContent = t('preparing');
        const form = new FormData(uploadForm);
        const payload = Object.fromEntries(form.entries());
        payload.files = selectedFiles.map((file) => ({name: file.name, size: file.size, type: file.type}));

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
                files: created.files.map((file, index) => ({id: file.id, name: file.name, size: selectedFiles[index].size, uploadedSize: file.uploaded_size, complete: false})),
            };
        }

        statusText.textContent = t('uploading');
        for (let index = 0; index < selectedFiles.length; index += 1) {
            await uploadFile(transfer.files[index], selectedFiles[index]);
        }

        localStorage.removeItem(stateKey);
        uploadForm.classList.remove('is-uploading');
        totalBar.classList.remove('is-uploading');
        statusText.textContent = t('uploadComplete');
        revealText(speedText, t('complete'));
        revealText(etaText, '0s');
        window.location.href = `/t/${transfer.token}/sent`;
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
        selectedFiles = Array.from(event.dataTransfer.files);
        renderFiles();
    });
    fileInput.addEventListener('change', () => {
        selectedFiles = Array.from(fileInput.files);
        renderFiles();
    });
    pauseButton.addEventListener('click', () => {
        paused = !paused;
        pauseButton.textContent = paused ? t('resumeUpload') : t('pauseUpload');
    });
    uploadForm.addEventListener('submit', (event) => startUpload(event).catch((error) => {
        statusText.textContent = error.message;
        uploadForm.classList.remove('is-uploading');
        totalBar.classList.remove('is-uploading');
        uploadForm.querySelector('button[type="submit"]').disabled = false;
    }));

    const saved = localStorage.getItem(stateKey);
    if (saved && resumePanel) {
        savedTransfer = JSON.parse(saved);
        resumePanel.classList.remove('hidden');
    }
};
