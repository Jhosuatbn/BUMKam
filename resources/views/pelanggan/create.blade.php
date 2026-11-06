@extends('layouts.sneat-dashboard')

@section('title', 'Tambah Pelanggan')

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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pelanggan-form-heading">
            <h2 id="pelanggan-form-heading" class="mb-4 text-base font-semibold text-slate-900">Informasi Pelanggan</h2>

            <form action="{{ route('pelanggan.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 md:grid-cols-2">

                    <div>
                        <label for="nik" class="block text-sm font-semibold text-slate-700">NIK</label>
                        <input id="nik" type="text" name="nik" value="{{ old('nik') }}" required inputmode="numeric" minlength="16" maxlength="16" pattern="[0-9]{16}" aria-describedby="nik_hint" oninput="this.value = this.value.replace(/\D/g, '')" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('nik') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="nik_hint" class="mt-1.5 text-xs text-slate-500">Hanya angka, tepat 16 digit.</p>
                        @error('nik')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_pelanggan" class="block text-sm font-semibold text-slate-700">Nama Pelanggan</label>
                        <input id="nama_pelanggan" type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" required maxlength="100" autocomplete="name" aria-describedby="nama_pelanggan_hint" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('nama_pelanggan') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="nama_pelanggan_hint" class="mt-1.5 text-xs text-slate-500">Harus berupa teks, tidak boleh hanya angka.</p>
                        @error('nama_pelanggan')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_telepon" class="block text-sm font-semibold text-slate-700">No. Telepon</label>
                        <input id="no_telepon" type="text" name="no_telepon" value="{{ old('no_telepon') }}" required inputmode="numeric" minlength="10" maxlength="13" pattern="[0-9]{10,13}" autocomplete="tel" placeholder="08xxxxxxxxxx" aria-describedby="no_telepon_hint" oninput="this.value = this.value.replace(/\D/g, '')" class="mt-2 block min-h-11 w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('no_telepon') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">
                        <p id="no_telepon_hint" class="mt-1.5 text-xs text-slate-500">Hanya angka, 10 sampai 13 digit.</p>
                        @error('no_telepon')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-semibold text-slate-700">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="4" required maxlength="255" autocomplete="street-address" class="mt-2 block w-full rounded-xl border bg-white px-3.5 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 @error('alamat') border-rose-400 focus:border-rose-600 focus:ring-rose-600/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-600/20 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label for="wa_reminder_opt_in" class="flex items-start gap-3 text-sm font-medium text-slate-800">
                        <input id="wa_reminder_opt_in" type="checkbox" name="wa_reminder_opt_in" value="1" @checked(old('wa_reminder_opt_in')) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-700">
                        <span>Pelanggan menyetujui menerima pengingat tagihan melalui WhatsApp.</span>
                    </label>
                    <p class="mt-2 pl-7 text-xs text-slate-500">Centang hanya setelah persetujuan pelanggan diperoleh. Pengingat otomatis dinonaktifkan bila tidak dicentang.</p>
                </div>

                <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
                    <a href="{{ route('pelanggan.index') }}" class="bumkam-cancel-button inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Batal</a>
                    <button type="submit" data-no-auto-icon class="pelanggan-save-button inline-flex min-h-11 items-center justify-center rounded-2xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan</button>
                </div>
            </form>
        </section>
    </div>

@endsection
