function initSearchableSelect(wrapper) {
    const input = wrapper.querySelector('[data-searchable-input]');
    const select = wrapper.querySelector('[data-searchable-options]');
    const results = wrapper.querySelector('[data-searchable-results]');

    if (!input || !select || !results) return;

    function closeResults() {
        results.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
    }

    function renderResults() {
        const query = input.value.trim().toLocaleLowerCase('id-ID');
        const matchingOptions = Array.from(select.options).filter(option => {
            if (!option.value) return false;

            const searchableText = (option.dataset.search || option.textContent)
                .toLocaleLowerCase('id-ID');

            return !query || searchableText.includes(query);
        });
        const visibleOptions = matchingOptions.slice(0, 100);

        results.replaceChildren();

        if (visibleOptions.length === 0) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'px-4 py-3 text-sm text-slate-500';
            emptyMessage.textContent = 'Data tidak ditemukan.';
            results.appendChild(emptyMessage);
        } else {
            visibleOptions.forEach(option => {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('role', 'option');
                button.className = 'block w-full px-4 py-3 text-left text-sm text-slate-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none';
                button.textContent = option.textContent.trim();

                button.addEventListener('click', () => {
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

            if (matchingOptions.length > visibleOptions.length) {
                const moreMessage = document.createElement('p');
                moreMessage.className = 'px-4 py-2 text-xs text-slate-500';
                moreMessage.textContent = 'Menampilkan 100 hasil pertama. Persempit pencarian untuk hasil lainnya.';
                results.appendChild(moreMessage);
            }
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

        if (!display) return;

        select.addEventListener('change', () => {
            const selectedOption = select.options[select.selectedIndex];
            display.value = selectedOption?.value ? selectedOption.dataset.angsuran : '';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-searchable-select]').forEach(initSearchableSelect);
    initInstallmentDisplays();
});
