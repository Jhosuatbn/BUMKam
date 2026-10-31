@extends('layouts.sneat-dashboard')

@section('title', 'Detail Monitoring Kredit')

@section('content')
    @php
        $statusClass = match ($ringkasan['status']) { 'Lunas' => 'success', 'Terlambat' => 'danger', 'Jatuh Tempo Hari Ini' => 'warning', default => 'info' };
        $pokokKredit = max(0, (float) $pemesanan->total_harga - (float) $pemesanan->dp);
    @endphp
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-primary">Pengawasan Kredit</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Rincian Kredit</h1>
                <p class="mt-2 text-sm text-slate-600">Pemesanan {{ $pemesanan->id_pemesanan }} · {{ $pemesanan->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak ditemukan' }}</p>
            </div>
            <a href="{{ route('monitoring-kredit.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali ke Monitoring</a>
        </header>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan kredit">
            <article class="card"><div class="card-body"><p class="mb-2 text-sm text-body-secondary">Total Kewajiban</p><p class="mb-0 text-xl font-bold">Rp {{ number_format($ringkasan['total_kewajiban'], 0, ',', '.') }}</p></div></article>
            <article class="card"><div class="card-body"><p class="mb-2 text-sm text-body-secondary">Sudah Dibayar</p><p class="mb-0 text-xl font-bold text-success">Rp {{ number_format($ringkasan['total_terbayar'], 0, ',', '.') }}</p></div></article>
            <article class="card"><div class="card-body"><p class="mb-2 text-sm text-body-secondary">Sisa Kewajiban</p><p class="mb-0 text-xl font-bold text-danger">Rp {{ number_format($ringkasan['sisa_kewajiban'], 0, ',', '.') }}</p></div></article>
            <article class="card"><div class="card-body"><p class="mb-2 text-sm text-body-secondary">Status Kredit</p><span class="badge bg-label-{{ $statusClass }}">{{ $ringkasan['status'] }}</span></div></article>
        </section>

        <section class="card" aria-labelledby="informasi-kredit-heading">
            <div class="card-header"><h2 id="informasi-kredit-heading" class="card-title">Informasi Pelanggan dan Pemesanan</h2></div>
            <div class="card-body grid grid-cols-1 gap-x-8 sm:grid-cols-2 xl:grid-cols-3">
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Pelanggan</p><p class="mb-0 fw-semibold">{{ $pemesanan->pelanggan?->nama_pelanggan ?? '—' }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">ID Pelanggan</p><p class="mb-0 fw-semibold">{{ $pemesanan->id_pelanggan }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Nomor Telepon</p><p class="mb-0 fw-semibold">{{ $pemesanan->pelanggan?->no_telepon ?? '—' }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">ID Pemesanan</p><p class="mb-0 fw-semibold">{{ $pemesanan->id_pemesanan }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Tanggal Pemesanan</p><p class="mb-0 fw-semibold">{{ \Carbon\Carbon::parse($pemesanan->tanggal_pemesanan)->format('d/m/Y') }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Barang</p><p class="mb-0 fw-semibold">{{ $pemesanan->detailPemesanan->pluck('barang.nama_barang')->filter()->implode(', ') ?: '—' }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Skema Pembayaran</p><p class="mb-0 fw-semibold">{{ $pemesanan->skema_pembayaran }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Harga Barang</p><p class="mb-0 fw-semibold">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">DP</p><p class="mb-0 fw-semibold">Rp {{ number_format($pemesanan->dp, 0, ',', '.') }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Pokok Kredit</p><p class="mb-0 fw-semibold">Rp {{ number_format($pokokKredit, 0, ',', '.') }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Bunga</p><p class="mb-0 fw-semibold">Rp {{ number_format($pemesanan->bunga, 0, ',', '.') }}</p></div>
                <div class="border-b py-3"><p class="mb-1 text-sm text-body-secondary">Tenor dan Angsuran</p><p class="mb-0 fw-semibold">{{ $pemesanan->tenor }} bulan · Rp {{ number_format($pemesanan->angsuran, 0, ',', '.') }}/bulan</p></div>
            </div>
        </section>

        <section class="card overflow-hidden" aria-labelledby="jadwal-angsuran-heading">
            <div class="card-header">
                <h2 id="jadwal-angsuran-heading" class="card-title mb-1">Jadwal dan Riwayat Angsuran</h2>
                <p class="mb-0 text-sm text-body-secondary">Jatuh tempo bulanan dimulai satu bulan setelah tanggal pemesanan. Pembayaran online yang belum settlement tidak dihitung sebagai angsuran lunas.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Angsuran</th><th scope="col">Jatuh Tempo</th><th scope="col">Tanggal Bayar</th><th scope="col">Nominal</th><th scope="col">Sudah Dibayar</th><th scope="col">Status</th><th scope="col">ID Transaksi</th></tr></thead>
                    <tbody>
                        @foreach ($ringkasan['jadwal'] as $angsuran)
                            @php($statusAngsuranClass = match ($angsuran['status']) { 'Lunas' => 'success', 'Terlambat' => 'danger', 'Jatuh Tempo Hari Ini' => 'warning', default => 'info' })
                            <tr>
                                <td>Angsuran {{ $angsuran['nomor'] }}</td>
                                <td class="text-nowrap">{{ $angsuran['tanggal_jatuh_tempo']->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $angsuran['tanggal_pembayaran'] ? \Carbon\Carbon::parse($angsuran['tanggal_pembayaran'])->format('d/m/Y') : '—' }}</td>
                                <td class="text-nowrap">Rp {{ number_format($angsuran['nominal'], 0, ',', '.') }}</td>
                                <td class="text-nowrap">Rp {{ number_format($angsuran['nominal_terbayar'], 0, ',', '.') }}</td>
                                <td><span class="badge bg-label-{{ $statusAngsuranClass }}">{{ $angsuran['status'] }}</span></td>
                                <td class="small">{{ $angsuran['id_transaksi'] ? implode(', ', $angsuran['id_transaksi']) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
