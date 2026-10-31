@extends('layouts.sneat-dashboard')

@section('title', 'Tambah Barang')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Barang</h1>
                <p class="mt-2 text-sm text-slate-600">Lengkapi informasi barang baru.</p>
            </div>
            <a href="{{ route('barang.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="barang-form-heading">
            <h2 id="barang-form-heading" class="text-base font-semibold text-slate-900">Informasi Barang</h2>
            <form action="{{ route('barang.store') }}" method="POST" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="kategori" class="block text-sm font-semibold text-slate-700">Kategori</label>
                        <select id="kategori" name="kategori" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="id_barang_notice" class="block text-sm font-semibold text-slate-700">ID Barang</label>
                        <input id="id_barang_notice" type="text" value="Dibuat otomatis berdasarkan kategori" disabled class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 italic">
                    </div>
                    <div class="md:col-span-2">
                        <label for="nama_barang" class="block text-sm font-semibold text-slate-700">Nama Barang</label>
                        <input id="nama_barang" type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Kursi" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="harga" class="block text-sm font-semibold text-slate-700">Harga</label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">Rp</span>
                            <input id="harga" type="text" inputmode="numeric" data-rupiah-input name="harga" value="{{ old('harga') }}" placeholder="Contoh: 1.500.000" required class="block min-w-0 flex-1 border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:outline-none focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="stok" class="block text-sm font-semibold text-slate-700">Stok awal (opsional)</label>
                        <input id="stok" type="number" name="stok" value="{{ old('stok') }}" min="0" step="1" placeholder="Kosongkan jika belum diketahui" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan</button>
                    <a href="{{ route('barang.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
