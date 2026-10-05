@extends('layouts.app')

@section('title', 'Tambah Pemesanan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Pemesanan</h1>
                <p class="mt-2 text-sm text-slate-600">Isi data pelanggan, barang, dan skema pembayaran.</p>
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('pemesanan.store') }}" method="POST" class="space-y-8">
                @csrf
                <fieldset>
                    <legend class="text-base font-semibold text-slate-900">Informasi Pemesanan</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div>
                            <label for="id_pemesanan" class="block text-sm font-semibold text-slate-700">ID Pemesanan</label>
                            <input id="id_pemesanan" type="text" name="id_pemesanan" value="{{ old('id_pemesanan') }}" placeholder="Contoh: PM001" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div>
                            <label for="tanggal_pemesanan" class="block text-sm font-semibold text-slate-700">Tanggal Pemesanan</label>
                            <input id="tanggal_pemesanan" type="date" name="tanggal_pemesanan" value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div class="md:col-span-2">
                            <label for="id_pelanggan" class="block text-sm font-semibold text-slate-700">Pelanggan</label>
                            <select id="id_pelanggan" name="id_pelanggan" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach ($pelanggan as $item)
                                    <option value="{{ $item->id_pelanggan }}" {{ old('id_pelanggan') == $item->id_pelanggan ? 'selected' : '' }}>{{ $item->nama_pelanggan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t border-slate-200 pt-6">
                    <legend class="text-base font-semibold text-slate-900">Barang</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="id_barang" class="block text-sm font-semibold text-slate-700">Barang</label>
                            <select id="id_barang" name="id_barang" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barang as $item)
                                    <option value="{{ $item->id_barang }}" {{ old('id_barang') == $item->id_barang ? 'selected' : '' }}>{{ $item->nama_barang }} - Rp {{ number_format($item->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="jumlah" class="block text-sm font-semibold text-slate-700">Jumlah</label>
                            <input id="jumlah" type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t border-slate-200 pt-6">
                    <legend class="text-base font-semibold text-slate-900">Skema Pembayaran</legend>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                        <div>
                            <label for="skema_pembayaran" class="block text-sm font-semibold text-slate-700">Skema Pembayaran</label>
                            <select id="skema_pembayaran" name="skema_pembayaran" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                                <option value="">-- Pilih Skema --</option>
                                <option value="DP" {{ old('skema_pembayaran') == 'DP' ? 'selected' : '' }}>DP</option>
                                <option value="Sebagian" {{ old('skema_pembayaran') == 'Sebagian' ? 'selected' : '' }}>Sebagian</option>
                                <option value="Tanpa DP" {{ old('skema_pembayaran') == 'Tanpa DP' ? 'selected' : '' }}>Tanpa DP</option>
                            </select>
                        </div>
                        <div>
                            <label for="dp" class="block text-sm font-semibold text-slate-700">Jumlah DP</label>
                            <input id="dp" type="number" name="dp" value="{{ old('dp', 0) }}" min="0" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div>
                            <label for="tenor" class="block text-sm font-semibold text-slate-700">Tenor (bulan)</label>
                            <input id="tenor" type="number" name="tenor" value="{{ old('tenor') }}" min="1" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div>
                            <label for="bunga" class="block text-sm font-semibold text-slate-700">Bunga</label>
                            <input id="bunga" type="number" name="bunga" value="{{ old('bunga', 0) }}" min="0" step="0.01" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        </div>
                        <div class="md:col-span-2">
                            <label for="jaminan" class="block text-sm font-semibold text-slate-700">Jaminan</label>
                            <textarea id="jaminan" name="jaminan" rows="3" placeholder="Masukkan jaminan jika ada" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">{{ old('jaminan') }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan Pemesanan</button>
                    <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
