@extends('layouts.sneat-dashboard')
@section('title', 'Data Supplier')
@section('content')
<section class="card mobile-list-page overflow-hidden">
    <div class="card-header mobile-list-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><h1 class="card-title mb-1">Daftar Supplier</h1><p class="mb-0 text-body-secondary">Pemasok yang menjadi sumber barang dan riwayat pembelian BUMKam.</p></div>
        @if (auth()->user()?->peran === 'Staff')<a href="{{ route('supplier.create') }}" class="mobile-list-add btn bumkam-green-action mt-2"><i class="icon-base bx bx-plus me-1"></i>Tambah Supplier</a>@endif
    </div>
    @if ($errors->any())
        <div role="alert" class="alert alert-danger mx-3 mt-3 mb-0" aria-live="assertive">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    @if (session('success'))<div role="status" class="alert alert-success mx-3 mt-3 mb-0">{{ session('success') }}</div>@endif
    @if (session('error'))<div role="alert" class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error') }}</div>@endif
    <div class="card-body mobile-list-search-container border-bottom"><form method="GET" class="mobile-list-search d-flex flex-wrap gap-2"><input name="q" value="{{ request('q') }}" class="form-control" style="max-width:24rem" placeholder="Cari nama atau nomor telepon"><button class="btn bumkam-green-action"><i class="icon-base bx bx-search me-1"></i>Cari</button></form></div>
    <div class="table-responsive"><table class="table mobile-card-table table-hover align-middle mb-0"><thead><tr><th>Supplier</th><th>Kontak</th><th>Telepon</th><th>Riwayat Pembelian</th><th>Status</th><th class="text-center">Aksi</th></tr></thead><tbody>
    @forelse($suppliers as $supplier)<tr><td data-label="Supplier"><div class="fw-semibold">{{ $supplier->nama_supplier }}</div><small class="text-body-secondary">{{ $supplier->id_supplier }}</small></td><td data-label="Kontak">{{ $supplier->kontak_person ?: '—' }}</td><td data-label="Telepon">{{ $supplier->no_telepon ?: '—' }}</td><td data-label="Riwayat Pembelian">{{ $supplier->barang_masuk_count }} transaksi</td><td data-label="Status"><span class="badge {{ $supplier->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td data-label="Aksi" class="text-nowrap text-center"><div class="d-flex align-items-center justify-content-center gap-2"><a class="btn btn-sm btn-outline-success" href="{{ route('supplier.show',$supplier) }}" title="Detail supplier"><i class="icon-base bx bx-show me-1" aria-hidden="true"></i>Detail</a>@if(auth()->user()?->peran === 'Staff') <a class="btn btn-sm btn-outline-success" href="{{ route('supplier.edit',$supplier) }}" title="Kelola supplier"><i class="icon-base bx bx-edit me-1" aria-hidden="true"></i>Kelola</a>@endif</div></td></tr>
    @empty<tr class="mobile-card-empty"><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada data supplier.</td></tr>@endforelse
    </tbody></table></div><div class="card-footer">{{ $suppliers->links() }}</div>
</section>
@endsection
