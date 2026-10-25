@extends('layouts.app')

@section('title', 'Edit Pemesanan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Edit Pemesanan</h1>
                <p class="mt-2 text-sm text-slate-600">Perbarui data pemesanan, barang, dan skema pembayaran.</p>
            </div>
            <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
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

        @php
            $detail = $pemesanan->detailPemesanan->first();
        @endphp

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('pemesanan.update', $pemesanan->id_pemesanan) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                <fieldset>
                    <legend class="text-base font-semibold text-slate-900">Informasi Pemesanan</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div>
                            <label for="id_pemesanan_display" class="block text-sm font-semibold text-slate-700">ID Pemesanan</label>
                            <input id="id_pemesanan_display" type="text" value="{{ $pemesanan->id_pemesanan }}" disabled class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600">
                        </div>
                        <div>
                            <label for="tanggal_pemesanan" class="block text-sm font-semibold text-slate-700">Tanggal Pemesanan</label>
                            <input id="tanggal_pemesanan" type="date" name="tanggal_pemesanan" value="{{ $pemesanan->tanggal_pemesanan }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div class="md:col-span-2">
                            <label for="cari_pelanggan" class="block text-sm font-semibold text-slate-700">Pelanggan</label>
                            <div class="relative mt-2" data-searchable-select>
                                <input id="cari_pelanggan" type="search" required role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="daftarPelanggan" autocomplete="off" placeholder="Cari nama atau ID pelanggan..." data-searchable-input class="block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <div id="daftarPelanggan" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                                <select id="id_pelanggan" name="id_pelanggan" class="hidden" tabindex="-1" aria-hidden="true" data-searchable-options>
                                @foreach ($pelanggan as $item)
                                    <option value="{{ $item->id_pelanggan }}" data-search="{{ strtolower($item->id_pelanggan . ' ' . $item->nama_pelanggan) }}" {{ $pemesanan->id_pelanggan == $item->id_pelanggan ? 'selected' : '' }}>{{ $item->nama_pelanggan }} ({{ $item->id_pelanggan }})</option>
                                @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Ketik nama atau ID, lalu pilih pelanggan dari daftar.</p>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t border-slate-200 pt-6">
                    <legend class="text-base font-semibold text-slate-900">Barang</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="cari_barang" class="block text-sm font-semibold text-slate-700">Barang</label>
                            <div class="relative mt-2" data-searchable-select>
                                <input id="cari_barang" type="search" required role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="daftarBarang" autocomplete="off" placeholder="Cari nama atau ID barang..." data-searchable-input class="block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <div id="daftarBarang" role="listbox" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg" data-searchable-results></div>
                                <select id="id_barang" name="id_barang" class="hidden" tabindex="-1" aria-hidden="true" data-searchable-options>
                                @foreach ($barang as $item)
                                    <option value="{{ $item->id_barang }}" data-search="{{ strtolower($item->id_barang . ' ' . $item->nama_barang) }}" {{ $detail && $detail->id_barang == $item->id_barang ? 'selected' : '' }}>{{ $item->nama_barang }} - Rp {{ number_format($item->harga, 0, ',', '.') }}</option>
                                @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Ketik nama atau ID, lalu pilih barang dari daftar.</p>
                        </div>
                        <div>
                            <label for="jumlah" class="block text-sm font-semibold text-slate-700">Jumlah</label>
                            <input id="jumlah" type="number" name="jumlah" value="{{ $detail->jumlah ?? 1 }}" min="1" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t border-slate-200 pt-6">
                    <legend class="text-base font-semibold text-slate-900">Skema Pembayaran</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div>
                            <label for="skema_pembayaran" class="block text-sm font-semibold text-slate-700">Skema Pembayaran</label>
                            <select id="skema_pembayaran" name="skema_pembayaran" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <option value="DP" {{ $pemesanan->skema_pembayaran == 'DP' ? 'selected' : '' }}>DP</option>
                                <option value="Sebagian" {{ $pemesanan->skema_pembayaran == 'Sebagian' ? 'selected' : '' }}>Sebagian</option>
                                <option value="Tanpa DP" {{ $pemesanan->skema_pembayaran == 'Tanpa DP' ? 'selected' : '' }}>Tanpa DP</option>
                            </select>
                        </div>
                        <div>
                            <label for="dp" class="block text-sm font-semibold text-slate-700">Jumlah DP</label>
                            <input id="dp" type="number" name="dp" value="{{ $pemesanan->dp }}" min="0" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div>
                            <label for="tenor" class="block text-sm font-semibold text-slate-700">Tenor (bulan)</label>
                            <input id="tenor" type="number" name="tenor" value="{{ $pemesanan->tenor }}" min="1" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div>
                            <label for="bunga" class="block text-sm font-semibold text-slate-700">Bunga</label>
                            <input id="bunga" type="number" name="bunga" value="{{ $pemesanan->bunga }}" min="0" step="0.01" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div class="md:col-span-2">
                            <label for="jaminan" class="block text-sm font-semibold text-slate-700">Jaminan</label>
                            <textarea id="jaminan" name="jaminan" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">{{ $pemesanan->jaminan }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Update Pemesanan</button>
                    <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
