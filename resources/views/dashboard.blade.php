@extends('layouts.sneat-dashboard')

@section('title', auth()->user()->peran === 'Pimpinan' ? 'Dashboard Pimpinan' : 'Dashboard Admin')

@section('content')
    @if (auth()->user()->peran === 'Pimpinan')
        @include('dashboard-pimpinan')
    @else
        @include('dashboard-staff')
    @endif
@endsection
