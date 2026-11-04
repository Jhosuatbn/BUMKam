@extends('layouts.sneat-dashboard')
@section('title', 'Pengeluaran Operasional')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <section class="card overflow-hidden"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">Pengeluaran Operasional</h2><p class="mb-0 text-body-secondary">Catatan kas keluar dengan alasan, tanggal, nominal, dan pencatat.</p></div>@if (auth()->user()?->peran === 'Staff')<a href="{{ route('pengeluaran.create') }}" class="btn bumkam-green-action rounded-xl"><i class="icon-base bx bx-plus me-1"></i>Catat Pengeluaran</a>@endif</div>
        <div class="card-body border-bottom"><form method="GET" class="flex flex-wrap items-end gap-3"><div><label class="form-label" for="start_date">Dari tanggal</label><input class="form-control" type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"></div><div><label class="form-label" for="end_date">Sampai tanggal</label><input class="form-control" type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"></div><button class="btn bumkam-green-action rounded-xl">Tampilkan</button></form></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor</th><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th>Dicatat Oleh</th></tr></thead><tbody>@forelse ($rows as $row)<tr><td class="fw-semibold">{{ $row->id_pengeluaran }}</td><td>{{ $row->tanggal_pengeluaran?->format('d/m/Y') }}</td><td>{{ $row->kategori }}</td><td>{{ $row->keterangan }}</td><td>Rp {{ number_format($row->nominal, 0, ',', '.') }}</td><td>{{ $row->pencatat?->nama_lengkap ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada catatan pengeluaran.</td></tr>@endforelse</tbody></table></div>
        <div class="card-footer flex flex-wrap justify-between gap-2"><span>{{ $rows->links() }}</span><strong>Total hasil filter: Rp {{ number_format($total, 0, ',', '.') }}</strong></div>
    </section>
</div>
@endsection
