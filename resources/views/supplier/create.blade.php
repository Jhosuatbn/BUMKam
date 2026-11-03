@extends('layouts.sneat-dashboard')
@section('title','Tambah Supplier')
@section('content')<h1 class="h3 mb-4">Tambah Supplier</h1>@include('supplier._form',['action'=>route('supplier.store')])@endsection
