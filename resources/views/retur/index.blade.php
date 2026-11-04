@extends('layouts.sneat-dashboard')
@section('title', 'Retur Pelanggan')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <section class="card overflow-hidden">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">Retur Pelanggan</h2><p class="mb-0 text-body-secondary">Pengajuan retur, keputusan kondisi barang, dan pencatatan persediaan.</p></div>@if (auth()->user()?->peran === 'Staff')<a href="{{ route('retur.create') }}" class="btn bumkam-green-action rounded-xl"><i class="icon-base bx bx-plus me-1"></i>Ajukan Retur</a>@endif</div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor Retur</th><th>Pemesanan / Pelanggan</th><th>Tanggal</th><th>Dicatat Oleh</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse ($rows as $row)<tr><td class="fw-semibold">{{ $row->id_retur }}</td><td>{{ $row->id_pemesanan }}<small class="d-block text-body-secondary">{{ $row->pemesanan?->pelanggan?->nama_pelanggan ?? '-' }}</small></td><td>{{ $row->created_at?->format('d/m/Y') }}</td><td>{{ $row->pencatat?->nama_lengkap ?? '-' }}</td><td><span class="badge bg-label-{{ $row->status === 'Disetujui' ? 'success' : ($row->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $row->status }}</span></td><td><a href="{{ route('retur.show', $row) }}" class="btn btn-sm btn-outline-primary">Lihat</a></td></tr>
        @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada pengajuan retur.</td></tr>@endforelse
        </tbody></table></div><div class="card-footer">{{ $rows->links() }}</div>
    </section>
</div>
@endsection
