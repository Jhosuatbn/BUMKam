@extends('layouts.sneat-dashboard')
@section('title', 'Ajukan Retur')
@section('content')
<section class="card">
    <div class="card-header"><h1 class="card-title mb-1">Ajukan Returan Pelanggan</h1><p class="mb-0 text-body-secondary">Hanya barang yang sudah diserahkan dapat diajukan. Barang rusak tidak otomatis masuk stok.</p></div>
    <form action="{{ route('retur.store') }}" method="POST">@csrf
        <div class="card-body">@include('retur._form-fields')</div>
        <div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('retur.index') }}" class="btn btn-outline-secondary rounded-xl">Batal</a><button class="btn bumkam-green-action rounded-xl" type="submit">Simpan Pengajuan</button></div>
    </form>
</section>
@endsection
