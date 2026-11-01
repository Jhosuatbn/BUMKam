@extends('layouts.sneat-dashboard')

@section('title', 'Denda Keterlambatan')

@section('content')
<div class="space-y-6">
    @unless ($dendaAktif)<div role="status" class="alert alert-warning">Tabel fitur denda belum tersedia. Daftar dan keputusan denda akan aktif setelah migration fitur diterapkan.</div>@endunless
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <section class="card overflow-hidden">
        <div class="card-header border-bottom"><h1 class="card-title mb-1">Denda Keterlambatan</h1><p class="mb-0 text-body-secondary">Tarif 0,1% per hari, maksimal 5% dari sisa angsuran. Masa tenggang 7 hari.</p></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Pemesanan / Angsuran</th><th>Jatuh Tempo</th><th>Sisa Angsuran</th><th>Hari Terlambat</th><th>Denda Awal</th><th>Dikurangi</th><th>Dibayar</th><th>Sisa Denda</th><th>Keputusan</th></tr></thead>
            <tbody>
            @php($adaBaris = false)
            @foreach ($dataDenda as $item)
                @foreach ($item['ringkasan']['jadwal'] as $row)
                    @continue($row['denda_awal'] <= 0 && $row['pengurangan_denda'] <= 0)
                    @php($adaBaris = true)
                    <tr>
                        <td><a href="{{ route('monitoring-kredit.show', $item['pemesanan']->id_pemesanan) }}">{{ $item['pemesanan']->id_pemesanan }}</a><small class="d-block text-body-secondary">{{ $item['pemesanan']->pelanggan?->nama_pelanggan }} · Angsuran {{ $row['nomor'] }}</small></td>
                        <td class="text-nowrap">{{ $row['tanggal_jatuh_tempo']->format('d/m/Y') }}</td><td class="text-nowrap">Rp {{ number_format($row['sisa_angsuran'], 0, ',', '.') }}</td><td>{{ $row['hari_keterlambatan'] }} hari</td>
                        <td class="text-nowrap">Rp {{ number_format($row['denda_awal'], 0, ',', '.') }}</td><td class="text-nowrap">Rp {{ number_format($row['pengurangan_denda'], 0, ',', '.') }}</td><td class="text-nowrap">Rp {{ number_format($row['denda_dibayar'], 0, ',', '.') }}</td><td class="text-nowrap fw-semibold">Rp {{ number_format($row['denda_berjalan'], 0, ',', '.') }}</td>
                        <td class="min-w-64">
                            @forelse ($item['pemesanan']->penyesuaianDenda->where('nomor_angsuran', $row['nomor']) as $decision)
                                <div class="mb-2 text-xs"><strong>{{ $decision->pimpinan?->nama_lengkap }}</strong> · {{ $decision->diputuskan_pada?->format('d/m/Y H:i') }}<br>Rp {{ number_format($decision->nominal_pengurangan, 0, ',', '.') }} — {{ $decision->alasan }}</div>
                            @empty<span class="text-body-secondary">Belum ada keputusan</span>@endforelse
                            @if (auth()->user()?->peran === 'Pimpinan' && $row['denda_berjalan'] > 0)
                                <form method="POST" action="{{ route('monitoring-kredit.denda.reduce', $item['pemesanan']->id_pemesanan) }}" class="mt-2 space-y-2">
                                    @csrf<input type="hidden" name="nomor_angsuran" value="{{ $row['nomor'] }}">
                                    <input name="nominal_pengurangan" type="number" min="0.01" max="{{ $row['denda_berjalan'] }}" step="0.01" required class="form-control form-control-sm" placeholder="Nominal pengurangan">
                                    <textarea name="alasan" required minlength="8" maxlength="2000" class="form-control form-control-sm" rows="2" placeholder="Alasan keputusan"></textarea>
                                    <button class="btn btn-sm btn-primary" type="submit">Simpan Keputusan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
            @unless($adaBaris)<tr><td colspan="9" class="text-center py-5">Belum ada angsuran yang terkena denda.</td></tr>@endunless
            </tbody>
        </table></div>
    </section>
</div>
@endsection
