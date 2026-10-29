@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {{-- Icon Box (bi-box-seam) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/>
                    </svg>
                    Data Barang
                </h1>
                <p class="mt-2 text-sm text-slate-600">Kelola daftar barang dan harga BUMKam.</p>
            </div>
            
            {{-- Tombol Membuka Modal Pop-up --}}
            @if(auth()->check() && auth()->user()->role !== 'pimpinan')
                <button onclick="openModal()" type="button" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    {{-- Icon Plus --}}
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Barang
                </button>
            @endif
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-list-heading">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="barang-list-heading" class="text-base font-semibold text-slate-900">Daftar Barang</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi barang yang tersedia.</p>
                </div>
                
                {{-- Form Pencarian Barang --}}
                <form action="{{ route('barang.index') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID, Nama, atau Kategori..." class="block w-64 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('barang.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">ID Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Kategori</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Harga</th>
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
                                <td class="px-4 py-3.5 sm:px-6 text-center">
                                    @if(auth()->check() && auth()->user()->role !== 'pimpinan')
                                        <div class="inline-flex items-center justify-center gap-3">
                                            {{-- Icon Edit (Pencil Square) - Besar & Tanpa Lingkaran Background --}}
                                            <a href="{{ route('barang.edit', $item->id_barang) }}" class="text-amber-600 hover:text-amber-700 transition" title="Edit">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            {{-- Icon Hapus (Trash) - Besar & Tanpa Lingkaran Background --}}
                                            <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 transition" title="Hapus" onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data barang.</td></tr>
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
                        <input id="harga" type="number" name="harga" value="{{ old('harga') }}" min="0" placeholder="Harga" required class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <p class="text-xs italic text-slate-500">*ID Barang akan dibuat otomatis berdasarkan kategori pilihan.</p>

                    <div class="mt-6 flex justify-end gap-3 pt-2">
                        <button onclick="closeModal()" type="button" class="min-w-24 rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm border border-slate-300 hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="min-w-24 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 transition">Simpan</button>
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