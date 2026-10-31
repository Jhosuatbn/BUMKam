@extends('layouts.sneat-dashboard')

@section('title', 'Barang')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-list-heading">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="barang-list-heading" class="text-base font-semibold text-slate-900">Daftar Barang</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi barang yang tersedia.</p>
                </div>
                
                <div class="flex w-full flex-col gap-2 sm:w-auto lg:w-[24rem]">
                    @if(auth()->user()?->peran !== 'Pimpinan')
                    <button onclick="openModal()" type="button" style="border-radius: 12px !important;" class="inline-flex min-h-11 shrink-0 self-end items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">                            <i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Barang
                        </button>
                    @endif
                    {{-- Form Pencarian Barang --}}
                    <form action="{{ route('barang.index') }}" method="GET" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID, nama, atau kategori..." class="block w-full min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        <button type="submit" style="border-radius: 12px !important;" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border-0 bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <circle cx="11" cy="11" r="7"/>
        <path d="m20 20-4-4"/>
    </svg>
    Cari
</button>
                        @if(request('search'))
                            <a href="{{ route('barang.index') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">ID Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Kategori</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Harga</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Stok</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($barang as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_barang }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-800 border border-slate-200">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    @if (is_null($item->stok))
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Belum diatur</span>
                                    @elseif ($item->stok === 0)
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Habis</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ number_format($item->stok, 0, ',', '.') }} unit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 sm:px-6 text-center">
                                    @if(auth()->user()?->peran !== 'Pimpinan')
                                        <div class="inline-flex items-center justify-center gap-3">
                                            {{-- Icon Edit (Pencil Square) - Besar & Tanpa Lingkaran Background --}}
                                            <a href="{{ route('barang.edit', $item->id_barang) }}" class="text-amber-600 hover:text-amber-700 transition" title="Edit">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            {{-- Icon Hapus (Trash) - Besar & Tanpa Lingkaran Background --}}
                                            <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST" class="inline-block" data-confirm-delete="Yakin ingin menghapus barang ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 transition" title="Hapus">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('barang.show', $item->id_barang) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-800 hover:underline" aria-label="Lihat detail {{ $item->nama_barang }}">
                                            Detail <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- POP-UP / MODAL TAMBAH BARANG --}}
    <div id="tambahBarangModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto">
        <div onclick="closeModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md transform rounded-2xl bg-slate-100 p-6 text-left shadow-2xl transition-all border border-slate-300 z-10">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-800">Tambah Barang</h3>
                    <button onclick="closeModal()" type="button" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        <p class="font-semibold">Terjadi kesalahan:</p>
                        <ul class="mt-1 list-inside list-disc space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('barang.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <input id="nama_barang" type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Nama Barang" required class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div>
                        <select id="kategori" name="kategori" required class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="" disabled selected>Kategori</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <input id="harga" type="text" inputmode="numeric" data-rupiah-input name="harga" value="{{ old('harga') }}" placeholder="Harga" required class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div>
                        <label for="stok" class="mb-1 block text-sm font-medium text-slate-700">Stok awal (opsional)</label>
                        <input id="stok" type="number" name="stok" value="{{ old('stok') }}" min="0" step="1" placeholder="Kosongkan jika belum diketahui" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <p class="text-xs italic text-slate-500">*ID Barang akan dibuat otomatis berdasarkan kategori pilihan.</p>

                    <div class="mt-6 flex justify-end gap-3 pt-2">
                        <button onclick="closeModal()" type="button" class="min-w-24 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm border border-slate-300 hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="min-w-24 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script JavaScript Modal --}}
    <script>
        function openModal() {
            document.getElementById('tambahBarangModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('tambahBarangModal').classList.add('hidden');
        }

        @if(session('open_create_modal'))
            openModal();
        @endif
    </script>
@endsection
