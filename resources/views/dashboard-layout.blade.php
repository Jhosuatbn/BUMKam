@php
    $persentasePembayaranLunas = $totalStatusPembayaran > 0
        ? (int) round(($pembayaranLunas / $totalStatusPembayaran) * 100)
        : 0;
    $persentasePerubahanNilai = $nilaiPesananBulanLalu > 0
        ? (($nilaiPesananBulanIni - $nilaiPesananBulanLalu) / $nilaiPesananBulanLalu) * 100
        : ($nilaiPesananBulanIni > 0 ? 100 : 0);
    $lebarProdukMaks = max(1, (int) $maxBarangDipesan);
    $dashboardChartData = [
        'monthLabels' => $pemesananTrend->pluck('label')->values(),
        'monthlyOrders' => $pemesananTrend->pluck('jumlah')->values(),
        'previousYearOrders' => $monthlyOrderComparison->values(),
        'monthlyRevenue' => $monthlyRevenueTrend->values(),
        'paymentPaidPercent' => $persentasePembayaranLunas,
        'orderStatus' => [(int) $pemesananLunas, (int) $pemesananBelumLunas],
        'orderValues' => [(float) $nilaiPesananBulanLalu, (float) $nilaiPesananBulanIni],
        'orderValueLabels' => ['Bulan lalu', 'Bulan ini'],
    ];
@endphp

