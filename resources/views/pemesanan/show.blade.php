@extends('layouts.app')

@section('title', 'Detail Pemesanan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Detail Pemesanan</h1>
                <p class="mt-2 text-sm text-slate-600">Ringkasan pelanggan, barang, dan skema pembayaran.</p>
            </div>
            <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pemesanan-detail-heading">
            <h2 id="pemesanan-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">Informasi Pemesanan</h2>
            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pemesanan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->id_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Pelanggan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tanggal</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->tanggal_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Skema Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->skema_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">DP</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->dp, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tenor</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->tenor }} bulan</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Jaminan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->jaminan ?? '-' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Bunga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->bunga, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Total Harga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Angsuran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->angsuran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Status</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->status_pemesanan }}</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-dipesan-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 id="barang-dipesan-heading" class="text-base font-semibold text-slate-900">Barang yang Dipesan</h2></div>
            <div class="divide-y divide-slate-100 px-5 sm:px-6">
                @forelse ($pemesanan->detailPemesanan as $detail)
                    <dl class="grid grid-cols-1 gap-x-8 py-4 sm:grid-cols-2">
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Barang</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $detail->barang->nama_barang ?? '-' }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Harga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($detail->harga, 0, ',', '.') }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Jumlah</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $detail->jumlah }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Subtotal</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</dd></div>
                    </dl>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Belum ada detail barang.</p>
                @endforelse
            </div>
            <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                <a href="{{ route('pemesanan.edit', $pemesanan->id_pemesanan) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Edit</a>
                <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Daftar Pemesanan</a>
            </div>
        </section>
    </div>
@endsection
