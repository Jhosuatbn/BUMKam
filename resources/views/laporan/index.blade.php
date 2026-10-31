@extends('layouts.sneat-dashboard')

@section('title', 'Laporan')

@section('content')
    @php
        $tabAktif = request('tab', 'pemesanan');
    @endphp

    <div>
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
                           class="nav-link {{ $tabAktif === 'pemesanan' ? 'active' : '' }}">
                            <i class="icon-base bx bx-receipt me-1" aria-hidden="true"></i>Laporan Pemesanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'kredit'])) }}"
                           class="nav-link {{ $tabAktif === 'kredit' ? 'active' : '' }}">
                            <i class="icon-base bx bx-wallet me-1" aria-hidden="true"></i>Laporan Kredit
                        </a>
                    </li>
                </ul>
            </div>
        </section>

        <section class="card mb-4" aria-labelledby="filter-laporan-heading">
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
            </section>
        @elseif ($tabAktif === 'kredit')
            <section class="card laporan-print-area" aria-labelledby="daftar-kredit-heading">
                <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
                    <div>
                        <h2 id="daftar-kredit-heading" class="card-title mb-1">Daftar Kredit</h2>
                        <p class="mb-0 text-body-secondary">Rincian pembiayaan, angsuran, sisa tagihan, dan jaminan pelanggan.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary">{{ $daftarKredit->count() }} data</span>
                        <button type="button" class="btn btn-outline-primary btn-sm laporan-print-action" onclick="window.print()"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 laporan-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th><th scope="col">Barang</th><th scope="col">Total Harga</th><th scope="col">DP</th><th scope="col">Nominal Angsuran</th>
                                <th scope="col">Tenor</th><th scope="col">Angsuran Tercatat</th><th scope="col">Sisa Tagihan</th><th scope="col">Status</th><th scope="col">Jaminan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftarKredit as $kredit)
                                @php
                                    $totalDibayar = (float) $kredit->pembayaran->sum('nominal');
                                    $sisaTagihan = max(0, (float) $kredit->total_harga + (float) $kredit->bunga - (float) $kredit->dp - $totalDibayar);
                                    $jumlahJatuhTempo = 0;
                                    $tanggalMulaiAngsuran = \Illuminate\Support\Carbon::parse($kredit->tanggal_pemesanan);
                                    for ($bulanJatuhTempo = 1; $bulanJatuhTempo <= (int) $kredit->tenor; $bulanJatuhTempo++) {
                                        if ($tanggalMulaiAngsuran->copy()->addMonthsNoOverflow($bulanJatuhTempo)->startOfDay()->lte(now()->startOfDay())) $jumlahJatuhTempo++;
                                    }
                                    $statusKredit = $sisaTagihan <= 0 ? 'Lunas' : ($jumlahJatuhTempo > $kredit->pembayaran->count() ? 'Menunggak' : 'Berjalan');
                                    $statusKreditClass = $statusKredit === 'Lunas' ? 'success' : ($statusKredit === 'Menunggak' ? 'danger' : 'warning');
                                @endphp
                                <tr>
                                    <td class="text-body-secondary">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        @forelse ($kredit->detailPemesanan as $detail)
                                            {{ $detail->barang->nama_barang ?? '—' }}@if (!$loop->last), @endif
                                        @empty
                                            —
                                        @endforelse
                                    </td>
                                    <td class="text-nowrap">Rp {{ number_format($kredit->total_harga ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-nowrap">Rp {{ number_format($kredit->dp ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-nowrap">Rp {{ number_format($kredit->angsuran ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-nowrap">{{ $kredit->tenor ?? '—' }} bln</td>
                                    <td class="text-nowrap">{{ $kredit->pembayaran->count() }} / {{ $kredit->tenor ?? 0 }}</td>
                                    <td class="text-nowrap fw-semibold">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</td>
                                    <td><span class="badge bg-label-{{ $statusKreditClass }}">{{ $statusKredit }}</span></td>
                                    <td>{{ $kredit->jaminan ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="py-5 text-center text-body-secondary">Belum ada data kredit untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-column flex-sm-row justify-content-between gap-2 text-body-secondary">
                    <span>Menampilkan {{ $daftarKredit->count() }} data kredit.</span>
                    <span>Total angsuran per bulan: <strong class="text-heading">Rp {{ number_format($totalAngsuranBulanan ?? 0, 0, ',', '.') }}</strong></span>
                </div>
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
        @media print {
            body * { visibility: hidden !important; }
            .laporan-print-area, .laporan-print-area * { visibility: visible !important; }
            .laporan-print-area { position: absolute; inset: 0; width: 100%; border: 0 !important; box-shadow: none !important; }
            .laporan-tabs-card, #filter-laporan-heading, .laporan-print-area .card-footer { break-inside: avoid; }
            .laporan-print-action { display: none !important; }
            .table-responsive { overflow: visible !important; }
            .laporan-table { min-width: 0; font-size: 9px; }
        }
    </style>
@endsection
