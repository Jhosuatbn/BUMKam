@extends('layouts.sneat-dashboard')

@section('title', 'Laporan')

@section('content')
    @php
        $tabAktif = match (request('tab', 'pemesanan')) {
            'piutang' => 'kredit',
            'denda' => 'penerimaan',
            'pembelian' => 'persediaan',
            default => request('tab', 'pemesanan'),
        };
        $jenisLaporan = [
            'pemesanan' => 'Laporan Pemesanan',
            'kredit' => 'Kredit & Piutang',
            'penerimaan' => 'Penerimaan',
            'persediaan' => 'Persediaan',
            'pengeluaran' => 'Pengeluaran Operasional',
        ];
    @endphp

    <div class="laporan-page">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="icon-base bx bx-error-circle me-1" aria-hidden="true"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card mb-4 laporan-filter-card" aria-labelledby="filter-laporan-heading">
            <div class="card-header border-bottom">
                <h2 id="filter-laporan-heading" class="card-title mb-1">Filter Laporan</h2>
                <p class="mb-0 text-body-secondary">Pilih rentang tanggal untuk mempersempit data laporan.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 {{ $tabAktif === 'kredit' ? 'col-xl-2' : 'col-xl-3' }}">
                        <label for="tab" class="form-label">Jenis Laporan</label>
                        <select id="tab" name="tab" class="form-select">
                            @foreach ($jenisLaporan as $nilaiTab => $labelLaporan)
                                <option value="{{ $nilaiTab }}" @selected($tabAktif === $nilaiTab)>{{ $labelLaporan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($tabAktif === 'persediaan')
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="persediaan_mode" class="form-label">Tampilan Persediaan</label>
                            <select id="persediaan_mode" name="persediaan_mode" class="form-select">
                                <option value="stok" @selected(request('persediaan_mode', request('tab') === 'pembelian' ? 'penerimaan' : 'stok') === 'stok')>Stok & Pergerakan</option>
                                <option value="penerimaan" @selected(request('persediaan_mode', request('tab') === 'pembelian' ? 'penerimaan' : 'stok') === 'penerimaan')>Penerimaan Barang</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-12 col-md-6 {{ $tabAktif === 'kredit' ? 'col-xl-2' : 'col-xl-3' }}">
                        <label for="start_date" class="form-label">Dari tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 {{ $tabAktif === 'kredit' ? 'col-xl-2' : 'col-xl-3' }}">
                        <label for="end_date" class="form-label">Sampai tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>
                    @if ($tabAktif === 'kredit')
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="pelanggan" class="form-label">Pelanggan <span class="text-body-secondary">(opsional)</span></label>
                            <input type="text" id="pelanggan" name="pelanggan" value="{{ request('pelanggan') }}" class="form-control" placeholder="Nama atau ID pelanggan">
                        </div>
                    @endif
                    <div class="col-12 {{ $tabAktif === 'kredit' ? 'col-md-6 col-xl-3' : 'col-xl-3' }} d-flex flex-wrap gap-2">
                        <button type="submit" class="btn bumkam-green-action">
                            <i class="icon-base bx bx-filter-alt me-1" aria-hidden="true"></i>Tampilkan
                        </button>
                        @if (request('start_date') || request('end_date') || request('pelanggan'))
                            <a href="{{ route('laporan.index', ['tab' => $tabAktif]) }}" class="btn btn-outline-secondary">
                                <i class="icon-base bx bx-reset me-1" aria-hidden="true"></i>Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        @if ($tabAktif === 'pemesanan')
            <section class="card laporan-print-area" aria-labelledby="daftar-pemesanan-heading">
                <div class="laporan-official-header"><img src="{{ asset('assets/img/bumkam-mark.png') }}" alt=""><div><strong>BUMKam Tuah Rezeki</strong><span>Badan Usaha Milik Kampung · Rantau Bertuah</span><span>Laporan Pemesanan · Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}</span></div></div>
                <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
                    <div>
                        <h2 id="daftar-pemesanan-heading" class="card-title mb-1">Daftar Pemesanan</h2>
                        <p class="mb-0 text-body-secondary">Transaksi pemesanan barang BUMKam.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary">{{ $pemesananTerbaru->count() }} dari {{ $pemesananTerbaru->total() }} data</span>
                        <button type="button" class="btn bumkam-green-action btn-sm laporan-print-action" data-print-url="{{ route('laporan.index', request()->except(['page', 'pembayaran_page', 'barang_page', 'movement_page']) + ['print' => 1]) }}" onclick="window.open(this.dataset.printUrl, '_blank')"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 border-b p-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([['Total Denda Dikenakan', $totalDendaDikenakan], ['Denda Sudah Dibayar', $totalDendaDibayar], ['Denda Belum Dibayar', $totalDendaBelumDibayar], ['Denda Dikurangi / Dibebaskan', $totalDendaDikurangi]] as [$labelDenda, $nilaiDenda])
                        <article class="rounded-xl border border-slate-200 p-3"><p class="mb-1 text-xs text-body-secondary">{{ $labelDenda }}</p><p class="mb-0 fw-semibold">Rp {{ number_format($nilaiDenda, 0, ',', '.') }}</p></article>
                    @endforeach
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 laporan-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th><th scope="col">Tanggal</th><th scope="col">Pelanggan</th><th scope="col">Barang</th>
                                <th scope="col">Total Harga</th><th scope="col">Skema</th><th scope="col">DP</th><th scope="col">Tenor</th><th scope="col">Angsuran</th><th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pemesananTerbaru as $pemesanan)
                                @php
                                    $status = strtolower(trim($pemesanan->status_pemesanan ?? ''));
                                    $statusClass = in_array($status, ['lunas', 'selesai', 'paid']) ? 'success' : (in_array($status, ['berjalan', 'proses', 'diproses']) ? 'warning' : 'secondary');
                                @endphp
                                <tr>
                                    <td class="text-body-secondary">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $pemesanan->tanggal_pemesanan ? \Illuminate\Support\Carbon::parse($pemesanan->tanggal_pemesanan)->format('d/m/Y') : '—' }}</td>
                                    <td class="fw-semibold">{{ $pemesanan->pelanggan->nama_pelanggan ?? '—' }}</td>
                                    <td>
                                        @forelse ($pemesanan->detailPemesanan as $detail)
                                            {{ $detail->barang->nama_barang ?? '—' }}@if (!$loop->last), @endif
                                        @empty
                                            —
                                        @endforelse
                                    </td>
                                    <td class="text-nowrap fw-semibold">Rp {{ number_format($pemesanan->total_harga ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $pemesanan->skema_pembayaran ?: '—' }}</td>
                                    <td class="text-nowrap">{{ ($pemesanan->dp ?? 0) > 0 ? 'Rp ' . number_format($pemesanan->dp, 0, ',', '.') : '—' }}</td>
                                    <td class="text-nowrap">{{ ($pemesanan->tenor ?? 0) > 0 ? $pemesanan->tenor . ' bln' : '—' }}</td>
                                    <td class="text-nowrap">{{ ($pemesanan->angsuran ?? 0) > 0 ? 'Rp ' . number_format($pemesanan->angsuran, 0, ',', '.') : '—' }}</td>
                                    <td><span class="badge bg-label-{{ $statusClass }}">{{ $status !== '' ? ucfirst($status) : '—' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="py-5 text-center text-body-secondary">Belum ada data pemesanan untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-column flex-sm-row justify-content-between gap-2 text-body-secondary">
                    <span>Menampilkan {{ $pemesananTerbaru->firstItem() ?? 0 }}–{{ $pemesananTerbaru->lastItem() ?? 0 }} dari {{ $pemesananTerbaru->total() }} pemesanan.</span>
                    <span>Total nilai pada halaman ini: <strong class="text-heading">Rp {{ number_format($pemesananTerbaru->sum('total_harga'), 0, ',', '.') }}</strong></span>
                </div>
                @if ($pemesananTerbaru->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4 sm:px-6 laporan-pagination">
                        {{ $pemesananTerbaru->links() }}
                    </div>
                @endif
                <div class="laporan-signature"><p>Rantau Bertuah, {{ now()->locale('id')->translatedFormat('d F Y') }}</p><p>Pimpinan BUMKam Tuah Rezeki</p><div class="signature-space"></div><strong>( ........................................ )</strong></div>
            </section>
        @elseif ($tabAktif === 'kredit')
            @unless ($dendaAktif)<div role="status" class="alert alert-warning">Migration fitur denda belum diterapkan. Ringkasan denda akan aktif setelah migration dijalankan.</div>@endunless
            <section class="card laporan-print-area" aria-labelledby="daftar-kredit-heading">
                <div class="laporan-official-header"><img src="{{ asset('assets/img/bumkam-mark.png') }}" alt=""><div><strong>BUMKam Tuah Rezeki</strong><span>Badan Usaha Milik Kampung · Rantau Bertuah</span><span>Laporan Kredit &amp; Piutang · Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}</span></div></div>
                <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
                    <div>
                        <h2 id="daftar-kredit-heading" class="card-title mb-1">Daftar Kredit &amp; Piutang</h2>
                        <p class="mb-0 text-body-secondary">Ringkasan pembiayaan, sisa piutang, angsuran, dan denda pelanggan.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary">{{ $daftarKredit->total() }} data</span>
                        <button type="button" class="btn bumkam-green-action btn-sm laporan-print-action" data-print-url="{{ route('laporan.index', request()->except(['page', 'pembayaran_page', 'barang_page', 'movement_page']) + ['print' => 1]) }}" onclick="window.open(this.dataset.printUrl, '_blank')"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
                    </div>
                </div>
                <div class="row g-3 border-bottom p-3 p-md-4">
                    @foreach ([['Jumlah Kredit', $jumlahKredit . ' transaksi'], ['Sisa Kewajiban', 'Rp ' . number_format($totalSisaKewajiban, 0, ',', '.')], ['Denda Dikenakan', 'Rp ' . number_format($totalDendaDikenakan, 0, ',', '.')], ['Denda Belum Dibayar', 'Rp ' . number_format($totalDendaBelumDibayar, 0, ',', '.')]] as [$labelRingkasan, $nilaiRingkasan])
                        <div class="col-12 col-sm-6 col-xl-3"><article class="h-100 rounded-xl border border-slate-200 p-3"><p class="mb-1 small text-body-secondary">{{ $labelRingkasan }}</p><strong>{{ $nilaiRingkasan }}</strong></article></div>
                    @endforeach
                </div>
                <div class="table-responsive" style="overflow-x:auto;width:100%">
                    <table class="table table-hover align-middle mb-0 laporan-table laporan-credit-table" style="width:1765px!important;min-width:1765px!important;table-layout:fixed!important">
                        <colgroup>
                            <col style="width:60px"><col style="width:130px"><col style="width:225px"><col style="width:200px"><col style="width:190px">
                            <col style="width:150px"><col style="width:190px"><col style="width:190px"><col style="width:270px"><col style="width:160px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">#</th><th scope="col">Tanggal</th><th scope="col">Pelanggan</th><th scope="col">Barang</th>
                                <th scope="col">Nilai Kredit</th><th scope="col">Angsuran</th><th scope="col">Sudah Dibayar</th><th scope="col">Sisa Kewajiban</th><th scope="col">Denda</th><th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftarKredit as $kredit)
                                @php
                                    $monitoringKredit = $kredit->monitoring_kredit;
                                    $sisaTagihan = $monitoringKredit['sisa_kewajiban'];
                                    $statusKredit = $monitoringKredit['status'];
                                    $statusKreditClass = $statusKredit === 'Lunas' ? 'success' : ($statusKredit === 'Terlambat' ? 'danger' : 'warning');
                                @endphp
                                <tr>
                                    <td class="text-body-secondary">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $kredit->tanggal_pemesanan ? \Illuminate\Support\Carbon::parse($kredit->tanggal_pemesanan)->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        <span class="d-block fw-semibold">{{ $kredit->pelanggan?->nama_pelanggan ?? '—' }}</span>
                                        <small class="d-block text-body-secondary">{{ $kredit->id_pemesanan }}</small>
                                    </td>
                                    <td class="fw-semibold">
                                        @forelse ($kredit->detailPemesanan as $detail)
                                            <span class="d-block">{{ $detail->barang->nama_barang ?? '—' }}</span>
                                        @empty
                                            —
                                        @endforelse
                                    </td>
                                    <td class="text-nowrap">Rp {{ number_format(max(0, (float) ($kredit->total_harga ?? 0) + (float) ($kredit->bunga ?? 0) - (float) ($kredit->dp ?? 0)), 0, ',', '.') }}<small class="d-block text-body-secondary">DP Rp {{ number_format($kredit->dp ?? 0, 0, ',', '.') }} · {{ $kredit->tenor ?? 0 }} bulan</small></td>
                                    <td class="text-nowrap">Rp {{ number_format($kredit->angsuran ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-nowrap">Rp {{ number_format($monitoringKredit['total_terbayar'], 0, ',', '.') }}<small class="d-block text-body-secondary">{{ $kredit->pembayaran->count() }} transaksi</small></td>
                                    <td class="text-nowrap fw-semibold">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}<small class="d-block text-body-secondary">angsuran + denda</small></td>
                                    <td>
                                        <div class="credit-penalty-breakdown">
                                            <div><span>Dikenakan</span><strong>Rp {{ number_format($monitoringKredit['total_denda_dikenakan'], 0, ',', '.') }}</strong></div>
                                            <div><span>Dibayar</span><strong>Rp {{ number_format($monitoringKredit['total_denda_dibayar'], 0, ',', '.') }}</strong></div>
                                            <div><span>Sisa</span><strong>Rp {{ number_format($monitoringKredit['total_denda_belum_dibayar'], 0, ',', '.') }}</strong></div>
                                            @if ($monitoringKredit['total_denda_dikurangi'] > 0)
                                                <div><span>Penyesuaian lama</span><strong>Rp {{ number_format($monitoringKredit['total_denda_dikurangi'], 0, ',', '.') }}</strong></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="badge bg-label-{{ $statusKreditClass }}">{{ $statusKredit }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="py-5 text-center text-body-secondary">Belum ada data kredit untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-column flex-sm-row justify-content-between gap-2 text-body-secondary">
                    <span>Menampilkan {{ $daftarKredit->firstItem() ?? 0 }}–{{ $daftarKredit->lastItem() ?? 0 }} dari {{ $daftarKredit->total() }} data kredit.</span>
                    <span>Total angsuran per bulan: <strong class="text-heading">Rp {{ number_format($totalAngsuranBulanan ?? 0, 0, ',', '.') }}</strong></span>
                </div>
                @if ($daftarKredit->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4 sm:px-6 laporan-pagination">
                        {{ $daftarKredit->links() }}
                    </div>
                @endif
                <div class="laporan-signature"><p>Rantau Bertuah, {{ now()->locale('id')->translatedFormat('d F Y') }}</p><p>Pimpinan BUMKam Tuah Rezeki</p><div class="signature-space"></div><strong>( ........................................ )</strong></div>
            </section>

            <div class="alert alert-primary mt-4 mb-0 d-flex align-items-start gap-2" role="note">
                <i class="icon-base bx bx-info-circle mt-1" aria-hidden="true"></i>
                <span>Data kredit menampilkan transaksi dengan skema angsuran beserta dokumen jaminan yang tercatat.</span>
            </div>
        @endif
    </div>

    <style>
        .laporan-table { min-width: 980px; }
        .laporan-table th { white-space: nowrap; font-size: .75rem; }
        .laporan-credit-table { width: 1765px !important; min-width: 1765px !important; table-layout: fixed !important; }
        .laporan-credit-table th, .laporan-credit-table td { vertical-align: middle; padding: .85rem .75rem; }
        .laporan-credit-table th { white-space: normal; line-height: 1.35; }
        .laporan-credit-table td { overflow-wrap: anywhere; }
        .laporan-credit-table td:nth-child(5), .laporan-credit-table td:nth-child(6), .laporan-credit-table td:nth-child(7), .laporan-credit-table td:nth-child(8) { white-space: nowrap; }
        .credit-penalty-breakdown { display: grid; gap: .35rem; min-width: 0; font-size: .8rem; }
        .credit-penalty-breakdown > div { display: flex; align-items: baseline; justify-content: space-between; gap: .65rem; }
        .credit-penalty-breakdown span { color: var(--bs-secondary-color); }
        .credit-penalty-breakdown strong { white-space: nowrap; font-weight: 600; }
        .laporan-official-header, .laporan-signature { display: none; }
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            body * { visibility: hidden !important; }
            .laporan-print-area, .laporan-print-area * { visibility: visible !important; }
            .laporan-print-area { position: absolute; inset: 0; width: 100%; border: 0 !important; box-shadow: none !important; color: #172b25 !important; }
            .laporan-tabs-card, #filter-laporan-heading, .laporan-print-area .card-footer { break-inside: avoid; }
            .laporan-print-action { display: none !important; }
            .table-responsive { overflow: visible !important; }
            .laporan-table { min-width: 0; width: 100%; font-size: 8px; }
            .laporan-page .laporan-credit-table { min-width: 0; width: 100%; table-layout: fixed; }
            .laporan-page .laporan-credit-table th, .laporan-page .laporan-credit-table td { white-space: normal !important; overflow-wrap: anywhere; word-break: normal; }
            .laporan-page .laporan-credit-table thead { display: table-header-group; }
            .laporan-page .laporan-credit-table tbody > tr { break-inside: avoid; page-break-inside: avoid; }
            .laporan-table th, .laporan-table td { padding: 4px 5px !important; }
            .laporan-official-header { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #177245; padding: 0 0 9px; margin-bottom: 12px; color: #172b25 !important; }
            .laporan-official-header img { width: 52px; height: 52px; object-fit: contain; }
            .laporan-official-header div { display: grid; gap: 2px; }
            .laporan-official-header strong { font-size: 18px; }
            .laporan-official-header span { font-size: 9px; }
            .laporan-signature { display: block; width: 220px; margin: 24px 20px 0 auto; text-align: center; font-size: 10px; break-inside: avoid; color: #172b25 !important; }
            .laporan-signature p { margin: 0 0 3px; }
            .laporan-signature .signature-space { height: 54px; }
        }
    </style>
    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', () => {
                window.addEventListener('afterprint', () => window.close(), { once: true });
                window.print();
            });
        </script>
    @endif
@endsection
