const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const uploadForm = document.querySelector('[data-upload-form]');

if (uploadForm) {
    const fileInput = uploadForm.querySelector('[data-files]');
    const dropzone = uploadForm.querySelector('[data-dropzone]');
    const list = uploadForm.querySelector('[data-file-list]');
    const totalBar = uploadForm.querySelector('[data-total-bar]');
    const totalText = uploadForm.querySelector('[data-total-text]');
    const statusText = uploadForm.querySelector('[data-status]');
    const resumePanel = document.querySelector('[data-resume-panel]');
    const chunkSize = Number(uploadForm.dataset.chunkSize);
    const stateKey = 'private-transfer-active';
    let selectedFiles = [];
    let transfer = null;
    let savedTransfer = null;

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

    const renderFiles = () => {
        list.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const row = document.createElement('li');
            row.className = 'rounded-md border border-slate-200 bg-white p-3';
            row.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">${file.name}</p>
                        <p class="text-xs text-slate-500">${formatSize(file.size)}</p>
                    </div>
                    <span class="text-xs text-slate-500" data-file-status="${index}">Waiting</span>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-teal-500 transition-all" style="width:0%" data-file-bar="${index}"></div>
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
        totalBar.style.width = `${percent}%`;
        totalText.textContent = `${percent}%`;
        transfer.files.forEach((item, index) => {
            const filePercent = item.size > 0 ? Math.round((item.uploadedSize / item.size) * 100) : 0;
            document.querySelector(`[data-file-bar="${index}"]`).style.width = `${filePercent}%`;
            document.querySelector(`[data-file-status="${index}"]`).textContent = item.complete ? 'Complete' : `${filePercent}%`;
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
        if (selectedFiles.length === 0) {
            statusText.textContent = 'Please choose at least one file.';
            return;
        }

        uploadForm.querySelector('button[type="submit"]').disabled = true;
        statusText.textContent = 'Preparing transfer...';
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
            statusText.textContent = 'Checking saved upload progress...';
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

        statusText.textContent = 'Uploading. Keep this tab open; the upload can continue in the background while the tab remains open.';
        for (let index = 0; index < selectedFiles.length; index += 1) {
            await uploadFile(transfer.files[index], selectedFiles[index]);
        }

        localStorage.removeItem(stateKey);
        statusText.textContent = 'Upload complete. The email is being sent automatically.';
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
        uploadForm.querySelector('button[type="submit"]').disabled = false;
    }));

    const saved = localStorage.getItem(stateKey);
    if (saved && resumePanel) {
        savedTransfer = JSON.parse(saved);
        resumePanel.classList.remove('hidden');
    }
}
