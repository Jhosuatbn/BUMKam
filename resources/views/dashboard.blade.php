
@extends('layouts.app')

@section('title', auth()->user()->peran === 'Pimpinan' ? 'Ringkasan Eksekutif' : 'Dashboard')

@section('content')
    <div class="space-y-8">
        <header @class([
            'flex flex-col justify-between gap-4 pb-6 sm:flex-row sm:items-end',
            'relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0b1f3a] via-blue-950 to-blue-800 px-6 py-7 text-white shadow-lg sm:px-8' => auth()->user()->peran === 'Pimpinan',
            'border-b border-slate-200' => auth()->user()->peran !== 'Pimpinan',
        ])>
            @if (auth()->user()->peran === 'Pimpinan')
                <span class="absolute -right-8 -top-16 h-48 w-48 rounded-full border-[24px] border-white/5" aria-hidden="true"></span>
            @endif
            <div>
                <h1 @class([
                    'text-2xl font-bold tracking-tight sm:text-3xl',
                    'text-white' => auth()->user()->peran === 'Pimpinan',
                    'text-slate-900' => auth()->user()->peran !== 'Pimpinan',
                ])>{{ auth()->user()->peran === 'Pimpinan' ? 'Ringkasan Eksekutif' : 'Dashboard' }}</h1>
                <p @class([
                    'mt-2 text-sm sm:text-base',
                    'text-blue-100' => auth()->user()->peran === 'Pimpinan',
                    'text-slate-600' => auth()->user()->peran !== 'Pimpinan',
                ])>{{ auth()->user()->peran === 'Pimpinan' ? 'Pantauan menyeluruh aktivitas BUMKam Tuah Rezeki' : 'Ringkasan operasional BUMKam Tuah Rezeki' }}</p>
            </div>
            <p @class([
                'text-sm font-medium',
                'text-blue-100' => auth()->user()->peran === 'Pimpinan',
                'text-slate-500' => auth()->user()->peran !== 'Pimpinan',
            ])>{{ auth()->user()->peran === 'Pimpinan' ? \Carbon\Carbon::now()->locale('id')->translatedFormat('F Y') : 'Ringkasan kinerja' }}</p>
        </header>

        {{-- Ringkasan --}}
        @if (auth()->user()->peran === 'Pimpinan')
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Ikhtisar BUMKam</h2>
                    <p class="text-sm text-slate-500">Gambaran data usaha yang mendukung keputusan pimpinan.</p>
                </div>
                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Data utama</span>
            </div>
        @endif

        <section @class([
            'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4',
            'mt-4' => auth()->user()->peran === 'Pimpinan',
        ]) aria-label="Ringkasan kinerja">
            <article @class([
                'bg-white p-5 shadow-sm',
                'rounded-2xl border border-slate-200 transition-shadow hover:shadow-md' => auth()->user()->peran !== 'Pimpinan',
                'rounded-2xl border border-blue-100 border-t-4 border-t-blue-600 transition hover:shadow-md' => auth()->user()->peran === 'Pimpinan',
            ])>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-600">Pelanggan</h2>
                    @if (auth()->user()->peran === 'Pimpinan')
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6"/></svg></span>
                    @endif
                </div>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPelanggan }}</p>
                @if (auth()->user()->peran === 'Pimpinan')<p class="mt-1 text-xs text-slate-500">Terdaftar dalam sistem</p>@endif
            </article>

            <article @class([
                'bg-white p-5 shadow-sm',
                'rounded-2xl border border-slate-200 transition-shadow hover:shadow-md' => auth()->user()->peran !== 'Pimpinan',
                'rounded-2xl border border-cyan-100 border-t-4 border-t-cyan-500 transition hover:shadow-md' => auth()->user()->peran === 'Pimpinan',
            ])>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-600">Barang</h2>
                    @if (auth()->user()->peran === 'Pimpinan')
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-cyan-50 text-cyan-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg></span>
                    @endif
                </div>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalBarang }}</p>
                @if (auth()->user()->peran === 'Pimpinan')<p class="mt-1 text-xs text-slate-500">Tersedia dalam katalog</p>@endif
            </article>

            <article @class([
                'p-5 shadow-sm',
                'rounded-2xl border border-blue-200 bg-blue-50/70 transition-shadow hover:shadow-md' => auth()->user()->peran !== 'Pimpinan',
                'rounded-2xl border border-blue-800 bg-gradient-to-br from-blue-800 to-blue-950 text-white transition hover:shadow-md' => auth()->user()->peran === 'Pimpinan',
            ])>
                <div class="flex items-center justify-between gap-3">
                    <h2 @class(['text-sm font-semibold', 'text-blue-100' => auth()->user()->peran === 'Pimpinan', 'text-blue-900' => auth()->user()->peran !== 'Pimpinan'])>Pemesanan</h2>
                    @if (auth()->user()->peran === 'Pimpinan')
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 text-cyan-200" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-4 4"/></svg></span>
                    @endif
                </div>
                <p @class(['mt-4 tabular-nums text-3xl font-bold', 'text-white' => auth()->user()->peran === 'Pimpinan', 'text-blue-950' => auth()->user()->peran !== 'Pimpinan'])>{{ $totalPemesanan }}</p>
                @if (auth()->user()->peran === 'Pimpinan')<p class="mt-1 text-xs text-blue-200">Total pemesanan tercatat</p>@endif
            </article>

            <article @class([
                'bg-white p-5 shadow-sm',
                'rounded-2xl border border-slate-200 transition-shadow hover:shadow-md' => auth()->user()->peran !== 'Pimpinan',
                'rounded-2xl border border-emerald-100 border-t-4 border-t-emerald-500 transition hover:shadow-md' => auth()->user()->peran === 'Pimpinan',
            ])>
                @if (auth()->user()->peran === 'Pimpinan')
                    <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-600">Pelanggan Menggunakan Kredit</h2>
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h3"/></svg></span>
                    </div>
                    <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalKredit ?? 0 }}</p>
                    <p class="mt-1 text-xs text-slate-500">Pelanggan dengan skema kredit</p>
                @else
                    <h2 class="text-sm font-semibold text-slate-600">Pembayaran</h2>
                    <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPembayaran }}</p>
                @endif
            </article>
        </section>

        {{-- Grafik --}}
        @if (auth()->user()->peran === 'Pimpinan')
            <div class="flex flex-col gap-1 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Analitik Operasional</h2>
                    <p class="text-sm text-slate-500">Tren pemesanan dan status pembayaran yang tercatat.</p>
                </div>
                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Pantauan usaha</span>
            </div>
        @endif
        <section @class([
            'grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,0.8fr)]',
            'mt-4' => auth()->user()->peran === 'Pimpinan',
        ])>
            <article @class([
                'min-w-0 bg-white p-5 sm:p-6',
                'rounded-3xl border border-blue-100 shadow-md' => auth()->user()->peran === 'Pimpinan',
                'rounded-2xl border border-slate-200 shadow-sm' => auth()->user()->peran !== 'Pimpinan',
            ]) aria-labelledby="tren-pemesanan-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        @if (auth()->user()->peran === 'Pimpinan')
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-700">Perkembangan usaha</p>
                        @endif
                        <h2 id="tren-pemesanan-heading" class="text-lg font-bold text-slate-900">Tren Pemesanan</h2>
                        <p class="mt-1 text-sm text-slate-600">Jumlah pesanan selama enam bulan terakhir</p>
                    </div>
                    <span @class([
                        'rounded-full px-3 py-1.5 text-xs font-semibold',
                        'bg-blue-50 text-blue-900' => auth()->user()->peran !== 'Pimpinan',
                        'border border-blue-100 bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-800' => auth()->user()->peran === 'Pimpinan',
                    ])>{{ number_format($totalPemesanan, 0, ',', '.') }} total</span>
                </div>

                @if ($pemesananTrend->sum('jumlah') > 0)
                    <div @class([
                        'mt-5 w-full overflow-hidden',
                        'rounded-2xl bg-gradient-to-b from-blue-50/70 to-white px-1 py-3 sm:px-3' => auth()->user()->peran === 'Pimpinan',
                    ])>
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

            <article @class([
                'bg-white p-5 sm:p-6',
                'rounded-3xl border border-blue-100 shadow-md' => auth()->user()->peran === 'Pimpinan',
                'rounded-2xl border border-slate-200 shadow-sm' => auth()->user()->peran !== 'Pimpinan',
            ]) aria-labelledby="status-pembayaran-heading">
                <div>
                    @if (auth()->user()->peran === 'Pimpinan')
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Kesehatan pembayaran</p>
                    @endif
                    <h2 id="status-pembayaran-heading" class="text-lg font-bold text-slate-900">Status Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-600">Komposisi pembayaran tercatat</p>
                </div>

                @php($persentasePembayaranLunas = $totalStatusPembayaran > 0 ? round(($pembayaranLunas / $totalStatusPembayaran) * 100) : 0)
                <div @class([
                    'mt-5 flex flex-col items-center gap-5 sm:flex-row sm:justify-center xl:flex-col',
                    'rounded-2xl bg-gradient-to-br from-slate-50 to-white p-4' => auth()->user()->peran === 'Pimpinan',
                ])>
                    <div class="relative shrink-0">
                    <svg viewBox="0 0 180 180" class="size-44 shrink-0" role="img" aria-labelledby="status-chart-title status-chart-desc">
                        <title id="status-chart-title">Komposisi status pembayaran</title>
                        <desc id="status-chart-desc">{{ $pembayaranLunas }} lunas dan {{ $pembayaranBelumLunas }} belum lunas.</desc>
                        <circle cx="90" cy="90" r="72" fill="none" stroke="{{ auth()->user()->peran === 'Pimpinan' ? '#e2e8f0' : '#fef3c7' }}" stroke-width="20" />

                        @if ($totalStatusPembayaran > 0 && $pembayaranLunas > 0)
                            <circle cx="90" cy="90" r="72" fill="none" stroke="{{ auth()->user()->peran === 'Pimpinan' ? '#10b981' : '#2563eb' }}" stroke-width="20" stroke-linecap="round" stroke-dasharray="{{ number_format($dashGrafikPembayaran, 2, '.', '') }} {{ number_format($kelilingGrafikPembayaran, 2, '.', '') }}" transform="rotate(-90 90 90)" />
                        @endif

                        <text x="90" y="84" text-anchor="middle" fill="#0f172a" font-size="30" font-weight="700">{{ auth()->user()->peran === 'Pimpinan' ? $persentasePembayaranLunas . '%' : $totalStatusPembayaran }}</text>
                        <text x="90" y="106" text-anchor="middle" fill="#475569" font-size="12">{{ auth()->user()->peran === 'Pimpinan' ? 'lunas' : 'pembayaran' }}</text>
                    </svg>
                    </div>

                    <ul @class([
                        'w-full space-y-3 sm:max-w-40 xl:max-w-48',
                        'rounded-xl border border-slate-100 bg-white p-4 shadow-sm' => auth()->user()->peran === 'Pimpinan',
                    ]) aria-label="Rincian status pembayaran">
                        <li class="flex items-center justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span @class(['size-3 rounded-full', 'bg-blue-600' => auth()->user()->peran !== 'Pimpinan', 'bg-emerald-500' => auth()->user()->peran === 'Pimpinan']) aria-hidden="true"></span>Lunas
                            </span>
                            <span class="tabular-nums font-semibold text-slate-900">{{ $pembayaranLunas }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span @class(['size-3 rounded-full', 'bg-amber-400' => auth()->user()->peran !== 'Pimpinan', 'bg-slate-300' => auth()->user()->peran === 'Pimpinan']) aria-hidden="true"></span>Belum lunas
                            </span>
                            <span class="tabular-nums font-semibold text-slate-900">{{ $pembayaranBelumLunas }}</span>
                        </li>
                    </ul>
                </div>
            </article>
        </section>

        {{-- Konten berdasarkan peran pengguna --}}
        @if (auth()->user()->peran === 'Pimpinan')
            @include('dashboard-pimpinan')
        @else
            {{-- Barang paling banyak dipesan khusus Staff --}}
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

                                <span class="whitespace-nowrap text-sm font-semibold tabular-nums text-slate-700">
                                    {{ $item['jumlah'] }} <span class="font-normal text-slate-500">unit</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="px-5 py-10 text-center text-sm text-slate-600 sm:px-6">
                        Belum ada barang yang tercatat dalam pemesanan.
                    </p>
                @endif
            </section>
        @endif
    </div>
@endsection
