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

            {{-- enctype WAJIB ada agar file bisa diunggah --}}
            <form action="{{ route('pelanggan.store') }}" method="POST" enctype="multipart/form-data" class="mt-6">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">

                    {{-- ID dibuat otomatis oleh sistem; hanya ditampilkan, tidak dikirim lewat form --}}
                    <div>
                        <label for="id_pelanggan" class="block text-sm font-semibold text-slate-700">ID Pelanggan</label>
                        <input id="id_pelanggan" type="text" value="{{ $nextId }}" readonly tabindex="-1" aria-describedby="id_pelanggan_hint" class="mt-2 block min-h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-500">
                        <p id="id_pelanggan_hint" class="mt-1.5 text-xs text-slate-500">Dibuat otomatis saat data disimpan.</p>
                    </div>

                    <div>
                        <label for="no_telepon" class="block text-sm font-semibold text-slate-700">No. Telepon</label>
                        <input id="no_telepon" type="text" name="no_telepon" value="{{ old('no_telepon') }}" required inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}" autocomplete="tel" placeholder="08xxxxxxxxxx" aria-describedby="no_telepon_hint" oninput="this.value = this.value.replace(/\D/g, '')" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('no_telepon') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="no_telepon_hint" class="mt-1.5 text-xs text-slate-500">Hanya angka, 10 sampai 13 digit.</p>
                        @error('no_telepon')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="nama_pelanggan" class="block text-sm font-semibold text-slate-700">Nama Pelanggan</label>
                        <input id="nama_pelanggan" type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" required maxlength="100" autocomplete="name" aria-describedby="nama_pelanggan_hint" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('nama_pelanggan') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="nama_pelanggan_hint" class="mt-1.5 text-xs text-slate-500">Harus berupa teks, tidak boleh hanya angka.</p>
                        @error('nama_pelanggan')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-semibold text-slate-700">NIK</label>
                        <input id="nik" type="text" name="nik" value="{{ old('nik') }}" required inputmode="numeric" maxlength="16" pattern="[0-9]{16}" aria-describedby="nik_hint" oninput="this.value = this.value.replace(/\D/g, '')" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('nik') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="nik_hint" class="mt-1.5 text-xs text-slate-500">Hanya angka, tepat 16 digit.</p>
                        @error('nik')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Upload foto KTP --}}
                    <div>
                        <label for="foto_ktp" class="block text-sm font-semibold text-slate-700">Foto KTP</label>
                        <input id="foto_ktp" type="file" name="foto_ktp" accept="image/jpeg,image/png,image/webp" aria-describedby="foto_ktp_hint" class="mt-2 block w-full cursor-pointer rounded-xl border bg-white text-sm text-slate-700 file:mr-4 file:min-h-11 file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-blue-800 hover:file:bg-blue-100 focus:outline-none focus:ring-2 @error('foto_ktp') border-rose-400 focus:ring-rose-600/20 @else border-slate-300 focus:ring-blue-600/20 @enderror">
                        <p id="foto_ktp_hint" class="mt-1.5 text-xs text-slate-500">Opsional. JPG, PNG, atau WEBP, maksimal 2 MB.</p>
                        <p id="foto_ktp_error" role="alert" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                        @error('foto_ktp')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pratinjau foto yang dipilih --}}
                    <div id="foto_ktp_preview_box" class="hidden md:col-span-2">
                        <p class="text-sm font-semibold text-slate-700">Pratinjau</p>
                        <img id="foto_ktp_preview" src="" alt="Pratinjau foto KTP yang dipilih" class="mt-2 max-h-56 rounded-xl border border-slate-200 object-contain">
                    </div>

                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-semibold text-slate-700">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="4" required maxlength="255" autocomplete="street-address" class="mt-2 block w-full rounded-xl border bg-white px-3.5 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('alamat') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan</button>
                    <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                </div>
            </form>
        </section>
    </div>

    <script>
        (function () {
            const input = document.getElementById('foto_ktp');
            const box = document.getElementById('foto_ktp_preview_box');
            const img = document.getElementById('foto_ktp_preview');
            const error = document.getElementById('foto_ktp_error');
            const maxBytes = 2 * 1024 * 1024; // 2 MB

            input.addEventListener('change', function () {
                const file = input.files[0];

                error.classList.add('hidden');
                error.textContent = '';

                if (!file) {
                    box.classList.add('hidden');
                    return;
                }

                if (file.size > maxBytes) {
                    input.value = '';
                    box.classList.add('hidden');
                    error.textContent = 'Ukuran foto maksimal 2 MB.';
                    error.classList.remove('hidden');
                    return;
                }

                img.src = URL.createObjectURL(file);
                box.classList.remove('hidden');
            });
        })();
    </script>
@endsection