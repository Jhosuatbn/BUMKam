@extends('layouts.sneat-dashboard')

@section('title', 'Monitoring Kredit')

@section('content')
    <div class="space-y-6">
        <section class="card overflow-hidden" aria-labelledby="ringkasan-kredit-heading">
            <div class="card-header border-b">
                <h2 id="ringkasan-kredit-heading" class="card-title mb-1">Ringkasan Kredit</h2>
                <p class="mb-0 text-sm text-body-secondary">Gambaran singkat kondisi angsuran pelanggan.</p>
            </div>
            @php
                $kartu = [
                    ['label' => 'Pelanggan Kredit Aktif', 'nilai' => number_format($ringkasan['pelanggan_aktif'], 0, ',', '.'), 'ikon' => 'bx-group', 'warna' => 'var(--bs-primary)'],
                    ['label' => 'Kredit Lunas', 'nilai' => number_format($ringkasan['kredit_lunas'], 0, ',', '.'), 'ikon' => 'bx-check-circle', 'warna' => 'var(--bs-success)'],
                    ['label' => 'Kredit Belum Lunas', 'nilai' => number_format($ringkasan['kredit_belum_lunas'], 0, ',', '.'), 'ikon' => 'bx-time-five', 'warna' => 'var(--bs-warning)'],
                    ['label' => 'Angsuran Jatuh Tempo Hari Ini', 'nilai' => number_format($ringkasan['jatuh_tempo_hari_ini'], 0, ',', '.'), 'ikon' => 'bx-calendar-exclamation', 'warna' => 'var(--bs-info)'],
                    ['label' => 'Angsuran Terlambat', 'nilai' => number_format($ringkasan['angsuran_terlambat'], 0, ',', '.'), 'ikon' => 'bx-error-circle', 'warna' => 'var(--bs-danger)'],
                    ['label' => 'Total Sisa Piutang', 'nilai' => 'Rp ' . number_format($ringkasan['total_piutang'], 0, ',', '.'), 'ikon' => 'bx-wallet', 'warna' => 'var(--bs-primary)'],
                ];
            @endphp
            <div class="grid grid-cols-1 gap-x-6 gap-y-2 px-4 py-3 sm:grid-cols-2 sm:px-5 xl:grid-cols-3">
                @foreach ($kartu as $item)
                    <article class="flex min-w-0 items-center gap-3 py-3">
                        <i class="icon-base bx {{ $item['ikon'] }} shrink-0 text-2xl" style="color: {{ $item['warna'] }}" aria-hidden="true"></i>
                        <div class="min-w-0">
                            <p class="mb-1 text-sm leading-snug text-body-secondary">{{ $item['label'] }}</p>
                            <p class="mb-0 break-words text-xl font-bold text-slate-900 tabular-nums">{{ $item['nilai'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="card overflow-hidden" aria-labelledby="daftar-kredit-heading">
            <div class="card-header flex flex-col gap-4 border-b sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="daftar-kredit-heading" class="card-title mb-1">Daftar Kredit Pelanggan</h2>
                    <p class="mb-0 text-sm text-body-secondary">Status dihitung dari jadwal angsuran dan pembayaran yang telah terverifikasi.</p>
                </div>
                <form method="GET" action="{{ route('monitoring-kredit.index') }}" class="grid w-full grid-cols-1 gap-2 sm:w-auto sm:grid-cols-[minmax(14rem,1fr)_13rem_auto]">
                    <label class="sr-only" for="q">Cari pelanggan atau ID pemesanan</label>
                    <input id="q" name="q" type="search" value="{{ $kataKunci }}" class="form-control" placeholder="Nama pelanggan atau ID pemesanan">
                    <label class="sr-only" for="status">Filter status kredit</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach (['Belum Jatuh Tempo', 'Jatuh Tempo Hari Ini', 'Terlambat', 'Lunas'] as $status)
                            <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-search me-1" aria-hidden="true"></i>Filter</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Pemesanan / Pelanggan</th>
                            <th scope="col">Barang</th>
                            <th scope="col">Total Kredit</th>
                            <th scope="col">Angsuran</th>
                            <th scope="col">Terbayar</th>
                            <th scope="col">Belum Bayar</th>
                            <th scope="col">Sisa Kewajiban</th>
                            <th scope="col">Jatuh Tempo Berikutnya</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kredit as $item)
                            @php($monitoringItem = $item->monitoring_kredit)
                            @php($statusClass = match ($monitoringItem['status']) { 'Lunas' => 'success', 'Terlambat' => 'danger', 'Jatuh Tempo Hari Ini' => 'warning', default => 'info' })
                            @php($namaBarang = $item->detailPemesanan->pluck('barang.nama_barang')->filter()->implode(', '))
                            <tr>
                                <td>
                                    <a href="{{ route('monitoring-kredit.show', $item->id_pemesanan) }}" class="fw-semibold">{{ $item->id_pemesanan }}</a>
                                    <span class="d-block text-body-secondary">{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak ditemukan' }}</span>
                                    <span class="d-block text-body-secondary small">{{ \Carbon\Carbon::parse($item->tanggal_pemesanan)->format('d/m/Y') }}</span>
                                </td>
                                <td>{{ $namaBarang !== '' ? $namaBarang : '—' }}</td>
                                <td class="text-nowrap">Rp {{ number_format($monitoringItem['total_kewajiban'], 0, ',', '.') }}</td>
                                <td class="text-nowrap">Rp {{ number_format($item->angsuran, 0, ',', '.') }} <span class="text-body-secondary">× {{ $item->tenor }}</span></td>
                                <td>{{ $monitoringItem['angsuran_terbayar'] }} angsuran</td>
                                <td>{{ $monitoringItem['angsuran_belum_dibayar'] }} angsuran</td>
                                <td class="text-nowrap fw-semibold">Rp {{ number_format($monitoringItem['sisa_kewajiban'], 0, ',', '.') }}</td>
                                <td class="text-nowrap">
                                    {{ $monitoringItem['tanggal_jatuh_tempo_berikutnya']?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td><span class="badge bg-label-{{ $statusClass }}">{{ $monitoringItem['status'] }}</span></td>
                                <td><a href="{{ route('monitoring-kredit.show', $item->id_pemesanan) }}" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-show-alt me-1" aria-hidden="true"></i>Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="py-5 text-center text-body-secondary">Tidak ada data kredit yang cocok dengan pencarian atau filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($kredit->hasPages())
                <div class="card-footer">{{ $kredit->links() }}</div>
            @endif
        </section>
    </div>
@endsection
