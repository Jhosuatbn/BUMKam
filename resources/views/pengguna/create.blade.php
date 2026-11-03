@extends('layouts.sneat-dashboard')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="space-y-6">
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pengguna-form-heading">
            <h2 id="pengguna-form-heading" class="text-base font-semibold text-slate-900">Informasi Akun</h2>
            <form action="{{ route('pengguna.store') }}" method="POST" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pengguna" class="block text-sm font-semibold text-slate-700">ID Pengguna</label>
                        <input id="id_pengguna" type="text" value="{{ $idPengguna }}" readonly class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600">
                    </div>
                    <div><label class="block text-sm font-semibold text-slate-700">Peran</label><div class="mt-2 flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-100 px-3.5 text-sm font-semibold text-slate-700">Staff</div></div>
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" type="password" name="password" minlength="10" required autocomplete="new-password" placeholder="Buat password awal (minimal 10 karakter)" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                    <div class="md:col-span-2">
                        <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                        <input id="nama_lengkap" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
                    <a href="{{ route('pengguna.index') }}" class="bumkam-cancel-button inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-900 shadow-sm transition hover:bg-slate-100">Batal</a>
                    <button data-no-auto-icon type="submit" class="bumkam-green-action inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition">Simpan Pengguna</button>
                </div>
            </form>
        </section>
    </div>
@endsection
