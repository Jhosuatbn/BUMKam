@extends('layouts.sneat-dashboard')

@section('title', 'Laporan')

@section('content')
    @php
        $tabAktif = request('tab', 'pemesanan');
    @endphp

    <div class="laporan-page">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="icon-base bx bx-error-circle me-1" aria-hidden="true"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card mb-4 laporan-tabs-card" aria-label="Jenis laporan">
            <div class="card-body p-2">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item">
                        <a href="{{ route('laporan.index', array_merge(request()->except('tab', 'pelanggan'), ['tab' => 'pemesanan'])) }}"
                           class="nav-link {{ $tabAktif === 'pemesanan' ? 'active bumkam-laporan-tab-active' : '' }}">
                            <i class="icon-base bx bx-receipt me-1" aria-hidden="true"></i>Laporan Pemesanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'kredit'])) }}"
                           class="nav-link {{ $tabAktif === 'kredit' ? 'active bumkam-laporan-tab-active' : '' }}">
                            <i class="icon-base bx bx-wallet me-1" aria-hidden="true"></i>Laporan Kredit
                        </a>
                    </li>
                </ul>
            </div>
        </section>

        <section class="card mb-4 laporan-filter-card" aria-labelledby="filter-laporan-heading">
            <div class="card-header border-bottom">
                <h2 id="filter-laporan-heading" class="card-title mb-1">Filter Laporan</h2>
                <p class="mb-0 text-body-secondary">Pilih rentang tanggal untuk mempersempit data laporan.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.index') }}" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="{{ $tabAktif }}">
                    <div class="col-12 col-md-6 {{ $tabAktif === 'kredit' ? 'col-xl-3' : 'col-xl-4' }}">
                        <label for="start_date" class="form-label">Dari tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 {{ $tabAktif === 'kredit' ? 'col-xl-3' : 'col-xl-4' }}">
                        <label for="end_date" class="form-label">Sampai tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>
                    @if ($tabAktif === 'kredit')
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="pelanggan" class="form-label">Pelanggan <span class="text-body-secondary">(opsional)</span></label>
                            <input type="text" id="pelanggan" name="pelanggan" value="{{ request('pelanggan') }}" class="form-control" placeholder="Nama atau ID pelanggan">
                        </div>
                    @endif
                    <div class="col-12 {{ $tabAktif === 'kredit' ? 'col-md-6 col-xl-3' : 'col-xl-4' }} d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
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
                        <span class="badge bg-label-primary">{{ $pemesananTerbaru->count() }} data ditampilkan</span>
                        <button type="button" class="btn btn-outline-primary btn-sm laporan-print-action" onclick="window.print()"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
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
                    <span>Menampilkan {{ $pemesananTerbaru->count() }} data pemesanan terbaru.</span>
                    <span>Total nilai pada daftar: <strong class="text-heading">Rp {{ number_format($pemesananTerbaru->sum('total_harga'), 0, ',', '.') }}</strong></span>
                </div>
                <div class="laporan-signature"><p>Rantau Bertuah, {{ now()->locale('id')->translatedFormat('d F Y') }}</p><p>Pimpinan BUMKam Tuah Rezeki</p><div class="signature-space"></div><strong>( ........................................ )</strong></div>
            </section>
        @elseif ($tabAktif === 'kredit')
            @unless ($dendaAktif)<div role="status" class="alert alert-warning">Migration fitur denda belum diterapkan. Ringkasan denda akan aktif setelah migration dijalankan.</div>@endunless
            <section class="card laporan-print-area" aria-labelledby="daftar-kredit-heading">
                <div class="laporan-official-header"><img src="{{ asset('assets/img/bumkam-mark.png') }}" alt=""><div><strong>BUMKam Tuah Rezeki</strong><span>Badan Usaha Milik Kampung · Rantau Bertuah</span><span>Laporan Kredit · Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}</span></div></div>
                <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
                    <div>
                        <h2 id="daftar-kredit-heading" class="card-title mb-1">Daftar Kredit</h2>
                        <p class="mb-0 text-body-secondary">Ringkasan kredit pelanggan. Buka rincian untuk melihat DP, sisa tagihan, denda, dan jaminan.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary">{{ $daftarKredit->count() }} data</span>
                        <button type="button" class="btn btn-outline-primary btn-sm laporan-print-action" onclick="window.print()"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 laporan-table laporan-credit-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th><th scope="col">Tanggal</th><th scope="col">Pelanggan</th><th scope="col">Barang</th>
                                <th scope="col">Total Kredit</th><th scope="col">Angsuran / Bulan</th><th scope="col">Status</th><th scope="col">Rincian</th>
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
                                @php($rincianId = 'rincian-kredit-' . $kredit->id_pemesanan)
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
                                    <td class="text-nowrap">Rp {{ number_format(max(0, (float) ($kredit->total_harga ?? 0) + (float) ($kredit->bunga ?? 0) - (float) ($kredit->dp ?? 0)), 0, ',', '.') }}</td>
                                    <td class="text-nowrap">Rp {{ number_format($kredit->angsuran ?? 0, 0, ',', '.') }}</td>
                                    <td><span class="badge bg-label-{{ $statusKreditClass }}">{{ $statusKredit }}</span></td>
                                    <td>
                                        <button type="button" class="credit-details-toggle btn btn-sm bumkam-green-action" data-credit-details-toggle aria-expanded="false" aria-controls="{{ $rincianId }}">
                                            <span data-credit-details-label>Rincian</span><i class="icon-base bx bx-chevron-down ms-1" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr id="{{ $rincianId }}" class="credit-details-row hidden">
                                    <td colspan="8">
                                        <div class="credit-details-grid grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="d-block small text-body-secondary">Skema Pembayaran</span><strong>{{ $kredit->skema_pembayaran ?: '—' }}</strong></article>
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="d-block small text-body-secondary">DP</span><strong>Rp {{ number_format($kredit->dp ?? 0, 0, ',', '.') }}</strong></article>
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="d-block small text-body-secondary">Tenor</span><strong>{{ $kredit->tenor ?? '—' }} bulan</strong></article>
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="d-block small text-body-secondary">Angsuran Tercatat</span><strong>{{ $kredit->pembayaran->count() }} / {{ $kredit->tenor ?? 0 }}</strong></article>
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="d-block small text-body-secondary">Sisa Angsuran + Denda</span><strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong></article>
                                            <article class="credit-fine-breakdown-card rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                <span class="d-block small text-body-secondary">Rincian Denda</span>
                                                <div class="credit-fine-breakdown mt-2">
                                                    <span>Dikenakan <strong>Rp {{ number_format($monitoringKredit['total_denda_dikenakan'], 0, ',', '.') }}</strong></span>
                                                    <span>Dibayar <strong>Rp {{ number_format($monitoringKredit['total_denda_dibayar'], 0, ',', '.') }}</strong></span>
                                                    <span>Belum dibayar <strong>Rp {{ number_format($monitoringKredit['total_denda_belum_dibayar'], 0, ',', '.') }}</strong></span>
                                                    <span>Dikurangi <strong>Rp {{ number_format($monitoringKredit['total_denda_dikurangi'], 0, ',', '.') }}</strong></span>
                                                </div>
                                            </article>
                                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                <span class="d-block small text-body-secondary">Jaminan</span>
                                                <strong>
                                                    @if ($kredit->jaminanDokumen)
                                                        {{ $kredit->jaminanDokumen->jenis_dokumen === 'Surat Tanah' ? 'Sertifikat Tanah' : $kredit->jaminanDokumen->jenis_dokumen }}
                                                        <small class="d-block text-body-secondary">{{ $kredit->jaminanDokumen->nomor_dokumen }}</small>
                                                    @else
                                                        {{ $kredit->jaminan ?: '—' }}
                                                    @endif
                                                </strong>
                                            </article>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-5 text-center text-body-secondary">Belum ada data kredit untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-column flex-sm-row justify-content-between gap-2 text-body-secondary">
                    <span>Menampilkan {{ $daftarKredit->count() }} data kredit.</span>
                    <span>Total angsuran per bulan: <strong class="text-heading">Rp {{ number_format($totalAngsuranBulanan ?? 0, 0, ',', '.') }}</strong></span>
                </div>
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
        .laporan-credit-table { min-width: 1050px; }
        .credit-details-row > td { background: #f8fafc; }
        .credit-details-grid article { min-width: 0; }
        .credit-fine-breakdown { display: grid; gap: .2rem; font-size: .72rem; line-height: 1.35; }
        .credit-fine-breakdown span { display: flex; justify-content: space-between; gap: .75rem; white-space: nowrap; }
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
            .laporan-page .laporan-credit-table { min-width: 0; table-layout: auto; }
            .credit-details-toggle { display: none !important; }
            .laporan-page .laporan-credit-table .credit-details-row { display: table-row !important; }
            .laporan-page .laporan-credit-table .credit-details-row > td { padding: 6px !important; }
            .credit-details-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
            .credit-details-grid article { break-inside: avoid; }
            .credit-fine-breakdown { min-width: 0; font-size: 6.5px; }
            .credit-fine-breakdown span { white-space: normal; }
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
    <script>
        document.querySelectorAll('[data-credit-details-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const row = document.getElementById(button.getAttribute('aria-controls'));
                if (!row) return;

                const expanded = button.getAttribute('aria-expanded') !== 'true';
                row.classList.toggle('hidden', !expanded);
                button.setAttribute('aria-expanded', String(expanded));
                button.querySelector('[data-credit-details-label]').textContent = expanded ? 'Tutup' : 'Rincian';
                const icon = button.querySelector('.bx');
                icon?.classList.toggle('bx-chevron-down', !expanded);
                icon?.classList.toggle('bx-chevron-up', expanded);
            });
        });
    </script>
@endsection
