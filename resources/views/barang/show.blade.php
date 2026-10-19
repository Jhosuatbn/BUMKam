@extends('layouts.app')

@section('title', 'Detail Barang')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Detail Barang</h1>
                <p class="mt-2 text-sm text-slate-600">Informasi barang dan harga yang tercatat.</p>
            </div>
            <a href="{{ route('barang.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-detail-heading">
            <h2 id="barang-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">Informasi Barang</h2>
            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Barang</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $barang->id_barang }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Nama Barang</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $barang->nama_barang }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Kategori</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $barang->kategori }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Harga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($barang->harga, 0, ',', '.') }}</dd></div>
            </dl>
            <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                @if(auth()->check() && auth()->user()->role !== 'pimpinan')
                    <a href="{{ route('barang.edit', $barang->id_barang) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Edit</a>
                @endif
                <a href="{{ route('barang.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Daftar Barang</a>
            </div>
        </section>
    </div>
@endsection