@extends('layouts.sneat-dashboard')
@section('title', 'Detail Stok Opname')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <section class="card"><div class="card-header d-flex flex-wrap justify-content-between gap-2"><div><h2 class="card-title mb-1">Stok Opname {{ $opname->id_opname }}</h2><p class="mb-0 text-body-secondary">{{ $opname->tanggal_opname?->format('d/m/Y') }} · Dicatat {{ $opname->pencatat?->nama_lengkap ?? '-' }}</p></div><span class="badge bg-label-{{ $opname->status === 'Disetujui' ? 'success' : ($opname->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $opname->status }}</span></div>
        <div class="card-body"><p><strong>Alasan:</strong> {{ $opname->alasan }}</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Barang</th><th>Stok Sistem Saat Catat</th><th>Stok Fisik</th><th>Selisih</th></tr></thead><tbody>@foreach ($opname->details as $item)<tr><td>{{ $item->barang?->nama_barang ?? '-' }}</td><td>{{ $item->stok_sistem }}</td><td>{{ $item->stok_fisik }}</td><td class="fw-semibold">{{ $item->stok_fisik - $item->stok_sistem }}</td></tr>@endforeach</tbody></table></div>
            @if ($opname->diputuskan_pada)<div class="rounded-xl bg-slate-50 p-4"><strong>Keputusan {{ $opname->status }}</strong><p class="mb-1">{{ $opname->catatan_keputusan }}</p><small>{{ $opname->pemutus?->nama_lengkap ?? '-' }} · {{ $opname->diputuskan_pada->format('d/m/Y H:i') }}</small></div>@endif
        </div>
        @if (auth()->user()?->peran === 'Pimpinan' && $opname->status === 'Menunggu Persetujuan')<form action="{{ route('stok-opname.decide', $opname) }}" method="POST" class="card-footer space-y-3">@csrf<div><label class="form-label">Keputusan</label><select name="keputusan" class="form-select" required><option value="Disetujui">Setujui dan terapkan selisih</option><option value="Ditolak">Tolak</option></select></div><div><label class="form-label">Catatan keputusan</label><textarea name="catatan_keputusan" required minlength="5" maxlength="2000" rows="2" class="form-control"></textarea></div><div class="flex justify-end"><button class="btn bumkam-green-action rounded-xl">Simpan Keputusan</button></div></form>@endif
        <div class="card-footer flex justify-end"><a href="{{ route('stok-opname.index') }}" class="btn btn-outline-secondary rounded-xl">Kembali</a></div>
    </section>
</div>
@endsection
