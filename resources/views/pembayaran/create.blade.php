@extends('layouts.app')

@section('title', 'Tambah Pembayaran')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Pembayaran</h1>
                <p class="mt-2 text-sm text-slate-600">Catat pembayaran untuk pemesanan.</p>
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
            <form action="{{ route('pembayaran.store') }}" method="POST" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pembayaran" class="block text-sm font-semibold text-slate-700">ID Pembayaran</label>
                        <input id="id_pembayaran" type="text" name="id_pembayaran" value="{{ old('id_pembayaran') }}" placeholder="Contoh: BYR-001" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div>
                        <label for="tanggal_pembayaran" class="block text-sm font-semibold text-slate-700">Tanggal Pembayaran</label>
                        <input id="tanggal_pembayaran" type="date" name="tanggal_pembayaran" value="{{ old('tanggal_pembayaran', date('Y-m-d')) }}" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="id_pemesanan" class="block text-sm font-semibold text-slate-700">Pemesanan</label>
                        <select id="id_pemesanan" name="id_pemesanan" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="">-- Pilih Pemesanan --</option>
                            @foreach ($pemesanan as $item)
                                <option value="{{ $item->id_pemesanan }}" {{ old('id_pemesanan') == $item->id_pemesanan ? 'selected' : '' }}>
                                    {{ $item->id_pemesanan }} - {{ $item->pelanggan->nama_pelanggan ?? '-' }} - Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="nominal" class="block text-sm font-semibold text-slate-700">Nominal Pembayaran</label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">Rp</span>
                            <input id="nominal" type="number" name="nominal" value="{{ old('nominal') }}" min="0" placeholder="Contoh: 500000" class="block min-w-0 flex-1 border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:outline-none focus:ring-0">
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan Pembayaran</button>
                    <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
