@extends('layouts.sneat-dashboard')
@section('title', 'Catat Stok Opname')
@section('content')
<div class="mx-auto max-w-5xl">@if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive"><div class="fw-semibold">Stok opname belum dapat disimpan:</div><ul class="mb-0 mt-2 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('stok-opname.store') }}" method="POST" class="card">@csrf
    <div class="card-header"><h2 class="card-title mb-1">Catat Stok Opname</h2><p class="mb-0 text-body-secondary">Masukkan hasil hitung fisik untuk barang dengan stok aktif. Stok sistem dicatat sebagai pembanding.</p></div>
    <div class="card-body space-y-4"><div class="max-w-xs"><label class="form-label" for="tanggal_opname">Tanggal Opname</label><input required type="date" id="tanggal_opname" name="tanggal_opname" value="{{ old('tanggal_opname', now()->toDateString()) }}" class="form-control"></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Barang</th><th>Stok Sistem</th><th>Stok Fisik</th></tr></thead><tbody>
        @forelse ($barang as $item)<tr><td>{{ $item->nama_barang }}<small class="d-block text-body-secondary">{{ $item->id_barang }}</small><input type="hidden" name="items[{{ $loop->index }}][id_barang]" value="{{ $item->id_barang }}"></td><td>{{ $item->stok }}</td><td><input required class="form-control max-w-40" type="number" min="0" name="items[{{ $loop->index }}][stok_fisik]" value="{{ old('items.'.$loop->index.'.stok_fisik', $item->stok) }}"></td></tr>@empty<tr><td colspan="3" class="text-center text-body-secondary">Belum ada barang yang stoknya diatur.</td></tr>@endforelse
        </tbody></table></div>
        <div><label class="form-label" for="alasan">Alasan / catatan pemeriksaan</label><textarea required minlength="5" maxlength="2000" id="alasan" name="alasan" rows="3" class="form-control" placeholder="Contoh: pemeriksaan stok bulanan">{{ old('alasan') }}</textarea></div>
    </div><div class="card-footer flex justify-end gap-2"><a href="{{ route('stok-opname.index') }}" class="btn btn-outline-secondary rounded-xl">Batal</a><button type="submit" class="btn bumkam-green-action rounded-xl">Kirim untuk Persetujuan</button></div>
</form></div>
@endsection
