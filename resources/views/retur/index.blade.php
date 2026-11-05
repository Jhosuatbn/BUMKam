@extends('layouts.sneat-dashboard')
@section('title', 'Returan Pelanggan')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <section class="card overflow-hidden">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">Returan Pelanggan</h2><p class="mb-0 text-body-secondary">Pengajuan retur, keputusan kondisi barang, dan pencatatan persediaan.</p></div>@if (auth()->user()?->peran === 'Staff')<button type="button" class="btn bumkam-green-action rounded-xl" data-bs-toggle="modal" data-bs-target="#ajukanReturModal"><i class="icon-base bx bx-plus me-1"></i>Ajukan Retur</button>@endif</div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor Retur</th><th>Pemesanan / Pelanggan</th><th>Tanggal</th><th>Dicatat Oleh</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse ($rows as $row)<tr><td class="fw-semibold">{{ $row->id_retur }}</td><td>{{ $row->id_pemesanan }}<small class="d-block text-body-secondary">{{ $row->pemesanan?->pelanggan?->nama_pelanggan ?? '-' }}</small></td><td>{{ $row->created_at?->format('d/m/Y') }}</td><td>{{ $row->pencatat?->nama_lengkap ?? '-' }}</td><td><span class="badge bg-label-{{ $row->status === 'Disetujui' ? 'success' : ($row->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $row->status }}</span></td><td><a href="{{ route('retur.show', $row) }}" class="btn btn-sm btn-outline-primary">Lihat</a></td></tr>
        @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada pengajuan retur.</td></tr>@endforelse
        </tbody></table></div><div class="card-footer">{{ $rows->links() }}</div>
    </section>
</div>
@if (auth()->user()?->peran === 'Staff')
    <div class="modal fade" id="ajukanReturModal" tabindex="-1" aria-labelledby="ajukanReturModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content">
            <form action="{{ route('retur.store') }}" method="POST">@csrf
                <div class="modal-header"><div><h2 class="modal-title fs-5" id="ajukanReturModalLabel">Ajukan Returan Pelanggan</h2><p class="mb-0 mt-1 small text-body-secondary">Barang hanya dapat dikembalikan setelah diserahkan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" data-no-auto-icon></button></div>
                <div class="modal-body">@include('retur._form-fields')</div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-xl" data-bs-dismiss="modal" data-no-auto-icon>Batal</button><button class="btn bumkam-green-action rounded-xl" type="submit" data-no-auto-icon>Simpan Pengajuan</button></div>
            </form>
        </div></div>
    </div>
    <script>document.body.appendChild(document.getElementById('ajukanReturModal'));</script>
    @if ($errors->any())<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('ajukanReturModal')).show());</script>@endif
@endif
@endsection
