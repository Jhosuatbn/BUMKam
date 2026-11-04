import './bootstrap';
import './searchable-select';

document.addEventListener('click', (event) => {
    const previewButton = event.target.closest('[data-photo-preview]');
    if (previewButton) {
        const dialog = document.querySelector('[data-photo-preview-dialog]');
        const image = dialog?.querySelector('[data-photo-preview-image]');
        const title = dialog?.querySelector('[data-photo-preview-title]');

        if (dialog && image && typeof dialog.showModal === 'function') {
            image.src = previewButton.dataset.photoPreview;
            image.alt = previewButton.dataset.photoAlt || 'Pratinjau foto';
            title.textContent = image.alt;
            dialog.showModal();
        }
        return;
    }

    if (event.target.closest('[data-photo-preview-close]')) {
        document.querySelector('[data-photo-preview-dialog]')?.close();
        return;
    }

    const filePreviewButton = event.target.closest('[data-file-preview]');
    if (filePreviewButton) {
        const dialog = document.querySelector('[data-file-preview-dialog]');
        const frame = dialog?.querySelector('[data-file-preview-frame]');
        const title = dialog?.querySelector('[data-file-preview-title]');
        if (dialog && frame && typeof dialog.showModal === 'function') {
            frame.src = filePreviewButton.dataset.filePreview;
            if (title) title.textContent = filePreviewButton.dataset.fileName || 'Pratinjau dokumen';
            dialog.showModal();
        }
        return;
    }

    if (event.target.closest('[data-file-preview-close]')) {
        document.querySelector('[data-file-preview-dialog]')?.close();
    }

});

let pendingDeleteForm = null;
let pendingDeleteSubmitter = null;
let confirmedDeleteForm = null;

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form');
    if (!form) return;

    const methodOverride = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
    const isDelete = methodOverride === 'DELETE' || form.dataset.confirmDelete !== undefined;
    if (!isDelete) return;

    if (confirmedDeleteForm === form) {
        confirmedDeleteForm = null;
        return;
    }

    const dialog = document.querySelector('[data-delete-confirmation-dialog]');
    if (!dialog || typeof dialog.showModal !== 'function') {
        if (!window.confirm('Apakah Anda yakin ingin menghapus data ini?')) event.preventDefault();
        return;
    }

    event.preventDefault();
    pendingDeleteForm = form;
    pendingDeleteSubmitter = event.submitter || null;
    dialog.showModal();
});

document.querySelector('[data-delete-cancel]')?.addEventListener('click', () => {
    document.querySelector('[data-delete-confirmation-dialog]')?.close();
});

document.querySelector('[data-delete-confirm]')?.addEventListener('click', () => {
    if (!pendingDeleteForm) return;

    const form = pendingDeleteForm;
    const submitter = pendingDeleteSubmitter;
    pendingDeleteForm = null;
    pendingDeleteSubmitter = null;
    confirmedDeleteForm = form;
    document.querySelector('[data-delete-confirmation-dialog]')?.close();

    if (submitter) form.requestSubmit(submitter);
    else form.requestSubmit();
});

document.querySelector('[data-delete-confirmation-dialog]')?.addEventListener('close', () => {
    pendingDeleteForm = null;
    pendingDeleteSubmitter = null;
});

const actionIconPaths = {
    save: '<path d="m5 12 4 4L19 6"/>',
    edit: '<path d="m16 4 4 4M4 20l4-.8L19 8a2 2 0 0 0-3-3L5 16l-1 4Z"/>',
    delete: '<path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
    print: '<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/>',
    login: '<path d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>',
    cancel: '<path d="m6 6 12 12M18 6 6 18"/>',
    show: '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
};
document.querySelectorAll('button').forEach((button) => {
    if (button.querySelector('svg, i.icon-base, .bx') || button.matches('[data-no-auto-icon], [data-photo-preview], [data-photo-preview-close], [data-clear-photo-preview], [data-clear-upload-preview], [data-theme-toggle]')) return;
    const label = `${button.textContent} ${button.getAttribute('aria-label') || ''}`.toLocaleLowerCase('id-ID');
    const key = /hapus|delete/.test(label) ? 'delete'
        : /simpan|update|login|masuk/.test(label) ? (/(login|masuk)/.test(label) ? 'login' : 'save')
        : /cari|search/.test(label) ? 'search'
        : /cetak|print|ekspor/.test(label) ? 'print'
        : /batal|reset|tutup/.test(label) ? 'cancel'
        : /detail|lihat/.test(label) ? 'show'
        : null;
    if (!key) return;
    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icon.setAttribute('viewBox', '0 0 24 24');
    icon.setAttribute('fill', 'none');
    icon.setAttribute('stroke', 'currentColor');
    icon.setAttribute('stroke-width', '1.8');
    icon.setAttribute('stroke-linecap', 'round');
    icon.setAttribute('stroke-linejoin', 'round');
    icon.setAttribute('aria-hidden', 'true');
    icon.setAttribute('class', 'mr-2 inline-block size-4 align-[-3px]');
    icon.innerHTML = actionIconPaths[key];
    button.prepend(icon);
});

