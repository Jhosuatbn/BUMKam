@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Detail Pelanggan</h1>
                <p class="mt-2 text-sm text-slate-600">Identitas dan informasi kontak pelanggan.</p>
            </div>

            <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                Kembali
            </a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pelanggan-detail-heading">
            <h2 id="pelanggan-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">
                Informasi Pelanggan
            </h2>

            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4">
                    <dt class="text-sm font-medium text-slate-500">ID Pelanggan</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pelanggan->id_pelanggan }}</dd>
                </div>

                <div class="border-b border-slate-100 py-4">
                    <dt class="text-sm font-medium text-slate-500">Nama Pelanggan</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pelanggan->nama_pelanggan }}</dd>
                </div>

                <div class="border-b border-slate-100 py-4">
                    <dt class="text-sm font-medium text-slate-500">NIK</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pelanggan->nik }}</dd>
                </div>

                <div class="border-b border-slate-100 py-4">
                    <dt class="text-sm font-medium text-slate-500">No. Telepon</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pelanggan->no_telepon }}</dd>
                </div>

                <div class="border-b border-slate-100 py-4 sm:col-span-2">
                    <dt class="text-sm font-medium text-slate-500">Alamat</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pelanggan->alamat }}</dd>
                </div>
            </dl>

            <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                {{-- Tombol Edit hanya untuk Staff --}}
                @if (auth()->user()->peran !== 'Pimpinan')
                    <a href="{{ route('pelanggan.edit', $pelanggan->id_pelanggan) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                        Edit
                    </a>
                @endif

                <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    Daftar Pelanggan
                </a>
            </div>
        </section>
    </div>
@endsection
