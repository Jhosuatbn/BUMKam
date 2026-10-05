@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Pelanggan</h1>
                <p class="mt-2 text-sm text-slate-600">Lengkapi identitas dan informasi kontak pelanggan.</p>
            </div>
            <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
        </header>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pelanggan-form-heading">
            <h2 id="pelanggan-form-heading" class="text-base font-semibold text-slate-900">Informasi Pelanggan</h2>
            <form action="{{ route('pelanggan.store') }}" method="POST" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pelanggan" class="block text-sm font-semibold text-slate-700">ID Pelanggan</label>
                        <input id="id_pelanggan" type="text" name="id_pelanggan" value="{{ old('id_pelanggan') }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div>
                        <label for="no_telepon" class="block text-sm font-semibold text-slate-700">No. Telepon</label>
                        <input id="no_telepon" type="text" name="no_telepon" value="{{ old('no_telepon') }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="nama_pelanggan" class="block text-sm font-semibold text-slate-700">Nama Pelanggan</label>
                        <input id="nama_pelanggan" type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div>
                        <label for="nik" class="block text-sm font-semibold text-slate-700">NIK</label>
                        <input id="nik" type="text" name="nik" value="{{ old('nik') }}" required class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-semibold text-slate-700">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="4" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">{{ old('alamat') }}</textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan</button>
                    <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