const savedTheme = localStorage.getItem('bumkam-theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    document.documentElement.dataset.bsTheme = 'dark';
} else {
    document.documentElement.classList.remove('dark');
    document.documentElement.dataset.bsTheme = 'light';
}
const syncThemeToggleLabels = () => {
    const isDark = document.documentElement.classList.contains('dark');
    const nextMode = isDark ? 'terang' : 'gelap';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', `Aktifkan mode ${nextMode}`);
        button.setAttribute('title', `Aktifkan mode ${nextMode}`);
        button.replaceChildren();

        if (button.classList.contains('bumkam-theme-toggle')) {
            const icon = document.createElement('i');
            icon.className = `icon-base bx ${isDark ? 'bx-sun' : 'bx-moon'}`;
            icon.setAttribute('aria-hidden', 'true');
            button.append(icon);
            return;
        }

        const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('class', 'size-4');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('fill', 'none');
        icon.setAttribute('stroke', 'currentColor');
        icon.setAttribute('stroke-width', '1.8');
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = isDark
            ? '<circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M20.9 13.1A8.5 8.5 0 0 1 10.9 3.1 8.5 8.5 0 1 0 20.9 13.1Z"/>';
        button.append(icon);
    });
};
syncThemeToggleLabels();
document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) return;
    const isDark = document.documentElement.classList.toggle('dark');
    document.documentElement.dataset.bsTheme = isDark ? 'dark' : 'light';
    localStorage.setItem('bumkam-theme', isDark ? 'dark' : 'light');
    syncThemeToggleLabels();
});

document.querySelectorAll('[data-photo-preview-dialog]').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
});

document.querySelectorAll('[data-file-preview-dialog]').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => {
        const frame = dialog.querySelector('[data-file-preview-frame]');
        if (frame) frame.src = 'about:blank';
    });
});

document.querySelectorAll('[data-upload-preview-input]').forEach((input) => {
    const form = input.closest('form');
    const box = form?.querySelector('[data-upload-preview-box]');
    const image = box?.querySelector('[data-upload-preview-image]');
    const imageButton = box?.querySelector('[data-upload-preview-image-button]');
    const fileButton = box?.querySelector('[data-upload-preview-file-button]');
    const error = form?.querySelector('[data-upload-preview-error]');
    const clearButton = box?.querySelector('[data-clear-upload-preview]');
    const fileName = box?.querySelector('[data-upload-preview-name]');
    const placeholder = form?.querySelector('[data-upload-preview-placeholder]');
    const removeSavedPhoto = form?.querySelector('input[name="hapus_foto"]');
    let previewUrl = null;

    if (!box || !image || !imageButton || !fileButton || !clearButton) return;

    const clearPreview = () => {
        input.value = '';
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        image.removeAttribute('src');
        imageButton.removeAttribute('data-photo-preview');
        imageButton.removeAttribute('data-photo-alt');
        imageButton.classList.add('hidden');
        fileButton.removeAttribute('data-file-preview');
        fileButton.removeAttribute('data-file-name');
        fileButton.classList.add('hidden');
        if (fileName) fileName.textContent = '';
        box.classList.add('hidden');
        placeholder?.classList.remove('hidden');
        if (error) {
            error.textContent = '';
            error.classList.add('hidden');
        }
    };

    clearButton.addEventListener('click', clearPreview);
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (error) {
            error.textContent = '';
            error.classList.add('hidden');
        }
        if (!file) {
            clearPreview();
            return;
        }

        const acceptedTypes = (input.getAttribute('accept') || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
        const accepted = acceptedTypes.some((type) => type.startsWith('.')
            ? file.name.toLowerCase().endsWith(type)
            : type.endsWith('/*')
                ? file.type.startsWith(type.slice(0, -1))
                : file.type.toLowerCase() === type);
        if (!accepted) {
            clearPreview();
            if (error) {
                error.textContent = input.dataset.allowedLabel
                    ? `Pilih file dengan format ${input.dataset.allowedLabel}.`
                    : 'Format file yang dipilih tidak didukung.';
                error.classList.remove('hidden');
            }
            return;
        }

        const maxSize = Number(input.dataset.maxSize || 2 * 1024 * 1024);
        if (file.size > maxSize) {
            clearPreview();
            if (error) {
                error.textContent = `Ukuran file maksimal ${Math.round(maxSize / (1024 * 1024))} MB.`;
                error.classList.remove('hidden');
            }
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        if (fileName) fileName.textContent = file.name;
        if (file.type.startsWith('image/')) {
            image.src = previewUrl;
            imageButton.dataset.photoPreview = previewUrl;
            imageButton.dataset.photoAlt = `Pratinjau ${file.name}`;
            imageButton.classList.remove('hidden');
            fileButton.classList.add('hidden');
        } else if (file.type === 'application/pdf') {
            fileButton.dataset.filePreview = previewUrl;
            fileButton.dataset.fileName = file.name;
            fileButton.classList.remove('hidden');
            imageButton.classList.add('hidden');
        }
        placeholder?.classList.add('hidden');
        box.classList.remove('hidden');
        if (removeSavedPhoto) removeSavedPhoto.checked = false;
    });
});

document.querySelectorAll('[data-payment-method]').forEach((select) => {
    const proofField = select.form?.querySelector('[data-cash-payment-proof]');
    if (!proofField) return;

    const syncProofField = () => {
        const isCash = select.value === 'tunai';
        proofField.classList.toggle('hidden', !isCash);
        const fileInput = proofField.querySelector('input[type="file"]');
        if (!isCash && fileInput) {
            fileInput.value = '';
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    select.addEventListener('change', syncProofField);
    syncProofField();
});

const rupiahDigits = (value) => value.replace(/\D/g, '');
document.querySelectorAll('[data-rupiah-input]').forEach((input) => {
    const nilaiAwal = Number(input.value);
    if (input.value !== '' && Number.isFinite(nilaiAwal)) {
        input.value = Math.round(nilaiAwal).toLocaleString('id-ID');
    }
    const format = () => {
        const digits = rupiahDigits(input.value);
        input.value = digits ? Number(digits).toLocaleString('id-ID') : '';
    };
    input.addEventListener('input', format);
    format();
});

document.addEventListener('submit', (event) => {
    event.target.querySelectorAll('[data-rupiah-input]').forEach((input) => {
        input.value = rupiahDigits(input.value) || '0';
    });
});
