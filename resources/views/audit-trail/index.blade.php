@extends('layouts.sneat-dashboard')
@section('title', 'Audit Trail')
@section('content')
<section class="card overflow-hidden">
    <div class="card-header"><h2 class="card-title mb-1">Audit Trail</h2><p class="mb-0 text-body-secondary">Riwayat aktivitas akun. Password, NIK, dan isi dokumen tidak dicatat.</p></div>
    @if ($errors->any())
        <div role="alert" class="mx-4 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            @foreach ($errors->all() as $error)<p class="mb-1 last:mb-0">{{ $error }}</p>@endforeach
        </div>
    @endif
    <div class="card-body"><form method="GET" action="{{ route('audit-trail.index') }}" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <div class="min-w-0"><label class="form-label mb-1" for="audit-q">Nama / deskripsi</label><input id="audit-q" class="form-control" type="search" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Cari aktivitas"></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-pengguna">Pengguna</label><select id="audit-pengguna" class="form-select" name="pengguna"><option value="">Semua pengguna</option>@foreach ($pengguna as $user)<option value="{{ $user->id_pengguna }}" @selected(($filter['pengguna'] ?? '') === $user->id_pengguna)>{{ $user->nama_lengkap }}</option>@endforeach</select></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-jenis">Aktivitas</label><select id="audit-jenis" class="form-select" name="jenis"><option value="">Semua aktivitas</option>@foreach (['login','logout','create','update','delete','verify','approve','reject','handover','cancel','reverse','penyesuaian_denda','account_status','password_reset'] as $type)<option value="{{ $type }}" @selected(($filter['jenis'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>@endforeach</select></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-dari">Dari tanggal</label><input id="audit-dari" class="form-control" type="date" name="dari" value="{{ $filter['dari'] ?? '' }}"></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-sampai">Sampai tanggal</label><input id="audit-sampai" class="form-control" type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}"></div>
        <button class="btn btn-primary w-100" type="submit"><i class="icon-base bx bx-filter-alt me-1" aria-hidden="true"></i>Filter</button>
    </form></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead><tbody>
        @forelse ($aktivitas as $item)<tr><td class="text-nowrap">{{ $item->created_at?->format('d/m/Y H:i:s') }}</td><td>{{ $item->nama_pengguna }}<small class="d-block text-body-secondary">{{ $item->peran_pengguna ?? 'Sistem' }}</small></td><td><span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $item->jenis_aktivitas)) }}</span></td><td>{{ $item->modul }}</td><td>{{ $item->deskripsi }}</td><td class="text-nowrap">{{ $item->ip_address ?? '—' }}</td></tr>
        @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada aktivitas untuk filter ini.</td></tr>@endforelse
    </tbody></table></div>
    @if ($aktivitas->hasPages())<div class="card-footer">{{ $aktivitas->links() }}</div>@endif
</section>
@endsection
