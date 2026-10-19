
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('barang.index') }}"
           class="text-sm font-semibold text-blue-700 hover:underline">
            &larr; Kembali ke Daftar Barang
        </a>

        <h1 class="mt-3 text-2xl font-bold text-slate-900">
            Detail Barang
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Informasi lengkap barang yang tersedia.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-semibold text-slate-900">
                {{ $barang->nama_barang }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                ID Barang: {{ $barang->id_barang }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:px-6">
            <div>
                <p class="text-sm font-medium text-slate-500">Nama Barang</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $barang->nama_barang }}
                </p>
            </div>

            <div>
                <p class="text-sm font-medium text-slate-500">Harga Barang</p>
                <p class="mt-1 font-semibold text-slate-900">
                    Rp {{ number_format($barang->harga, 0, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-sm font-medium text-slate-500">Stok</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $barang->stok }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
            @if (auth()->check() && auth()->user()->peran !== 'Pimpinan')
                <a href="{{ route('barang.edit', $barang->id_barang) }}"
                   class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">
                    Edit
                </a>
            @endif

            <a href="{{ route('barang.index') }}"
               class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                Daftar Barang
            </a>
        </div>
    </div>
</div>
@endsection