@push('dashboard-data')
    <script id="bumkam-dashboard-chart-data" type="application/json">{!! json_encode($dashboardChartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

<div class="row">
    {{-- Baris 1: sambutan dan dua kartu sorotan, mengikuti grid Sneat 8/2/2. --}}
    <div class="col-12 col-xxl-8 mb-6">
        <div class="card h-100"><div class="d-flex align-items-start row">
            <div class="col-sm-7"><div class="card-body">
                <span class="badge bg-label-primary mb-3">BUMKam Tuah Rezeki</span>
                <h5 class="card-title text-primary mb-3">{{ $isPimpinan ? 'Selamat datang, Pimpinan!' : 'Selamat datang, ' . (auth()->user()->nama_lengkap ?? 'Admin') . '!' }}</h5>
                <p class="mb-5">{{ $isPimpinan ? 'Pantau perkembangan usaha dan kondisi operasional BUMKam dalam satu tampilan.' : 'Pantau aktivitas pemesanan, pelanggan, dan layanan usaha dalam satu tampilan.' }}</p>
                <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-primary"><i class="icon-base bx bx-file me-1"></i>Lihat Laporan</a>
            </div></div>
            <div class="col-sm-5 text-center text-sm-start"><div class="card-body pb-0 px-0 px-md-6"><img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="175" alt="Ilustrasi dashboard BUMKam"></div></div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xxl-2 mb-6"><div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
        <div><p class="mb-1">Pemesanan Bulan Ini</p><h4 class="card-title mb-1">{{ number_format($transaksiBulanIni, 0, ',', '.') }}</h4><small class="text-body-secondary">Dibanding {{ number_format($transaksiBulanLalu, 0, ',', '.') }} bulan lalu</small></div>
        <div id="bumkam-order-sparkline" class="mt-3" role="img" aria-label="Tren pemesanan enam bulan terakhir"></div>
    </div></div></div>

    <div class="col-12 col-md-6 col-xxl-2 mb-6"><div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
        <div><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-wallet icon-lg"></i></span></div><p class="mb-1">Nilai Bulan Ini</p><h5 class="card-title mb-1">Rp {{ number_format($nilaiPesananBulanIni, 0, ',', '.') }}</h5><small class="{{ $persentasePerubahan >= 0 ? 'text-success' : 'text-danger' }}">{{ $persentasePerubahan > 0 ? '+' : '' }}{{ number_format($persentasePerubahan, 1, ',', '.') }}% transaksi</small></div>
        <div id="bumkam-sales-sparkline" class="mt-3" role="img" aria-label="Tren nilai pemesanan enam bulan terakhir"></div>
    </div></div></div>

    {{-- Baris 2: grafik utama dengan radial di dalam card, ringkasan dan laporan di kanan. --}}
    <div class="col-12 col-xxl-8 mb-6 total-revenue"><div class="card h-100"><div class="row row-bordered g-0 h-100">
        <div class="col-lg-8"><div class="card-header d-flex align-items-center justify-content-between"><div class="card-title mb-0"><h5 class="m-0 me-2">Perbandingan Pemesanan</h5><small class="text-body-secondary">Enam bulan terakhir dibanding tahun sebelumnya</small></div><span class="badge bg-label-primary">{{ $isPimpinan ? 'Analitik pimpinan' : 'Aktivitas usaha' }}</span></div><div id="bumkam-monthly-orders-chart" class="px-3 pb-3" role="img" aria-label="Grafik perbandingan pemesanan enam bulan terakhir dengan tahun lalu"></div></div>
        <div class="col-lg-4"><div class="card-body px-xl-6 py-5 d-flex align-items-center flex-column h-100"><div class="text-center mb-2"><span class="badge bg-label-primary">Pembayaran Lunas</span></div><div id="bumkam-payment-ratio-chart" role="img" aria-label="Persentase pembayaran lunas"></div><div class="text-center fw-medium my-2">{{ $persentasePembayaranLunas }}% pembayaran lunas</div><small class="text-body-secondary text-center">{{ $pembayaranLunas }} lunas dari {{ $totalStatusPembayaran }} pembayaran</small><div class="d-flex justify-content-between w-100 gap-2 border-top mt-auto pt-3"><div><small>Bulan ini</small><h6 class="mb-0">{{ number_format($transaksiBulanIni, 0, ',', '.') }}</h6></div><div class="text-end"><small>Bulan lalu</small><h6 class="mb-0">{{ number_format($transaksiBulanLalu, 0, ',', '.') }}</h6></div></div></div></div>
    </div></div></div>

    <div class="col-12 col-xxl-4 mb-6 profile-report"><div class="row">
        @if ($isPimpinan)
            <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-group icon-lg"></i></span></div><p class="mb-1">Pelanggan Kredit</p><h4 class="card-title mb-0">{{ number_format($totalKredit, 0, ',', '.') }}</h4></div></div></div>
            <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-error icon-lg"></i></span></div><p class="mb-1">Stok Habis</p><h4 class="card-title mb-0">{{ number_format($barangStokHabis->count(), 0, ',', '.') }}</h4></div></div></div>
        @else
            <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-group icon-lg"></i></span></div><p class="mb-1">Pelanggan</p><h4 class="card-title mb-0">{{ number_format($totalPelanggan, 0, ',', '.') }}</h4></div></div></div>
            <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-package icon-lg"></i></span></div><p class="mb-1">Barang</p><h4 class="card-title mb-0">{{ number_format($totalBarang, 0, ',', '.') }}</h4></div></div></div>
        @endif
        <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-check-circle icon-lg"></i></span></div><p class="mb-1">Pembayaran Lunas</p><h4 class="card-title mb-0">{{ number_format($pembayaranLunas, 0, ',', '.') }}</h4></div></div></div>
        <div class="col-6 mb-6"><div class="card h-100"><div class="card-body"><div class="avatar mb-3"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-time-five icon-lg"></i></span></div><p class="mb-1">Belum Lunas</p><h4 class="card-title mb-0">{{ number_format($pembayaranBelumLunas, 0, ',', '.') }}</h4></div></div></div>
        <div class="col-12 mb-6"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="mb-1">Ringkasan Periode</h5><small class="text-body-secondary">Perubahan pemesanan bulanan</small></div><span class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-trending-up icon-lg"></i></span></span></div><div class="d-flex align-items-end gap-2"><h3 class="mb-0 {{ $persentasePerubahan >= 0 ? 'text-success' : 'text-danger' }}">{{ $persentasePerubahan > 0 ? '+' : '' }}{{ number_format($persentasePerubahan, 1, ',', '.') }}%</h3><span class="text-body-secondary mb-1">dari bulan lalu</span></div><div class="progress mt-4" style="height: 8px"><div class="progress-bar" role="progressbar" style="width: {{ min(100, max(4, abs($persentasePerubahan))) }}%" aria-valuenow="{{ min(100, abs($persentasePerubahan)) }}" aria-valuemin="0" aria-valuemax="100"></div></div><small class="d-block mt-2 text-body-secondary">{{ number_format($nilaiPesananBulanIni, 0, ',', '.') }} nilai pemesanan bulan ini</small></div></div></div>
    </div></div>

    {{-- Baris 3: tiga kartu seperti contoh Sneat. --}}
    <div class="col-12 col-lg-4 mb-6"><div class="card h-100"><div class="card-header d-flex justify-content-between"><div><h5 class="card-title mb-1">Statistik Pemesanan</h5><p class="card-subtitle">{{ number_format($totalPemesanan, 0, ',', '.') }} total pemesanan</p></div><span class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-receipt"></i></span></span></div><div class="card-body"><div class="bumkam-order-status-summary d-flex align-items-center justify-content-between gap-3 mb-5"><div class="min-w-0"><h3 class="mb-1">{{ number_format($totalPemesanan, 0, ',', '.') }}</h3><small class="text-body-secondary">Total pemesanan</small></div><div class="bumkam-order-status-plot"><div id="bumkam-order-status-chart" role="img" aria-label="Grafik status pemesanan"></div></div></div><div class="d-flex justify-content-between border-top pt-3 mb-4"><span><i class="icon-base bx bxs-circle text-success me-2"></i>Lunas</span><strong>{{ number_format($pemesananLunas, 0, ',', '.') }}</strong></div><div class="d-flex justify-content-between mb-5"><span><i class="icon-base bx bxs-circle text-warning me-2"></i>Belum lunas</span><strong>{{ number_format($pemesananBelumLunas, 0, ',', '.') }}</strong></div><h6 class="mb-3">Barang paling diminati</h6>
        @forelse ($barangTerlaris->take(3) as $barangPopuler)
            @php($lebarProduk = max(3, (int) round(($barangPopuler['jumlah'] / $lebarProdukMaks) * 100)))
            <div class="d-flex align-items-center mb-4"><div class="avatar flex-shrink-0 me-3"><span @class(['avatar-initial rounded', 'bg-label-primary' => $loop->iteration === 1, 'bg-label-info' => $loop->iteration === 2, 'bg-label-success' => $loop->iteration === 3])><i class="icon-base bx bx-package"></i></span></div><div class="d-flex w-100 align-items-center justify-content-between gap-2"><div class="me-2 flex-grow-1"><h6 class="mb-1 text-truncate">{{ $barangPopuler['nama'] }}</h6><div class="progress" style="height: 6px"><div class="progress-bar" role="progressbar" style="width: {{ $lebarProduk }}%" aria-valuenow="{{ $lebarProduk }}" aria-valuemin="0" aria-valuemax="100"></div></div></div><span class="fw-semibold text-nowrap">{{ number_format($barangPopuler['jumlah'], 0, ',', '.') }}</span></div></div>
        @empty
            <p class="text-body-secondary mb-0">Belum ada data pemesanan barang.</p>
        @endforelse
    </div></div></div>

    <div class="col-12 col-lg-4 mb-6"><div class="card h-100"><div class="card-header d-flex align-items-center justify-content-between"><div><h5 class="card-title mb-1">Pendapatan Usaha</h5><p class="card-subtitle">Nilai pemesanan selama enam bulan</p></div><span class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-wallet"></i></span></span></div><div class="card-body"><div class="d-flex align-items-start justify-content-between mb-3"><div><small class="text-body-secondary">Bulan ini</small><h4 class="mb-0">Rp {{ number_format($nilaiPesananBulanIni, 0, ',', '.') }}</h4></div><span class="badge {{ $persentasePerubahanNilai >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">{{ $persentasePerubahanNilai > 0 ? '+' : '' }}{{ number_format($persentasePerubahanNilai, 1, ',', '.') }}%</span></div><div id="bumkam-revenue-trend-chart" role="img" aria-label="Grafik nilai pemesanan enam bulan terakhir"></div><div class="d-flex justify-content-between border-top pt-3 mt-3"><span class="text-body-secondary">Bulan lalu</span><strong>Rp {{ number_format($nilaiPesananBulanLalu, 0, ',', '.') }}</strong></div><p class="small text-body-secondary mt-3 mb-0">{{ $isPimpinan ? 'Ringkasan keuangan untuk pemantauan pimpinan.' : 'Ringkasan nilai pemesanan usaha.' }}</p></div></div></div>

    <div class="col-12 col-lg-4 mb-6"><div class="card h-100"><div class="card-header d-flex align-items-center justify-content-between"><div><h5 class="card-title mb-1">Transaksi Terbaru</h5><p class="card-subtitle">Lima pemesanan terakhir</p></div><a href="{{ route('laporan.index') }}" class="btn btn-sm btn-label-primary" aria-label="Buka laporan"><i class="icon-base bx bx-right-arrow-alt"></i></a></div><div class="card-body pt-4">
        @forelse ($transaksiTerbaru as $transaksi)
            @php($statusTransaksi = strtolower($transaksi->status_pemesanan ?? ''))
            <div data-dashboard-transaction class="d-flex align-items-center mb-5"><div class="avatar flex-shrink-0 me-3"><span @class(['avatar-initial rounded', 'bg-label-danger' => str_contains($statusTransaksi, 'batal'), 'bg-label-success' => str_contains($statusTransaksi, 'selesai') || (str_contains($statusTransaksi, 'lunas') && !str_contains($statusTransaksi, 'belum')), 'bg-label-primary' => str_contains($statusTransaksi, 'proses'), 'bg-label-warning' => !str_contains($statusTransaksi, 'batal') && !str_contains($statusTransaksi, 'selesai') && !(str_contains($statusTransaksi, 'lunas') && !str_contains($statusTransaksi, 'belum')) && !str_contains($statusTransaksi, 'proses')])><i class="icon-base bx bx-receipt"></i></span></div><div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2"><div class="me-2"><h6 class="mb-0 text-truncate" style="max-width: 9rem">{{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}</h6><small class="text-body-secondary">{{ \Carbon\Carbon::parse($transaksi->tanggal_pemesanan)->locale('id')->translatedFormat('d M Y') }} · {{ $transaksi->status_pemesanan ?? 'Status belum diketahui' }}</small></div><span class="small fw-semibold text-nowrap">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span></div></div>
        @empty
            <div class="text-center text-body-secondary py-5">Belum ada transaksi tercatat.</div>
        @endforelse
    </div></div></div>
</div>
