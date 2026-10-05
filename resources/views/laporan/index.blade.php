@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="space-y-8">
        <header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Laporan</h1>
            <p class="mt-2 text-sm text-slate-600">Ringkasan pemesanan dan pembayaran BUMKam.</p>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="filter-heading">
            <div class="mb-5">
                <h2 id="filter-heading" class="text-base font-semibold text-slate-900">Filter Periode</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih rentang tanggal untuk menyaring laporan.</p>
            </div>
            <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700">Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700">Sampai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Terapkan Filter</button>
                @if (request('start_date') || request('end_date'))
                    <a href="{{ route('laporan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Reset</a>
                @endif
            </form>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-slate-600">Total Pemesanan</h2><p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPemesanan }}</p></article>
            <article class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-sm"><h2 class="text-sm font-semibold text-blue-900">Total Pembayaran</h2><p class="mt-4 tabular-nums text-2xl font-bold text-blue-950">Rp {{ number_format($totalPembayaran ?? 0, 0, ',', '.') }}</p></article>
            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><h2 class="text-sm font-semibold text-emerald-900">Lunas</h2><p class="mt-4 tabular-nums text-3xl font-bold text-emerald-950">{{ $pemesananLunas }}</p></article>
            <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"><h2 class="text-sm font-semibold text-amber-950">Belum Lunas</h2><p class="mt-4 tabular-nums text-3xl font-bold text-amber-950">{{ $pemesananBelumLunas }}</p></article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="latest-orders-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 id="latest-orders-heading" class="text-base font-semibold text-slate-900">Pemesanan Terbaru</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-[48rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600"><tr><th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID Pemesanan</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Tanggal</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Status</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pemesananTerbaru as $pemesanan)
                            <tr class="hover:bg-slate-50/70"><td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $pemesanan->id_pemesanan }}</td><td class="px-4 py-3.5 sm:px-6">{{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}</td><td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $pemesanan->tanggal_pemesanan }}</td><td class="px-4 py-3.5 sm:px-6">{{ $pemesanan->status_pemesanan }}</td><td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data pemesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="latest-payments-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 id="latest-payments-heading" class="text-base font-semibold text-slate-900">Pembayaran Terbaru</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-[48rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600"><tr><th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID Pembayaran</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pemesanan</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Tanggal</th><th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nominal</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pembayaranTerbaru as $pembayaran)
                            <tr class="hover:bg-slate-50/70"><td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $pembayaran->id_pembayaran }}</td><td class="px-4 py-3.5 sm:px-6">{{ $pembayaran->pemesanan->id_pemesanan ?? '-' }}</td><td class="px-4 py-3.5 sm:px-6">{{ $pembayaran->pemesanan->pelanggan->nama_pelanggan ?? '-' }}</td><td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $pembayaran->tanggal_pembayaran }}</td><td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
