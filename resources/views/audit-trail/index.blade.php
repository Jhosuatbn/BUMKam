@extends('layouts.sneat-dashboard')
@section('title', 'Audit Trail')
@section('content')
<section class="card overflow-hidden">
    <div class="card-header"><h2 class="card-title mb-1">Audit Trail</h2><p class="mb-0 text-body-secondary">Riwayat aktivitas akun. Password, NIK, dan isi dokumen tidak dicatat.</p></div>
    <div class="card-body"><form method="GET" action="{{ route('audit-trail.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <input class="form-control xl:col-span-2" type="search" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Cari nama / deskripsi" aria-label="Cari aktivitas">
        <select class="form-select" name="pengguna"><option value="">Semua pengguna</option>@foreach ($pengguna as $user)<option value="{{ $user->id_pengguna }}" @selected(($filter['pengguna'] ?? '') === $user->id_pengguna)>{{ $user->nama_lengkap }}</option>@endforeach</select>
        <select class="form-select" name="jenis"><option value="">Semua aktivitas</option>@foreach (['login','logout','create','update','delete','verify','account_status','password_reset'] as $type)<option value="{{ $type }}" @selected(($filter['jenis'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>@endforeach</select>
        <input class="form-control" type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" aria-label="Tanggal mulai">
        <input class="form-control" type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" aria-label="Tanggal akhir">
        <button class="btn btn-primary xl:col-span-6" type="submit"><i class="icon-base bx bx-filter-alt me-1" aria-hidden="true"></i>Terapkan Filter</button>
    </form></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead><tbody>
        @forelse ($aktivitas as $item)<tr><td class="text-nowrap">{{ $item->created_at?->format('d/m/Y H:i:s') }}</td><td>{{ $item->nama_pengguna }}<small class="d-block text-body-secondary">{{ $item->peran_pengguna ?? 'Sistem' }}</small></td><td><span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $item->jenis_aktivitas)) }}</span></td><td>{{ $item->modul }}</td><td>{{ $item->deskripsi }}</td><td class="text-nowrap">{{ $item->ip_address ?? '—' }}</td></tr>
        @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada aktivitas untuk filter ini.</td></tr>@endforelse
    </tbody></table></div>
    @if ($aktivitas->hasPages())<div class="card-footer">{{ $aktivitas->links() }}</div>@endif
</section>
@endsection
