@extends('layouts.sneat-dashboard')
@section('title', 'Data Supplier')
@section('content')
<section class="card overflow-hidden">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><h1 class="card-title mb-1">Daftar Supplier</h1><p class="mb-0 text-body-secondary">Pemasok yang menjadi sumber barang dan riwayat pembelian BUMKam.</p></div>
        @if (auth()->user()?->peran === 'Staff')<a href="{{ route('supplier.create') }}" class="btn bumkam-green-action mt-2"><i class="icon-base bx bx-plus me-1"></i>Tambah Supplier</a>@endif
    </div>
    <div class="card-body border-bottom"><form method="GET" class="d-flex flex-wrap gap-2"><input name="q" value="{{ request('q') }}" class="form-control" style="max-width:24rem" placeholder="Cari nama atau nomor telepon"><button class="btn bumkam-green-action"><i class="icon-base bx bx-search me-1"></i>Cari</button></form></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Supplier</th><th>Kontak</th><th>Telepon</th><th>Riwayat Pembelian</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($suppliers as $supplier)<tr><td><div class="fw-semibold">{{ $supplier->nama_supplier }}</div><small class="text-body-secondary">{{ $supplier->id_supplier }}</small></td><td>{{ $supplier->kontak_person ?: '—' }}</td><td>{{ $supplier->no_telepon ?: '—' }}</td><td>{{ $supplier->barang_masuk_count }} transaksi</td><td><span class="badge {{ $supplier->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="{{ route('supplier.show',$supplier) }}">Detail</a>@if(auth()->user()?->peran === 'Staff') <a class="btn btn-sm btn-outline-success" href="{{ route('supplier.edit',$supplier) }}">Kelola</a>@endif</td></tr>
    @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada data supplier.</td></tr>@endforelse
    </tbody></table></div><div class="card-footer">{{ $suppliers->links() }}</div>
</section>
@endsection
