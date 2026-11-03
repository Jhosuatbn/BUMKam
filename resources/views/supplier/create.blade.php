@extends('layouts.sneat-dashboard')
@section('title','Tambah Supplier')
@section('content')@include('supplier._form',['action'=>route('supplier.store'),'heading'=>'Tambah Supplier'])@endsection
