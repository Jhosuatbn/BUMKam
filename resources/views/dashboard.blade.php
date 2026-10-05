@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <header class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Ringkasan operasional BUMKam Tuah Rezeki</p>
            </div>
            <p class="text-sm font-medium text-slate-500">Ringkasan kinerja</p>
        </header>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan kinerja">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <h2 class="text-sm font-semibold text-slate-600">Pelanggan</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPelanggan }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <h2 class="text-sm font-semibold text-slate-600">Barang</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalBarang }}</p>
            </article>
            <article class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-sm transition-shadow hover:shadow-md">
                <h2 class="text-sm font-semibold text-blue-900">Pemesanan</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-blue-950">{{ $totalPemesanan }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <h2 class="text-sm font-semibold text-slate-600">Pembayaran</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPembayaran }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,0.8fr)]">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="tren-pemesanan-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="tren-pemesanan-heading" class="text-lg font-bold text-slate-900">Tren Pemesanan</h2>
                        <p class="mt-1 text-sm text-slate-600">Jumlah pesanan per bulan · 6 bulan terakhir</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-900">{{ $totalPemesanan }} total</span>
                </div>

                @if ($pemesananTrend->sum('jumlah') > 0)
                    <div class="mt-5 w-full overflow-hidden">
                        <svg viewBox="0 0 640 230" class="h-auto w-full" role="img" aria-labelledby="tren-chart-title tren-chart-desc">
                            <title id="tren-chart-title">Tren pemesanan enam bulan terakhir</title>
                            <desc id="tren-chart-desc">Grafik garis jumlah pemesanan per bulan, dengan nilai dan nama bulan di setiap titik.</desc>
                            <line x1="28" y1="44" x2="608" y2="44" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="28" y1="89" x2="608" y2="89" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="28" y1="134" x2="608" y2="134" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="28" y1="180" x2="608" y2="180" stroke="#cbd5e1" stroke-width="1" />
                            <polygon points="28,180 {{ $pemesananTrendCoordinates }} 608,180" fill="#dbeafe" fill-opacity="0.65" />
                            <polyline points="{{ $pemesananTrendCoordinates }}" fill="none" stroke="#2563eb" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" />
                            @foreach ($pemesananTrend as $point)
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="6" fill="#fff" stroke="#1d4ed8" stroke-width="3">
                                    <title>{{ $point['label'] }}: {{ $point['jumlah'] }} pemesanan</title>
                                </circle>
                                <text x="{{ $point['x'] }}" y="{{ min($point['y'] - 14, 202) }}" text-anchor="middle" fill="#1e3a8a" font-size="13" font-weight="700">{{ $point['jumlah'] }}</text>
                                <text x="{{ $point['x'] }}" y="216" text-anchor="middle" fill="#475569" font-size="12">{{ $point['label'] }}</text>
                            @endforeach
                        </svg>
                    </div>
                @else
                    <div class="mt-5 flex min-h-48 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center text-sm text-slate-600">
                        Belum ada pemesanan dalam periode enam bulan terakhir.
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="status-pembayaran-heading">
                <div>
                    <h2 id="status-pembayaran-heading" class="text-lg font-bold text-slate-900">Status Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-600">Komposisi pembayaran tercatat</p>
                </div>

                <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row sm:justify-center xl:flex-col">
                    <svg viewBox="0 0 180 180" class="size-44 shrink-0" role="img" aria-labelledby="status-chart-title status-chart-desc">
                        <title id="status-chart-title">Komposisi status pembayaran</title>
                        <desc id="status-chart-desc">{{ $pembayaranLunas }} lunas dan {{ $pembayaranBelumLunas }} belum lunas.</desc>
                        <circle cx="90" cy="90" r="72" fill="none" stroke="#fef3c7" stroke-width="20" />
                        @if ($totalStatusPembayaran > 0 && $pembayaranLunas > 0)
                            <circle cx="90" cy="90" r="72" fill="none" stroke="#2563eb" stroke-width="20" stroke-linecap="round" stroke-dasharray="{{ number_format($dashGrafikPembayaran, 2, '.', '') }} {{ number_format($kelilingGrafikPembayaran, 2, '.', '') }}" transform="rotate(-90 90 90)" />
                        @endif
                        <text x="90" y="84" text-anchor="middle" fill="#0f172a" font-size="30" font-weight="700">{{ $totalStatusPembayaran }}</text>
                        <text x="90" y="106" text-anchor="middle" fill="#475569" font-size="12">pembayaran</text>
                    </svg>

                    <ul class="w-full space-y-3 sm:max-w-40 xl:max-w-48" aria-label="Rincian status pembayaran">
                        <li class="flex items-center justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-700"><span class="size-3 rounded-full bg-blue-600" aria-hidden="true"></span>Lunas</span>
                            <span class="tabular-nums font-semibold text-slate-900">{{ $pembayaranLunas }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-700"><span class="size-3 rounded-full bg-amber-400" aria-hidden="true"></span>Belum lunas</span>
                            <span class="tabular-nums font-semibold text-slate-900">{{ $pembayaranBelumLunas }}</span>
                        </li>
                    </ul>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-terlaris-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="barang-terlaris-heading" class="text-lg font-bold text-slate-900">Barang Paling Banyak Dipesan</h2>
                <p class="mt-1 text-sm text-slate-600">Berdasarkan jumlah unit pada detail pemesanan.</p>
            </div>
            @if ($barangTerlaris->isNotEmpty())
                <ol class="space-y-5 px-5 py-5 sm:px-6">
                    @foreach ($barangTerlaris as $item)
                        @php($lebarBar = max(4, (int) round(($item['jumlah'] / $maxBarangDipesan) * 100)))
                        <li class="grid grid-cols-[minmax(6rem,0.8fr)_minmax(0,2fr)_auto] items-center gap-3 sm:gap-5">
                            <span class="min-w-0 break-words text-sm font-medium text-slate-800">{{ $item['nama'] }}</span>
                            <div class="h-4 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $item['nama'] }}: {{ $item['jumlah'] }} unit dipesan">
                                <div class="h-full rounded-full bg-blue-600" style="width: {{ $lebarBar }}%"></div>
                            </div>
                            <span class="whitespace-nowrap text-sm font-semibold tabular-nums text-slate-700">{{ $item['jumlah'] }} <span class="font-normal text-slate-500">unit</span></span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="px-5 py-10 text-center text-sm text-slate-600 sm:px-6">Belum ada barang yang tercatat dalam pemesanan.</p>
            @endif
        </section>
    </div>
@endsection
