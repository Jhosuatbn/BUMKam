@extends('layouts.sneat-dashboard')

@section('title', 'Catat Barang Masuk')

@section('content')
    <section class="card">
        <div class="card-header">
            <h1 class="card-title mb-1">Catat Barang Masuk</h1>
            <p class="mb-0 text-body-secondary">Simpan pembelian dan tambah stok secara otomatis.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('barang-masuk.store') }}" id="receipt-form" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label" for="id_supplier">Supplier</label>
                    <select id="id_supplier" name="id_supplier" class="form-select" required>
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id_supplier }}" @selected(old('id_supplier') === $supplier->id_supplier)>{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tanggal_masuk">Tanggal Masuk</label>
                    <input id="tanggal_masuk" type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="nomor_faktur">Nomor Faktur</label>
                    <input id="nomor_faktur" name="nomor_faktur" maxlength="100" class="form-control" value="{{ old('nomor_faktur') }}">
                </div>

                <div class="col-12">
                    <div class="mb-2">
                        <h2 class="h6 mb-1">Barang yang diterima</h2>
                        <small class="text-body-secondary">Baris berikutnya muncul otomatis setelah barang, jumlah, dan harga satuan diisi.</small>
                    </div>
                    <div id="receipt-lines" class="d-grid gap-3">
                        @php($oldLines = old('items', [['id_barang' => '', 'jumlah' => 1, 'harga_satuan' => '']]))
                        @foreach ($oldLines as $i => $line)
                            <div class="row g-2 align-items-end receipt-line">
                                <div class="col-md-6">
                                    <label class="form-label" for="receipt-item-search-{{ $i }}">Nama Barang</label>
                                    <div class="relative" data-searchable-select>
                                        <input id="receipt-item-search-{{ $i }}" type="search" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="receipt-item-results-{{ $i }}" autocomplete="off" placeholder="Ketik minimal 3 karakter nama barang..." data-search-name-only data-dedupe-name-price data-search-url="{{ route('barang-masuk.cari-barang') }}" data-searchable-input class="form-control">
                                        <div id="receipt-item-results-{{ $i }}" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                                        <select data-line-field="id_barang" name="items[{{ $i }}][id_barang]" class="d-none" tabindex="-1" aria-hidden="true" data-searchable-options>
                                            <option value="">Pilih barang</option>
                                        @foreach ($barang as $item)
                                            <option value="{{ $item->id_barang }}" @selected(($line['id_barang'] ?? '') === $item->id_barang)>{{ $item->nama_barang }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jumlah</label>
                                    <input data-line-field="jumlah" name="items[{{ $i }}][jumlah]" type="number" min="1" value="{{ $line['jumlah'] ?? 1 }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input data-line-field="harga_satuan" data-rupiah-input name="items[{{ $i }}][harga_satuan]" type="text" inputmode="numeric" value="{{ $line['harga_satuan'] ?? '' }}" placeholder="Contoh: 4.000.000" class="form-control">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label" for="catatan">Catatan</label>
                    <textarea id="catatan" name="catatan" rows="2" class="form-control">{{ old('catatan') }}</textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn bumkam-green-action" type="submit"><i class="icon-base bx bx-save me-1"></i>Simpan Penerimaan</button>
                </div>
            </form>
        </div>
    </section>

    <template id="receipt-line-template">
        <div class="row g-2 align-items-end receipt-line">
            <div class="col-md-6">
                <label class="form-label" for="receipt-item-search-template">Nama Barang</label>
                <div class="relative" data-searchable-select>
                    <input id="receipt-item-search-template" type="search" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="receipt-item-results-template" autocomplete="off" placeholder="Ketik minimal 3 karakter nama barang..." data-search-name-only data-dedupe-name-price data-search-url="{{ route('barang-masuk.cari-barang') }}" data-searchable-input class="form-control">
                    <div id="receipt-item-results-template" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                    <select data-line-field="id_barang" class="d-none" tabindex="-1" aria-hidden="true" data-searchable-options>
                        <option value="">Pilih barang</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jumlah</label>
                <input data-line-field="jumlah" type="number" min="1" value="1" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Harga Satuan</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input data-line-field="harga_satuan" data-rupiah-input type="text" inputmode="numeric" placeholder="Contoh: 4.000.000" class="form-control">
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const list = document.getElementById('receipt-lines');
            const template = document.getElementById('receipt-line-template');
            const form = document.getElementById('receipt-form');
            if (!list || !template || !form) return;

            const rows = () => Array.from(list.querySelectorAll('.receipt-line'));
            let nextRowId = rows().length;
            const fields = (row) => Object.fromEntries(
                Array.from(row.querySelectorAll('[data-line-field]')).map((field) => [field.dataset.lineField, field])
            );
            const digits = (value) => value.replace(/\D/g, '');
            const isEmpty = (row) => {
                const values = fields(row);
                const searchInput = row.querySelector('[data-searchable-input]');
                return !values.id_barang.value && !searchInput.value.trim() && !digits(values.harga_satuan.value) && ['', '1'].includes(values.jumlah.value);
            };
            const isComplete = (row) => {
                const values = fields(row);
                return Boolean(values.id_barang.value)
                    && Number(values.jumlah.value) >= 1
                    && digits(values.harga_satuan.value) !== '';
            };
            const reindex = () => rows().forEach((row, index) => {
                Object.values(fields(row)).forEach((field) => {
                    field.name = `items[${index}][${field.dataset.lineField}]`;
                });
            });
            const appendRow = () => {
                const row = template.content.firstElementChild.cloneNode(true);
                const index = nextRowId++;
                const searchInput = row.querySelector('[data-searchable-input]');
                const results = row.querySelector('[data-searchable-results]');
                searchInput.id = `receipt-item-search-${index}`;
                searchInput.setAttribute('aria-controls', `receipt-item-results-${index}`);
                row.querySelector('label').htmlFor = searchInput.id;
                results.id = `receipt-item-results-${index}`;
                list.append(row);
                window.initSearchableSelect?.(row);
            };
            const syncRows = () => {
                rows().slice(1).forEach((row) => {
                    if (isEmpty(row)) {
                        const searchInput = row.querySelector('[data-searchable-input]');
                        searchInput.required = false;
                        searchInput.setCustomValidity('');
                        row.remove();
                    }
                });

                if (!rows().length) appendRow();
                reindex();

                rows().forEach((row, index) => {
                    const required = !isEmpty(row) || (index === 0 && rows().length === 1);
                    row.querySelector('[data-searchable-input]').required = required;
                    Object.entries(fields(row)).forEach(([name, field]) => {
                        if (name !== 'id_barang') field.required = required;
                    });
                });

                const lastRow = rows().at(-1);
                if (lastRow && isComplete(lastRow)) {
                    appendRow();
                    reindex();
                }
            };

            list.addEventListener('input', (event) => {
                const field = event.target.closest('[data-line-field]');
                const searchInput = event.target.closest('[data-searchable-input]');
                if (!field && !searchInput) return;

                if (field?.dataset.lineField === 'harga_satuan') {
                    const value = digits(field.value).replace(/^0+(?=\d)/, '');
                    field.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                syncRows();
            });
            list.addEventListener('change', (event) => {
                if (event.target.matches('[data-line-field="id_barang"]')) syncRows();
            });
            form.addEventListener('submit', () => {
                rows().slice(1).forEach((row) => { if (isEmpty(row)) row.remove(); });
                reindex();
            });

            rows().forEach((row) => window.initSearchableSelect?.(row));
            syncRows();
        });
    </script>
@endsection
