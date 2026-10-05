@extends('layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Detail Pembayaran</h1>
                <p class="mt-2 text-sm text-slate-600">Rincian pembayaran dan status pemesanan.</p>
            </div>
            <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pembayaran-detail-heading">
            <h2 id="pembayaran-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">Informasi Pembayaran</h2>
            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pembayaran</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->id_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pemesanan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->id_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Pelanggan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->pemesanan->pelanggan->nama_pelanggan ?? '-' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tanggal Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->tanggal_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Nominal</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Total Pembayaran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Sisa Pembayaran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->sisa_pembayaran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->status_pembayaran }}</dd></div>
            </dl>
            <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                <a href="{{ route('pembayaran.edit', $pembayaran->id_pembayaran) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Edit</a>
                <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Daftar Pembayaran</a>
            </div>
        </section>
    </div>
@endsection
