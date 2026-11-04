@extends('layouts.sneat-dashboard')

@section('title', 'Tambah Pembayaran')

@section('content')
    <div class="pembayaran-page space-y-6">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @unless ($dendaAktif)
            <div role="status" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Tabel fitur denda belum tersedia. Pembayaran tetap dapat dicatat, tetapi denda dan alokasi terpisah akan aktif setelah migration fitur diterapkan.</div>
        @endunless

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pembayaran-form-heading">
            <h2 id="pembayaran-form-heading" class="text-base font-semibold text-slate-900">Informasi Pembayaran</h2>
            @if ($pemesanan->isEmpty())
                <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">Tidak ada pemesanan yang dapat dibayar saat ini. Pemesanan lunas dan transaksi online yang masih menunggu tidak ditampilkan.</div>
            @endif
            <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pembayaran_display" class="block text-sm font-semibold text-slate-700">ID Pembayaran</label>
                        <input id="id_pembayaran_display" type="text" value="{{ $idPembayaranPreview }}" readonly class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700">
                        <p class="mt-1 text-xs text-slate-500">ID dibuat otomatis saat pembayaran disimpan.</p>
                    </div>
                    <div>
                        <label for="tanggal_pembayaran" class="block text-sm font-semibold text-slate-700">Tanggal Pembayaran</label>
                        <input id="tanggal_pembayaran" type="date" name="tanggal_pembayaran" value="{{ old('tanggal_pembayaran', now(config('kredit.zona_waktu', 'Asia/Jakarta'))->toDateString()) }}" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div>
                        <label for="metode_pembayaran" class="block text-sm font-semibold text-slate-700">Metode Pembayaran</label>
                        <select id="metode_pembayaran" name="metode_pembayaran" required data-payment-method class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="tunai" {{ old('metode_pembayaran', 'tunai') === 'tunai' ? 'selected' : '' }}>Tunai / Bayar di Tempat</option>
                            <option value="online" {{ old('metode_pembayaran') === 'online' ? 'selected' : '' }}>Online (Midtrans)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Tunai dicatat Staff. Online dilanjutkan melalui halaman Midtrans.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="cari_pemesanan" class="block text-sm font-semibold text-slate-700">Pemesanan</label>
                        <div class="relative mt-2" data-searchable-select>
                            <input id="cari_pemesanan" type="search" required role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="daftarPemesanan" autocomplete="off" placeholder="Cari ID pemesanan atau nama pelanggan..." data-searchable-input class="block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <div id="daftarPemesanan" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                            <select id="id_pemesanan" name="id_pemesanan" class="hidden" tabindex="-1" aria-hidden="true" data-searchable-options data-installment-select>
                                <option value="">-- Pilih Pemesanan --</option>
                                @foreach ($pemesanan as $item)
                                    @php($angsuranBerikut = collect($item->ringkasan_denda['jadwal'])->first(fn ($row) => $row['sisa_angsuran'] > 0 || $row['denda_berjalan'] > 0))
                                    @php($nilaiBerikut = $angsuranBerikut['sisa_angsuran'] ?? 0)
                                    @php($dendaBerikut = $angsuranBerikut['denda_berjalan'] ?? 0)
                                    @php($nominalBerikut = $nilaiBerikut + $dendaBerikut)
                                    @php($labelPemesanan = $item->id_pemesanan . ' - ' . ($item->pelanggan->nama_pelanggan ?? '-') . ' - Sisa angsuran Rp ' . number_format($nilaiBerikut, 0, ',', '.') . ' - Denda Rp ' . number_format($dendaBerikut, 0, ',', '.'))
                                    <option value="{{ $item->id_pemesanan }}" data-search="{{ strtolower($item->id_pemesanan . ' ' . ($item->pelanggan->nama_pelanggan ?? '')) }}" data-angsuran="{{ number_format($nominalBerikut, 2, '.', '') }}" data-denda="{{ number_format($dendaBerikut, 2, '.', '') }}" {{ old('id_pemesanan') == $item->id_pemesanan ? 'selected' : '' }}>
                                        {{ $labelPemesanan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Ketik ID atau nama pelanggan, lalu pilih pemesanan dari daftar.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="nominal" class="block text-sm font-semibold text-slate-700">Nominal Pembayaran</label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">Rp</span>
                            <input id="nominal" name="nominal" type="number" min="0.01" step="0.01" value="{{ old('nominal') }}" data-installment-display aria-describedby="nominal-hint" class="block min-w-0 flex-1 border-0 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0">
                        </div>
                        <p id="nominal-hint" class="mt-1 text-xs text-slate-500">Nominal default mengikuti sisa angsuran dan denda berikutnya. Staff dapat mencatat pembayaran sebagian.</p>
                        <p class="mt-1 text-xs text-slate-500">Denda angsuran berikutnya: <strong>Rp <span data-installment-penalty-display>0</span></strong>. Pembayaran dialokasikan ke pokok angsuran terlebih dahulu, lalu ke denda.</p>
                    </div>
                    <div class="md:col-span-2" data-cash-payment-proof>
                        <label for="bukti_pembayaran" class="block text-sm font-semibold text-slate-700">Bukti Pembayaran (opsional)</label>
                        <div class="mt-2 space-y-2">
                            <input id="bukti_pembayaran" type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/webp,application/pdf" data-upload-preview-input data-max-size="5242880" data-allowed-label="JPG, PNG, WEBP, atau PDF" class="form-control @error('bukti_pembayaran') border-rose-400 @enderror">
                        <div data-upload-preview-box class="hidden mt-3 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-3" aria-live="polite">
                            <button type="button" data-upload-preview-image-button class="hidden cursor-zoom-in rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" aria-label="Perbesar pratinjau bukti pembayaran">
                                <img data-upload-preview-image src="" alt="Pratinjau bukti pembayaran" class="h-36 w-56 rounded-lg bg-slate-100 object-contain">
                            </button>
                            <button type="button" data-upload-preview-file-button class="hidden rounded-lg bg-slate-100 px-4 py-3 text-sm font-semibold text-blue-800"><i class="icon-base bx bx-file-pdf me-1" aria-hidden="true"></i>Lihat PDF</button>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-medium text-slate-500">File siap diunggah</span><span data-upload-preview-name class="mt-0.5 block truncate text-sm font-semibold text-slate-800"></span></span>
                            <button type="button" data-clear-upload-preview class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-xl leading-none text-slate-500 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" aria-label="Hapus file yang dipilih" title="Hapus file yang dipilih">&times;</button>
                        </div>
                        <p data-upload-preview-error role="alert" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, WEBP, atau PDF; maksimal 5 MB.</p>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
                    <a href="{{ route('pembayaran.index') }}" class="bumkam-cancel-button inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                    <button type="submit" data-no-auto-icon class="bumkam-green-action inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">Simpan Pembayaran</button>
                </div>
            </form>
        </section>
    </div>
@endsection
