@extends('layouts.sneat-dashboard')

@section('title', 'Edit Pembayaran')

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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pembayaran-form-heading">
            <h2 id="pembayaran-form-heading" class="text-base font-semibold text-slate-900">Informasi Pembayaran</h2>
            <form action="{{ route('pembayaran.update', $pembayaran->id_pembayaran) }}" method="POST" enctype="multipart/form-data" class="mt-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pembayaran_display" class="block text-sm font-semibold text-slate-700">ID Pembayaran</label>
                        <input id="id_pembayaran_display" type="text" value="{{ $pembayaran->id_pembayaran }}" disabled class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600">
                    </div>
                    <div>
                        <label for="tanggal_pembayaran" class="block text-sm font-semibold text-slate-700">Tanggal Pembayaran</label>
                        <input id="tanggal_pembayaran" type="date" name="tanggal_pembayaran" value="{{ $pembayaran->tanggal_pembayaran }}" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="cari_pemesanan" class="block text-sm font-semibold text-slate-700">Pemesanan</label>
                        <div class="relative mt-2" data-searchable-select>
                            <input id="cari_pemesanan" type="search" required role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="daftarPemesanan" autocomplete="off" placeholder="Cari ID pemesanan atau nama pelanggan..." data-searchable-input class="block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <div id="daftarPemesanan" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                            <select id="id_pemesanan" name="id_pemesanan" class="hidden" tabindex="-1" aria-hidden="true" data-searchable-options data-installment-select>
                            @foreach ($pemesanan as $item)
                                @php($labelPemesanan = $item->id_pemesanan . ' - ' . ($item->pelanggan->nama_pelanggan ?? '-') . ' - Angsuran Rp ' . number_format($item->angsuran, 0, ',', '.') . ' - Total Rp ' . number_format($item->total_harga, 0, ',', '.'))
                                <option value="{{ $item->id_pemesanan }}" data-search="{{ strtolower($item->id_pemesanan . ' ' . ($item->pelanggan->nama_pelanggan ?? '')) }}" data-angsuran="{{ number_format($item->angsuran, 2, '.', '') }}" {{ $pembayaran->id_pemesanan == $item->id_pemesanan ? 'selected' : '' }}>
                                    {{ $labelPemesanan }}
                                </option>
                            @endforeach
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Ketik ID atau nama pelanggan, lalu pilih pemesanan dari daftar.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="nominal" class="block text-sm font-semibold text-slate-700">Nominal Angsuran</label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">Rp</span>
                            <input id="nominal" type="text" value="{{ number_format($pemesanan->firstWhere('id_pemesanan', $pembayaran->id_pemesanan)?->angsuran ?? 0, 0, ',', '.') }}" readonly data-installment-display aria-describedby="nominal-hint" class="block min-w-0 flex-1 border-0 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0">
                        </div>
                        <p id="nominal-hint" class="mt-1 text-xs text-slate-500">Nominal diambil otomatis dari angsuran pada pemesanan yang dipilih.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="bukti_pembayaran" class="block text-sm font-semibold text-slate-700">Bukti Pembayaran</label>
                        @if ($pembayaran->bukti_pembayaran)
                            @php($buktiUrl = asset('storage/' . $pembayaran->bukti_pembayaran))
                            @php($buktiExt = strtolower(pathinfo($pembayaran->bukti_pembayaran, PATHINFO_EXTENSION)))
                            @if (in_array($buktiExt, ['jpg', 'jpeg', 'png', 'webp'], true))
                                <button type="button" data-photo-preview="{{ $buktiUrl }}" data-photo-alt="Bukti pembayaran saat ini" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-blue-800 underline">Lihat bukti saat ini</button>
                            @else
                                <button type="button" data-file-preview="{{ $buktiUrl }}" data-file-name="Bukti pembayaran saat ini" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-blue-800 underline">Lihat bukti saat ini</button>
                            @endif
                        @endif
                        <div class="mt-2 space-y-2">
                            <input id="bukti_pembayaran" type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/webp,application/pdf" data-upload-preview-input data-max-size="5242880" data-allowed-label="JPG, PNG, WEBP, atau PDF" class="form-control @error('bukti_pembayaran') border-rose-400 @enderror">
                        <div data-upload-preview-box class="hidden mt-3 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-3" aria-live="polite">
                            <button type="button" data-upload-preview-image-button class="hidden cursor-zoom-in rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" aria-label="Perbesar pratinjau bukti pembayaran">
                                <img data-upload-preview-image src="" alt="Pratinjau bukti pembayaran baru" class="h-36 w-56 rounded-lg bg-slate-100 object-contain">
                            </button>
                            <button type="button" data-upload-preview-file-button class="hidden rounded-lg bg-slate-100 px-4 py-3 text-sm font-semibold text-blue-800"><i class="icon-base bx bx-file-pdf me-1" aria-hidden="true"></i>Lihat PDF</button>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-medium text-slate-500">File siap diunggah</span><span data-upload-preview-name class="mt-0.5 block truncate text-sm font-semibold text-slate-800"></span></span>
                            <button type="button" data-clear-upload-preview class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-xl leading-none text-slate-500 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" aria-label="Hapus file yang dipilih" title="Hapus file yang dipilih">&times;</button>
                        </div>
                        <p data-upload-preview-error role="alert" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Pilih file baru untuk mengganti bukti lama. JPG, PNG, WEBP, atau PDF; maksimal 5 MB.</p>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan Perubahan</button>
                    <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
