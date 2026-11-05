@extends('layouts.sneat-dashboard')
@section('title', 'Stok Opname')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <section class="card overflow-hidden"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">Stok Opname</h2><p class="mb-0 text-body-secondary">Hitung stok fisik. Selisih hanya diterapkan setelah persetujuan Pimpinan.</p></div>@if (auth()->user()?->peran === 'Staff')<a href="{{ route('stok-opname.create') }}" class="btn bumkam-green-action rounded-xl"><i class="icon-base bx bx-plus me-1"></i>Catat Stok Opname</a>@endif</div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor</th><th>Tanggal</th><th>Dicatat Oleh</th><th>Jumlah Barang</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse ($rows as $row)<tr><td class="fw-semibold">{{ $row->id_opname }}</td><td>{{ $row->tanggal_opname?->format('d/m/Y') }}</td><td>{{ $row->pencatat?->nama_lengkap ?? '-' }}</td><td>{{ $row->details_count }}</td><td><span class="badge bg-label-{{ $row->status === 'Disetujui' ? 'success' : ($row->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $row->status }}</span></td><td><a href="{{ route('stok-opname.show', $row) }}" class="btn btn-sm btn-outline-primary" aria-label="Lihat detail stok opname {{ $row->id_opname }}" title="Detail"><i class="icon-base bx bx-show" aria-hidden="true"></i></a></td></tr>@empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada stok opname.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $rows->links() }}</div>
    </section>
</div>
@endsection
