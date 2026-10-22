
@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
@php
    $tabAktif = request('tab', 'pemesanan');
@endphp

<div class="laporan-page">

    {{-- HEADER --}}
    <div class="page-heading">
        <h1>Laporan</h1>
        <p>Pantau pemesanan dan pembiayaan barang BUMKam.</p>
    </div>

    {{-- TAB --}}
    <div class="laporan-tabs">
        <a href="{{ route('laporan.index', array_merge(request()->except('tab', 'pelanggan'), ['tab' => 'pemesanan'])) }}"
           class="laporan-tab {{ $tabAktif === 'pemesanan' ? 'active' : '' }}">
            Laporan Pemesanan
        </a>

        <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'kredit'])) }}"
           class="laporan-tab {{ $tabAktif === 'kredit' ? 'active' : '' }}">
            Laporan Kredit
        </a>
    </div>

    {{-- FILTER TANGGAL DAN PELANGGAN --}}
    <form action="{{ route('laporan.index') }}" method="GET" class="laporan-filter">
        <input type="hidden" name="tab" value="{{ $tabAktif }}">

        <div class="filter-field">
            <label for="start_date">Dari tanggal{{ $tabAktif === 'kredit' ? ' (opsional)' : '' }}</label>
            <input type="date"
                   id="start_date"
                   name="start_date"
                   value="{{ request('start_date') }}">
        </div>

        <div class="filter-field">
            <label for="end_date">Sampai tanggal{{ $tabAktif === 'kredit' ? ' (opsional)' : '' }}</label>
            <input type="date"
                   id="end_date"
                   name="end_date"
                   value="{{ request('end_date') }}">
        </div>

        @if ($tabAktif === 'kredit')
            <div class="filter-field">
                <label for="pelanggan">Pelanggan (opsional)</label>
                <input type="text"
                       id="pelanggan"
                       name="pelanggan"
                       value="{{ request('pelanggan') }}"
                       placeholder="Cari pelanggan...">
            </div>
        @endif

        <div class="filter-actions">
            <button type="submit">
                <span class="filter-icon">☷</span>
                Tampilkan
            </button>
        </div>

        @if (request('start_date') || request('end_date') || request('pelanggan'))
            <a href="{{ route('laporan.index', ['tab' => $tabAktif]) }}"
               class="reset-button">
                Reset filter
            </a>
        @endif
    </form>

    {{-- LAPORAN PEMESANAN --}}
    @if ($tabAktif === 'pemesanan')

        <section class="transaksi-card">
            <div class="transaksi-card-header">
                <div>
                    <h2>Daftar pemesanan</h2>
                    <p>Transaksi pemesanan barang BUMKam</p>
                </div>
            </div>

            <div class="transaksi-table-wrapper">
                <table class="transaksi-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tgl</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Total Harga</th>
                            <th>Skema</th>
                            <th>DP</th>
                            <th>Tenor</th>
                            <th>Angsuran</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pemesananTerbaru as $pemesanan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $pemesanan->tanggal_pemesanan
                                        ? \Illuminate\Support\Carbon::parse($pemesanan->tanggal_pemesanan)->format('d/m/Y')
                                        : '-' }}
                                </td>

                                <td class="customer-name">
                                    {{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}
                                </td>

                                <td>
                                    @forelse ($pemesanan->detailPemesanan as $detail)
                                        {{ $detail->barang->nama_barang ?? '-' }}@if (!$loop->last), @endif
                                    @empty
                                        -
                                    @endforelse
                                </td>

                                <td class="money-cell">
                                    Rp {{ number_format($pemesanan->total_harga ?? 0, 0, ',', '.') }}
                                </td>

                                <td>{{ $pemesanan->skema_pembayaran ?: '-' }}</td>

                                <td>
                                    {{ ($pemesanan->dp ?? 0) > 0
                                        ? 'Rp ' . number_format($pemesanan->dp, 0, ',', '.')
                                        : '-' }}
                                </td>

                                <td>
                                    {{ ($pemesanan->tenor ?? 0) > 0
                                        ? $pemesanan->tenor . ' bln'
                                        : '-' }}
                                </td>

                                <td>
                                    {{ ($pemesanan->angsuran ?? 0) > 0
                                        ? 'Rp ' . number_format($pemesanan->angsuran, 0, ',', '.')
                                        : '-' }}
                                </td>

                                <td>
                                    @php
                                        $status = strtolower(trim($pemesanan->status ?? ''));
                                        $statusLunas = in_array($status, ['lunas', 'selesai', 'paid']);
                                        $statusBerjalan = in_array($status, ['berjalan', 'proses', 'diproses']);
                                    @endphp

                                    @if ($statusLunas)
                                        <span class="status-badge status-lunas">
                                            <span class="status-dot"></span>Lunas
                                        </span>
                                    @elseif ($statusBerjalan)
                                        <span class="status-badge status-berjalan">
                                            <span class="status-dot"></span>Berjalan
                                        </span>
                                    @elseif ($status !== '')
                                        <span class="status-badge status-lain">
                                            {{ ucfirst($status) }}
                                        </span>
                                    @else
                                        <span class="status-badge status-lain">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="transaksi-empty">
                                    Belum ada data pemesanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="transaksi-card-footer">
                <span>
                    Menampilkan {{ $pemesananTerbaru->count() }} data pemesanan
                </span>

                <span>
                    Total transaksi
                    <strong>
                        Rp {{ number_format($pemesananTerbaru->sum('total_harga'), 0, ',', '.') }}
                    </strong>
                </span>
            </div>
        </section>

    {{-- LAPORAN KREDIT --}}
    @elseif ($tabAktif === 'kredit')

        <section class="transaksi-card kredit-card">
            <div class="transaksi-card-header">
                <div>
                    <h2>Daftar kredit</h2>
                    <p>Rincian pembiayaan dan jaminan pelanggan</p>
                </div>

                <button type="button" class="btn-ekspor" onclick="window.print()">
                    <span>⇩</span> Ekspor
                </button>
            </div>

            <div class="transaksi-table-wrapper">
                <table class="transaksi-table kredit-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th>Total Harga</th>
                            <th>DP</th>
                            <th>Nominal angsuran</th>
                            <th>Tenor</th>
                            <th>Jaminan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($daftarKredit as $kredit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td class="customer-name">
                                    @forelse ($kredit->detailPemesanan as $detail)
                                        {{ $detail->barang->nama_barang ?? '-' }}@if (!$loop->last), @endif
                                    @empty
                                        -
                                    @endforelse
                                </td>

                                <td class="money-cell">
                                    Rp {{ number_format($kredit->total_harga ?? 0, 0, ',', '.') }}
                                </td>

                                <td>
                                    Rp {{ number_format($kredit->dp ?? 0, 0, ',', '.') }}
                                </td>

                                <td>
                                    Rp {{ number_format($kredit->angsuran ?? 0, 0, ',', '.') }}
                                </td>

                                <td>{{ $kredit->tenor ?? '-' }} bln</td>

                                <td>{{ $kredit->jaminan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="transaksi-empty">
                                    Belum ada data kredit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="transaksi-card-footer">
                <span>
                    Menampilkan {{ $daftarKredit->count() }} data kredit
                </span>

                <span>
                    Total angsuran per bulan
                    <strong>
                        Rp {{ number_format($totalAngsuranBulanan ?? 0, 0, ',', '.') }}
                    </strong>
                </span>
            </div>
        </section>

        {{-- INFORMASI KREDIT --}}
        <div class="kredit-info">
            <span class="info-icon">i</span>
            <p>
                Data kredit menampilkan transaksi yang menggunakan skema
                angsuran beserta dokumen jaminan yang tercatat.
            </p>
        </div>

    @endif

</div>

<style>
    .laporan-page {
        width: 100%;
        min-height: 100%;
        padding: 20px;
        box-sizing: border-box;
        color: #334155;
        font-family: Arial, sans-serif;
        background: #f3f6fa;
    }

    /* HEADER */
    .page-heading {
        margin-bottom: 20px;
    }

    .page-heading h1 {
        margin: 0 0 5px;
        color: #1e293b;
        font-size: 24px;
        font-weight: 700;
    }

    .page-heading p {
        margin: 0;
        color: #718096;
        font-size: 13px;
    }

    /* TAB */
    .laporan-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #dce4ee;
        margin-bottom: 16px;
    }

    .laporan-tab {
        display: inline-block;
        padding: 13px 14px 11px;
        color: #64748b;
        text-align: center;
        text-decoration: none;
        font-size: 13px;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        white-space: nowrap;
    }

    .laporan-tab:hover {
        color: #2563a6;
    }

    .laporan-tab.active {
        color: #2163ad;
        font-weight: 700;
        border-bottom-color: #2163ad;
    }

    /* FILTER */
    .laporan-filter {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(140px, 1fr) minmax(160px, 1.2fr) auto;
        gap: 10px;
        align-items: end;
        padding: 12px;
        margin: 0 0 16px;
        background: #fff;
        border: 1px solid #e1e8f0;
        border-radius: 8px;
        box-sizing: border-box;
    }

    .filter-field {
        min-width: 0;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 11px;
    }

    .filter-field input {
        display: block;
        width: 100%;
        min-width: 0;
        height: 35px;
        padding: 6px 8px;
        border: 1px solid #d8e1ed;
        border-radius: 5px;
        background: #fff;
        color: #334155;
        font-family: Arial, sans-serif;
        font-size: 12px;
        box-sizing: border-box;
    }

    .filter-field input::placeholder {
        color: #94a3b8;
    }

    .filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-actions button {
        height: 35px;
        padding: 0 14px;
        border: none;
        border-radius: 5px;
        background: #2864a8;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .filter-actions button:hover {
        background: #1e4f87;
    }

    .filter-icon {
        margin-right: 4px;
    }

    .reset-button {
        grid-column: 1 / -1;
        color: #64748b;
        font-size: 11px;
        text-decoration: none;
    }

    .reset-button:hover {
        text-decoration: underline;
    }

    /* KARTU TABEL */
    .transaksi-card {
        width: 100%;
        margin: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        box-sizing: border-box;
    }

    .transaksi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px 10px;
        border-bottom: 1px solid #e8edf4;
    }

    .transaksi-card-header h2 {
        margin: 0;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .transaksi-card-header p {
        margin: 4px 0 0;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 400;
    }

    /* TABEL PEMESANAN DAN KREDIT */
    .transaksi-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .transaksi-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        background: #fff;
        color: #334155;
        text-align: left;
        font-size: 11px;
    }

    .transaksi-table thead {
        background: #f8fafc;
    }

    .transaksi-table th {
        padding: 11px 10px;
        border-bottom: 1px solid #e3eaf2;
        color: #64748b;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .transaksi-table td {
        padding: 13px 10px;
        border-bottom: 1px solid #edf1f5;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .transaksi-table tbody tr:last-child td {
        border-bottom: none;
    }

    .transaksi-table tbody tr:hover {
        background: #fafcff;
    }

    .transaksi-table .customer-name {
        color: #334155;
        font-weight: 600;
    }

    .transaksi-table .money-cell {
        color: #334155;
        font-weight: 600;
        white-space: nowrap;
    }

    .transaksi-table .transaksi-empty {
        height: 80px;
        padding: 20px;
        color: #94a3b8;
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
    }

    /* TOMBOL EKSPOR */
    .btn-ekspor {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border: 1px solid #d7e1ed;
        border-radius: 5px;
        background: #fff;
        color: #64748b;
        font-size: 10px;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-ekspor:hover {
        background: #f8fafc;
    }

    /* STATUS PEMESANAN */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-dot {
        width: 5px;
        height: 5px;
        display: inline-block;
        border-radius: 50%;
        background: currentColor;
    }

    .status-lunas {
        color: #16803d;
        background: #dcfce7;
    }

    .status-berjalan {
        color: #a16207;
        background: #fef3c7;
    }

    .status-lain {
        color: #64748b;
        background: #f1f5f9;
    }

    /* FOOTER */
    .transaksi-card-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 16px;
        border-top: 1px solid #e3eaf2;
        background: #f8fafc;
        color: #718096;
        font-size: 10px;
    }

    .transaksi-card-footer strong {
        color: #334155;
        font-weight: 700;
    }

    /* INFORMASI KREDIT */
    .kredit-info {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 14px;
        padding: 13px 14px;
        border: 1px solid #d2e3f8;
        border-radius: 6px;
        background: #eaf3fd;
        color: #718096;
        box-sizing: border-box;
    }

    .kredit-info p {
        margin: 0;
        font-size: 10px;
        line-height: 1.6;
    }

    .info-icon {
        display: inline-flex;
        flex: 0 0 17px;
        align-items: center;
        justify-content: center;
        width: 17px;
        height: 17px;
        border: 1px solid #bfdbfe;
        border-radius: 50%;
        background: #fff;
        color: #2563a6;
        font-size: 11px;
        font-weight: 700;
    }

    /* CETAK / EKSPOR */
    @media print {
        body * {
            visibility: hidden;
        }

        .transaksi-card,
        .transaksi-card * {
            visibility: visible;
        }

        .transaksi-card {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }

        .btn-ekspor,
        .laporan-tabs,
        .laporan-filter {
            display: none !important;
        }
    }

    /* RESPONSIVE */
    @media (max-width: 1000px) {
        .laporan-filter {
            grid-template-columns: 1fr 1fr;
        }

        .filter-period {
            align-items: flex-start;
        }
    }

    @media (max-width: 600px) {
        .laporan-page {
            padding: 12px;
        }

        .page-heading h1 {
            font-size: 21px;
        }

        .laporan-tabs {
            gap: 0;
        }

        .laporan-tab {
            padding: 12px 8px;
            font-size: 12px;
        }

        .laporan-filter {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .filter-actions {
            flex-wrap: wrap;
        }

        .transaksi-table {
            min-width: 850px;
        }

        .transaksi-card-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .transaksi-card-header {
            padding: 12px;
        }
    }
</style>
@endsection
