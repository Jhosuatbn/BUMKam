@extends('layouts.sneat-dashboard')
@section('title','Kelola Supplier')
@section('content')<h1 class="h3 mb-4">Kelola Supplier</h1>@include('supplier._form',['action'=>route('supplier.update',$supplier),'method'=>'PUT'])@endsection
