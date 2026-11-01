@extends('layouts.sneat-dashboard')

@section('title', 'Jadwal Angsuran')

@section('content')
@unless ($dendaAktif)<div role="status" class="alert alert-warning">Migration fitur denda belum diterapkan. Jadwal angsuran tersedia, tetapi informasi denda baru aktif setelah migration dijalankan.</div>@endunless
<section class="card overflow-hidden">
    <div class="card-header border-bottom">
        <h1 class="card-title mb-1">Jadwal Angsuran</h1>
        <p class="mb-0 text-body-secondary">Masa tenggang 7 hari. Denda mulai dihitung setelah masa tenggang.</p>
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Pemesanan</th><th>Angsuran</th><th>Jatuh Tempo</th><th>Nilai</th><th>Terbayar</th><th>Sisa</th><th>Hari Terlambat</th><th>Denda</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($jadwal as $row)
            @php($warna = match ($row['status']) { 'Lunas' => 'success', 'Terlambat' => 'danger', 'Dalam Masa Tenggang', 'Jatuh Tempo Hari Ini' => 'warning', default => 'info' })
            <tr>
                <td><a href="{{ route('monitoring-kredit.show', $row['pemesanan']->id_pemesanan) }}">{{ $row['pemesanan']->id_pemesanan }}</a><small class="d-block text-body-secondary">{{ $row['pemesanan']->pelanggan?->nama_pelanggan }}</small></td>
                <td>Angsuran {{ $row['nomor'] }}</td><td class="text-nowrap">{{ $row['tanggal_jatuh_tempo']->format('d/m/Y') }}</td>
                <td class="text-nowrap">Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td><td class="text-nowrap">Rp {{ number_format($row['nominal_terbayar'], 0, ',', '.') }}</td>
                <td class="text-nowrap">Rp {{ number_format($row['sisa_angsuran'], 0, ',', '.') }}</td><td>{{ $row['hari_keterlambatan'] }} hari</td>
                <td class="text-nowrap">Rp {{ number_format($row['denda_berjalan'], 0, ',', '.') }}</td><td><span class="badge bg-label-{{ $warna }}">{{ $row['status'] }}</span></td>
            </tr>
        @empty<tr><td colspan="9" class="text-center py-5">Belum ada jadwal angsuran.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>
@endsection
