function initSearchableSelect(wrapper) {
    const input = wrapper.querySelector('[data-searchable-input]');
    const select = wrapper.querySelector('[data-searchable-options]');
    const results = wrapper.querySelector('[data-searchable-results]');

    if (!input || !select || !results) return;

    function closeResults() {
        results.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
    }

    let controller = null;
    async function renderResults() {
        const query = input.value.trim();
        controller?.abort();
        results.replaceChildren();
        if (query.length < 3) {
            const hint = document.createElement('div');
            hint.className = 'px-4 py-3 text-sm text-slate-500';
            hint.textContent = 'Ketik minimal 3 karakter untuk mencari.';
            results.appendChild(hint);
            results.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
            return;
        }
        controller = new AbortController();
        let records = [];
        try {
            const url = new URL(input.dataset.searchUrl, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            records = (await response.json()).data || [];
        } catch (error) {
            if (error.name === 'AbortError') return;
        }
        if (records.length === 0) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'px-4 py-3 text-sm text-slate-500';
            emptyMessage.textContent = 'Data tidak ditemukan.';
            results.appendChild(emptyMessage);
        } else {
            records.forEach(record => {
                const option = document.createElement('option');
                option.value = record.id_barang || record.id_pelanggan;
                if (record.id_barang) {
                    option.dataset.harga = record.harga;
                    const stock = record.stok === null ? 'Stok belum diatur' : `Stok ${Number(record.stok).toLocaleString('id-ID')}`;
                    option.textContent = `${record.nama_barang} · Rp ${Number(record.harga).toLocaleString('id-ID')} · ${stock}`;
                } else {
                    option.textContent = record.nama_pelanggan;
                }
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('role', 'option');
                button.className = 'block w-full px-4 py-3 text-left text-sm text-slate-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none';
                button.textContent = option.textContent.trim();

                button.addEventListener('click', () => {
                    select.replaceChildren(new Option('-- Pilih --', ''), option);
                    select.value = option.value;
                    input.value = option.textContent.trim();
                    input.setCustomValidity('');
                    input.removeAttribute('aria-invalid');
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeResults();
                });

                button.addEventListener('keydown', event => {
                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        button.nextElementSibling?.focus();
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        if (button.previousElementSibling) {
                            button.previousElementSibling.focus();
                        } else {
                            input.focus();
                        }
                    }

                    if (event.key === 'Escape') {
                        closeResults();
                        input.focus();
                    }
                });

                results.appendChild(button);
            });

        }

        results.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    }

    input.addEventListener('focus', renderResults);
    input.addEventListener('input', () => {
        input.setCustomValidity('');
        input.removeAttribute('aria-invalid');

        if (select.value) {
            select.value = '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        renderResults();
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeResults();

        if (event.key === 'ArrowDown') {
            const firstOption = results.querySelector('[role="option"]');

            if (firstOption) {
                event.preventDefault();
                firstOption.focus();
            }
        }
    });

    select.addEventListener('change', () => {
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption?.value) {
            input.value = selectedOption.textContent.trim();
            input.setCustomValidity('');
            input.removeAttribute('aria-invalid');
        }
    });

    input.form?.addEventListener('submit', event => {
        if (!select.value) {
            event.preventDefault();
            input.setCustomValidity('Pilih salah satu data dari daftar.');
            input.setAttribute('aria-invalid', 'true');
            input.reportValidity();
        }
    });

    document.addEventListener('click', event => {
        if (!wrapper.contains(event.target)) closeResults();
    });

    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        input.value = selectedOption.textContent.trim();
    }
}

function initInstallmentDisplays() {
    document.querySelectorAll('[data-installment-select]').forEach(select => {
        const display = select.closest('form')?.querySelector('[data-installment-display]');
        const penaltyDisplay = select.closest('form')?.querySelector('[data-installment-penalty-display]');

        if (!display) return;

        const update = (resetAmount = false) => {
            const selectedOption = select.options[select.selectedIndex];
            const nominal = selectedOption?.value ? Number(selectedOption.dataset.angsuran) : NaN;
            const denda = selectedOption?.value ? Number(selectedOption.dataset.denda || 0) : NaN;
            if (resetAmount || !display.value) display.value = Number.isFinite(nominal) ? nominal.toFixed(2) : '';
            if (penaltyDisplay) penaltyDisplay.textContent = Number.isFinite(denda) ? Math.round(denda).toLocaleString('id-ID') : '0';
        };

        select.addEventListener('change', () => update(true));
        update(false);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-searchable-select]').forEach(initSearchableSelect);
    initInstallmentDisplays();
});
