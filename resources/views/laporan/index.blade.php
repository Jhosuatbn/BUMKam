
@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    @php
        $tanggalCetak = now()->locale('id')->translatedFormat('d F Y');

        $mulai = request('start_date')
            ? \Carbon\Carbon::parse(request('start_date'))->locale('id')->translatedFormat('d F Y')
            : 'Semua tanggal';

        $sampai = request('end_date')
            ? \Carbon\Carbon::parse(request('end_date'))->locale('id')->translatedFormat('d F Y')
            : 'Sampai saat ini';
    @endphp

    <div class="space-y-8 report-container">

        {{-- Kop khusus hasil cetak: hanya satu --}}
        <div class="print-only-heading">
            <div class="print-company-name">BUMKAM TUAH REZEKI</div>
            <div class="print-company-subtitle">BADAN USAHA MILIK KAMPUNG</div>
            <div class="print-divider"></div>

            <h1>LAPORAN PEMESANAN DAN PEMBAYARAN</h1>
            <p>
                Periode: {{ $mulai }} — {{ $sampai }}
            </p>
            <p class="print-date">
                Tanggal cetak: {{ $tanggalCetak }}
            </p>
        </div>

        {{-- Header untuk tampilan website --}}
        <header class="screen-only flex flex-col justify-between gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Laporan
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Ringkasan pemesanan dan pembayaran BUMKam.
                </p>
            </div>

            @if (auth()->user()->peran === 'Pimpinan')
                <button
                    type="button"
                    onclick="window.print()"
                    class="no-print inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 9V2h12v7"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <path d="M6 14h12v8H6z"/>
                    </svg>
                    Cetak Laporan / PDF
                </button>
            @endif
        </header>

        {{-- Filter: tidak ikut tercetak --}}
        <section
            class="print-hide rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
            aria-labelledby="filter-heading"
        >
            <div class="mb-5">
                <h2 id="filter-heading" class="text-base font-semibold text-slate-900">
                    Filter Periode
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Pilih rentang tanggal untuk menyaring laporan.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('laporan.index') }}"
                class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]"
            >
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700">
                        Mulai
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ request('start_date') }}"
                        class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900"
                    >
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700">
                        Sampai
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        value="{{ request('end_date') }}"
                        class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    Terapkan Filter
                </button>

                @if (request('start_date') || request('end_date'))
                    <a
                        href="{{ route('laporan.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </section>

        {{-- Ringkasan --}}
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Ringkasan laporan"
        >
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-600">Total Pemesanan</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-slate-900">
                    {{ $totalPemesanan }}
                </p>
            </article>

            <article class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-blue-900">Total Pembayaran</h2>
                <p class="mt-4 tabular-nums text-2xl font-bold text-blue-950">
                    Rp {{ number_format($totalPembayaran ?? 0, 0, ',', '.') }}
                </p>
            </article>

            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-emerald-900">Lunas</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-emerald-950">
                    {{ $pemesananLunas }}
                </p>
            </article>

            <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-amber-950">Belum Lunas</h2>
                <p class="mt-4 tabular-nums text-3xl font-bold text-amber-950">
                    {{ $pemesananBelumLunas }}
                </p>
            </article>
        </section>

        {{-- Tabel pemesanan --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-labelledby="latest-orders-heading"
        >
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="latest-orders-heading" class="text-base font-semibold text-slate-900">
                    A. Pemesanan Terbaru
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[48rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-6">ID Pemesanan</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Tanggal</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Status</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pemesananTerbaru as $pemesanan)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">
                                    {{ $pemesanan->id_pemesanan }}
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    {{ $pemesanan->tanggal_pemesanan }}
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $pemesanan->status_pemesanan }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    Belum ada data pemesanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Tabel pembayaran --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-labelledby="latest-payments-heading"
        >
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="latest-payments-heading" class="text-base font-semibold text-slate-900">
                    B. Pembayaran Terbaru
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[48rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-6">ID Pembayaran</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Pemesanan</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Tanggal</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pembayaranTerbaru as $pembayaran)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">
                                    {{ $pembayaran->id_pembayaran }}
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $pembayaran->pemesanan->id_pemesanan ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $pembayaran->pemesanan->pelanggan->nama_pelanggan ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    {{ $pembayaran->tanggal_pembayaran }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    Belum ada data pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Pengesahan: hanya muncul saat dicetak --}}
        <div class="print-signature print-only">
            <p>Pimpinan BUMKam Tuah Rezeki</p>
            <div class="signature-space"></div>
            <p class="signature-line">Nama dan tanda tangan</p>
        </div>

    </div>
@endsection

@push('styles')
<style>
    .print-only-heading,
    .print-only {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        html,
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #1f2937 !important;
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 9pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sembunyikan navigasi, header web, filter dan tombol */
        .app-sidebar,
        .app-header,
        .screen-only,
        .print-hide,
        .no-print,
        nav,
        button,
        form,
        details,
        summary {
            display: none !important;
        }

        body > div,
        body > div > div,
        #main-content,
        #main-content > div {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        .report-container {
            display: block !important;
            width: 100% !important;
        }

        .print-only-heading {
            display: block !important;
            text-align: center !important;
            margin: 0 0 12px !important;
            padding: 0 0 8px !important;
            color: #111827 !important;
        }

        .print-company-name {
            font-size: 17pt !important;
            font-weight: 800 !important;
            letter-spacing: 1.2px !important;
            margin-bottom: 4px !important;
        }

        .print-company-subtitle {
            font-size: 8pt !important;
            letter-spacing: 2px !important;
            color: #374151 !important;
        }

        .print-divider {
            height: 5px !important;
            margin: 9px 0 12px !important;
            border-top: 3px solid #172b4d !important;
            border-bottom: 1px solid #172b4d !important;
        }

        .print-only-heading h1 {
            margin: 0 0 7px !important;
            color: #111827 !important;
            font-size: 12pt !important;
            font-weight: 800 !important;
            letter-spacing: .4px !important;
        }

        .print-only-heading p {
            margin: 3px 0 !important;
            font-size: 8.5pt !important;
        }

        .print-date {
            color: #4b5563 !important;
        }

        /* Hilangkan jarak besar dari layout Tailwind */
        .space-y-8 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 10px !important;
        }

        section,
        article {
            break-inside: avoid !important;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            background: #fff !important;
            color: #1f2937 !important;
        }

        section {
            padding: 8px !important;
        }

        article {
            padding: 8px !important;
        }

        section[aria-label="Ringkasan laporan"] {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }

        section[aria-label="Ringkasan laporan"] article {
            border-top: 2px solid #172b4d !important;
        }

        h2,
        h3 {
            color: #172b4d !important;
            font-size: 9pt !important;
            break-after: avoid !important;
        }

        .overflow-hidden,
        .overflow-x-auto {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin-top: 5px !important;
            font-size: 7.5pt !important;
        }

        th {
            background: #172b4d !important;
            color: #fff !important;
            text-align: left !important;
            font-weight: 700 !important;
            padding: 6px 5px !important;
            border: 1px solid #172b4d !important;
            overflow-wrap: anywhere !important;
        }

        td {
            padding: 6px 5px !important;
            color: #1f2937 !important;
            border: 1px solid #cbd5e1 !important;
            white-space: normal !important;
            overflow-wrap: anywhere !important;
            vertical-align: top !important;
        }

        tbody tr:nth-child(even) {
            background: #f1f5f9 !important;
        }

        tr {
            break-inside: avoid !important;
        }

        .print-signature {
            display: block !important;
            width: 210px !important;
            margin: 18px 0 0 auto !important;
            text-align: center !important;
            color: #1f2937 !important;
            font-size: 9pt !important;
            line-height: 1.4 !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .signature-space {
            height: 42px !important;
        }

        .signature-line {
            padding-top: 3px !important;
            font-weight: 700 !important;
            text-decoration: underline !important;
        }

        a {
            color: inherit !important;
            text-decoration: none !important;
        }
    }
</style>
@endpush
