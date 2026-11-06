@extends('layouts.sneat-dashboard')

@section('title', 'Denda Keterlambatan')

@section('content')
<div class="space-y-6">
    @unless ($dendaAktif)
        <div role="status" class="alert alert-warning">Tabel fitur denda belum tersedia. Daftar denda akan aktif setelah migration fitur diterapkan.</div>
    @endunless
    @if ($errors->any())
        <div role="alert" class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="card overflow-hidden">
        <div class="card-header border-bottom">
            <h1 class="card-title mb-1">Denda Keterlambatan</h1>
            <p class="mb-0 text-body-secondary">Masa tenggang 7 hari. Setelah itu, denda dihitung otomatis sebesar 0,1% per hari dari sisa angsuran, maksimal 5%.</p>
            <p class="mt-2 mb-0 small text-body-secondary">Denda ditagihkan bersama pembayaran berikutnya. Nominal pembayaran yang disiapkan sistem mencakup sisa angsuran dan denda berjalan. Pembayaran digunakan untuk melunasi angsuran terlebih dahulu, lalu denda.</p>
            <form method="GET" action="{{ route('monitoring-kredit.denda') }}" class="denda-filter-form mt-4">
                <div>
                    <label class="form-label" for="cari-pelanggan-denda">Nama pelanggan</label>
                    <input id="cari-pelanggan-denda" name="q" type="search" value="{{ $kataKunci }}" class="form-control" placeholder="Cari nama pelanggan">
                </div>
                <div>
                    <label class="form-label" for="denda-dari-tanggal">Jatuh tempo dari</label>
                    <input id="denda-dari-tanggal" name="dari_tanggal" type="date" value="{{ $dariTanggal }}" class="form-control">
                </div>
                <div>
                    <label class="form-label" for="denda-sampai-tanggal">Sampai tanggal</label>
                    <input id="denda-sampai-tanggal" name="sampai_tanggal" type="date" value="{{ $sampaiTanggal }}" class="form-control">
                </div>
                <button type="submit" class="btn bumkam-green-action"><i class="icon-base bx bx-search me-1" aria-hidden="true"></i>Terapkan</button>
                @if ($kataKunci !== '' || $dariTanggal || $sampaiTanggal)
                    <a href="{{ route('monitoring-kredit.denda') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Pemesanan / Angsuran</th><th>Jatuh Tempo</th><th>Sisa Angsuran</th>
                        <th>Hari Terlambat</th><th>Denda Terhitung</th><th>Denda Dibayar</th><th>Sisa Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataDenda as $item)
                        <tr>
                            <td>
                                <a href="{{ route('monitoring-kredit.show', $item['pemesanan']->id_pemesanan) }}">{{ $item['pemesanan']->id_pemesanan }}</a>
                                <small class="d-block text-body-secondary">{{ $item['pemesanan']->pelanggan?->nama_pelanggan }} · Angsuran {{ $item['angsuran']['nomor'] }}</small>
                            </td>
                            <td class="text-nowrap">{{ $item['angsuran']['tanggal_jatuh_tempo']->format('d/m/Y') }}</td>
                            <td class="text-nowrap">Rp {{ number_format($item['angsuran']['sisa_angsuran'], 0, ',', '.') }}</td>
                            <td>{{ $item['angsuran']['hari_keterlambatan'] }} hari</td>
                            <td class="text-nowrap">Rp {{ number_format($item['angsuran']['denda_awal'], 0, ',', '.') }}</td>
                            <td class="text-nowrap">Rp {{ number_format($item['angsuran']['denda_dibayar'], 0, ',', '.') }}</td>
                            <td class="text-nowrap fw-semibold">Rp {{ number_format($item['angsuran']['denda_berjalan'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5">Belum ada angsuran yang terkena denda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($dataDenda->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $dataDenda->links() }}
            </div>
        @endif
    </section>
</div>
<style>
    .denda-filter-form {
        display: grid;
        width: 100%;
        grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr) minmax(0, 1fr) max-content max-content;
        align-items: end;
        gap: 14px;
    }
    .denda-filter-form > div { min-width: 0; }
    .denda-filter-form .form-label { display: block; margin-bottom: 6px; }
    .denda-filter-form .form-control { width: 100%; min-width: 0; }
    .denda-filter-form .btn { min-height: 38px; white-space: nowrap; padding-inline: 16px; }
    @media (max-width: 1100px) {
        .denda-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .denda-filter-form .btn { justify-self: start; }
    }
    @media (max-width: 575px) {
        .denda-filter-form { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endsection
