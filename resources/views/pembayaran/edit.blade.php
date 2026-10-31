@extends('layouts.sneat-dashboard')

@section('title', 'Edit Pembayaran')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Edit Pembayaran</h1>
                <p class="mt-2 text-sm text-slate-600">Perbarui informasi pembayaran.</p>
            </div>
            <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
        </header>

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
                            <a href="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-blue-800 underline">Lihat bukti saat ini</a>
                        @endif
                        <input id="bukti_pembayaran" type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/webp,application/pdf" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-semibold file:text-blue-800">
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
