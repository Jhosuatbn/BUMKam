@extends('layouts.sneat-dashboard')
@section('title', 'Catat Pengeluaran')
@section('content')
<div class="mx-auto max-w-2xl">@if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive"><div class="fw-semibold">Pengeluaran belum dapat disimpan:</div><ul class="mb-0 mt-2 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('pengeluaran.store') }}" method="POST" class="card">@csrf<div class="card-header"><h2 class="card-title mb-1">Catat Pengeluaran Operasional</h2><p class="mb-0 text-body-secondary">Catatan disimpan sebagai histori dan tidak dapat dihapus dari halaman ini.</p></div><div class="card-body space-y-4">
    <div><label for="tanggal_pengeluaran" class="form-label">Tanggal</label><input required type="date" name="tanggal_pengeluaran" id="tanggal_pengeluaran" value="{{ old('tanggal_pengeluaran', now()->toDateString()) }}" class="form-control"></div>
    <div><label for="kategori" class="form-label">Kategori</label><input required maxlength="100" name="kategori" id="kategori" value="{{ old('kategori') }}" class="form-control" placeholder="Contoh: listrik, transportasi, ATK"></div>
    <div><label for="keterangan" class="form-label">Keterangan</label><input required maxlength="255" name="keterangan" id="keterangan" value="{{ old('keterangan') }}" class="form-control"></div>
    <div><label for="nominal" class="form-label">Nominal (Rp)</label><input required type="number" min="1" step="0.01" name="nominal" id="nominal" value="{{ old('nominal') }}" class="form-control"></div>
</div><div class="card-footer flex justify-end gap-2"><a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary rounded-xl">Batal</a><button type="submit" class="btn bumkam-green-action rounded-xl">Simpan Pengeluaran</button></div></form></div>
@endsection
