
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
                @if (auth()->user()->peran === 'Pimpinan')
                    <h2 class="text-sm font-semibold text-slate-600">Jumlah Kredit</h2>
                    <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalKredit ?? 0 }}</p>
                @else
                    <h2 class="text-sm font-semibold text-slate-600">Pembayaran</h2>
                    <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">{{ $totalPembayaran }}</p>
                @endif
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

        
@if (auth()->user()->peran === 'Pimpinan')
    {{-- Analisis kinerja bulanan khusus Pimpinan --}}
    <section class="space-y-5" aria-labelledby="analisis-pimpinan-heading">
        <div>
            <h2 id="analisis-pimpinan-heading"
                class="text-xl font-bold text-slate-900">
                Analisis Kinerja Bulanan
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Perbandingan aktivitas pemesanan bulan ini dengan bulan sebelumnya.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-blue-900">
                        Transaksi Bulan Ini
                    </p>
                    <span class="rounded-xl bg-white p-2 text-blue-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M3 3v18h18"/>
                            <path d="m19 9-5 5-4-4-4 4"/>
                        </svg>
                    </span>
                </div>

                <p class="mt-5 text-3xl font-bold text-blue-950">
                    {{ $transaksiBulanIni }}
                </p>
                <p class="mt-1 text-sm text-blue-800">
                    Total pemesanan pada bulan berjalan
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-600">
                        Transaksi Bulan Lalu
                    </p>
                    <span class="rounded-xl bg-slate-100 p-2 text-slate-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <rect x="3" y="4" width="18" height="17" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </span>
                </div>

                <p class="mt-5 text-3xl font-bold text-slate-900">
                    {{ $transaksiBulanLalu }}
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Total pemesanan bulan sebelumnya
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-600">
                        Perubahan Transaksi
                    </p>
                    <span class="rounded-xl bg-slate-100 p-2 text-slate-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M3 17 9 11l4 4 8-8"/>
                            <path d="M15 7h6v6"/>
                        </svg>
                    </span>
                </div>

                <p class="mt-5 text-3xl font-bold
                    {{ $transaksiBulanIni > $transaksiBulanLalu
                        ? 'text-emerald-700'
                        : ($transaksiBulanIni < $transaksiBulanLalu
                            ? 'text-rose-700'
                            : 'text-slate-900') }}">
                    @if ($transaksiBulanLalu > 0)
                        {{ $persentasePerubahan > 0 ? '+' : '' }}{{ number_format($persentasePerubahan, 1, ',', '.') }}%
                    @elseif ($transaksiBulanIni > 0)
                        Data pembanding belum ada
                    @else
                        0%
                    @endif
                </p>


<p class="mt-1 text-sm text-slate-500">
    @if ($transaksiBulanLalu == 0 && $transaksiBulanIni > 0)
        Ada transaksi bulan ini, tetapi belum ada transaksi bulan lalu sebagai pembanding.
    @elseif ($transaksiBulanIni > $transaksiBulanLalu)
        Transaksi meningkat dibanding bulan lalu.
    @elseif ($transaksiBulanIni < $transaksiBulanLalu)
        Transaksi menurun dibanding bulan lalu.
    @else
        Jumlah transaksi sama dengan bulan lalu.
    @endif
</p>

            </article>
        </div>
    </section>

    {{-- Transaksi terbaru khusus Pimpinan --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        aria-labelledby="transaksi-terbaru-heading">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="transaksi-terbaru-heading"
                class="text-lg font-bold text-slate-900">
                Transaksi Terbaru
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Lima transaksi pemesanan terbaru.
            </p>
        </div>

        @if ($transaksiTerbaru->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3 font-semibold">Pelanggan</th>
                            <th class="px-5 py-3 font-semibold">Total Harga</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaksiTerbaru as $transaksi)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal_pemesanan)->format('d-m-Y') }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $transaksi->pelanggan?->nama_pelanggan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    {{ $transaksi->status_pemesanan }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-5 py-10 text-center text-sm text-slate-600">
                Belum ada transaksi pemesanan.
            </p>
        @endif
    </section>
@else

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
        @endif
    </div>
@endsection
