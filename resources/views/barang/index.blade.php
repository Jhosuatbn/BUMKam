@extends('layouts.sneat-dashboard')

@section('title', 'Barang')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">{{ session('error') }}</div>
        @endif

        <section class="mobile-list-page overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-list-heading">
            <div class="mobile-list-toolbar flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div>
                    <h2 id="barang-list-heading" class="text-base font-semibold text-slate-900">Daftar Barang</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi barang yang tersedia.</p>
                </div>
                
                <div class="mobile-list-actions flex w-full flex-col gap-2 sm:w-auto lg:w-[24rem]">
                    @if(auth()->user()?->peran !== 'Pimpinan')
                    <button onclick="openModal()" type="button" style="border-radius: 12px !important;" class="mobile-list-add bumkam-green-action inline-flex min-h-11 shrink-0 self-end items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition">                            <i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Barang
                        </button>
                    @endif
                    {{-- Form Pencarian Barang --}}
                    <form action="{{ route('barang.index') }}" method="GET" class="mobile-list-search flex w-full min-w-0 flex-col gap-2 sm:flex-row">
                        <input type="text" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Cari ID, nama, atau kategori..." class="block min-h-11 w-full min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        <button type="submit" style="border-radius: 12px !important;" class="bumkam-green-action inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border-0 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition">
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

            @if ($errors->any() && old('nama_barang') === null && old('kategori') === null && old('harga') === null)
                <div role="alert" class="mx-5 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900 sm:mx-6" aria-live="assertive">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="mobile-card-table min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">ID Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Kategori</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Harga</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Stok</th>
                            @if (auth()->user()?->peran !== 'Pimpinan')
                                <th scope="col" class="px-4 py-3 font-semibold text-center sm:px-6">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($barang as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td data-label="ID Barang" class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_barang }}</td>
                                <td data-label="Nama Barang" class="px-4 py-3.5 sm:px-6">{{ $item->nama_barang }}</td>
                                <td data-label="Kategori" class="px-4 py-3.5 sm:px-6">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-800 border border-slate-200">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td data-label="Harga" class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                <td data-label="Stok" class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                    @if (is_null($item->stok))
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Belum diatur</span>
                                    @elseif ($item->stok_tersedia === 0)
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">0 tersedia</span>
                                        <span class="mt-1 block text-xs text-slate-500">Fisik {{ number_format($item->stok, 0, ',', '.') }} · dipesan {{ number_format($item->stok_dipesan, 0, ',', '.') }}</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ number_format($item->stok_tersedia, 0, ',', '.') }} tersedia</span>
                                        <span class="mt-1 block text-xs text-slate-500">Fisik {{ number_format($item->stok, 0, ',', '.') }} · dipesan {{ number_format($item->stok_dipesan, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                @if(auth()->user()?->peran !== 'Pimpinan')
                                    <td data-label="Aksi" class="px-4 py-3.5 sm:px-6 text-center">
                                        <div class="inline-flex items-center justify-center gap-3">
                                            <a href="{{ route('barang.edit', $item->id_barang) }}" class="btn btn-sm btn-outline-success" title="Kelola {{ $item->nama_barang }}"><i class="icon-base bx bx-edit me-1" aria-hidden="true"></i>Kelola</a>
                                            <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST" class="inline-block" data-confirm-delete="Yakin ingin menghapus barang ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus {{ $item->nama_barang }}"><i class="icon-base bx bx-trash me-1" aria-hidden="true"></i>Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="mobile-card-empty"><td colspan="{{ auth()->user()?->peran === 'Pimpinan' ? 5 : 6 }}" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                @if ($barang->hasPages())
                    {{-- Komponen Laravel yang sama persis dengan daftar Pelanggan. --}}
                    {{ $barang->onEachSide(3)->links() }}
                @else
                    {{-- Saat data belum cukup untuk halaman kedua, tampilkan versi nonaktif dengan gaya paginator Laravel. --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="m-0 text-sm text-gray-700 dark:text-gray-400">
                            Showing <span class="font-medium">{{ $barang->firstItem() ?? 0 }}</span>
                            to <span class="font-medium">{{ $barang->lastItem() ?? 0 }}</span>
                            of <span class="font-medium">{{ $barang->total() }}</span> results
                        </p>
                        <nav role="navigation" aria-label="Pagination Navigation">
                            <span class="inline-flex rtl:flex-row-reverse rounded-md shadow-sm">
                                <span aria-disabled="true" aria-label="Previous page">
                                    <span class="inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-md leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 0 1 0 1.414L9.414 10l3.293 3.293a1 1 0 0 1-1.414 1.414l-4-4a1 1 0 0 1 0-1.414l4-4a1 1 0 0 1 1.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </span>
                                <span aria-current="page"><span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">1</span></span>
                                <span aria-disabled="true"><span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed leading-5 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-500">2</span></span>
                                <span aria-disabled="true" aria-label="Next page">
                                    <span class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-r-md leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 0 1 0-1.414L10.586 10 7.293 6.707a1 1 0 0 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </span>
                            </span>
                        </nav>
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- POP-UP / MODAL TAMBAH BARANG --}}
    <div id="tambahBarangModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto">
        <div onclick="closeModal()" class="barang-modal-backdrop fixed inset-0 transition-opacity" aria-hidden="true"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="barang-create-dialog relative w-full max-w-xl transform rounded-2xl bg-slate-100 p-6 text-left shadow-2xl transition-all border border-slate-300 z-10">
                <div class="barang-create-dialog-heading mb-5 flex items-center justify-between">
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
                        <input id="nama_barang" type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Nama Barang" required class="barang-create-dialog-field block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div>
                        <select id="kategori" name="kategori" required class="barang-create-dialog-field block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="" disabled hidden @selected(old('kategori') === null)>Pilih jenis barang</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <input id="harga" type="text" inputmode="numeric" data-rupiah-input name="harga" value="{{ old('harga') }}" placeholder="Harga" required class="barang-create-dialog-field block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div>
                        <p class="text-sm text-slate-600">Stok awal barang adalah 0. Tambahkan persediaan melalui menu Barang Masuk.</p>
                    </div>

                    <p class="text-xs italic text-slate-500">*ID Barang akan dibuat otomatis berdasarkan kategori pilihan.</p>

                    <div class="mt-6 flex justify-end gap-3 pt-2">
                        <button onclick="closeModal()" data-no-auto-icon type="button" class="bumkam-cancel-button barang-modal-action min-w-24 rounded-2xl bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm border border-slate-300 hover:bg-slate-50 transition">Batal</button>
                        <button data-no-auto-icon type="submit" class="barang-modal-action bumkam-green-action min-w-24 rounded-2xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script JavaScript Modal --}}
    <script>
        function openModal() {
            const modal = document.getElementById('tambahBarangModal');
            // Keep the fixed backdrop at the document root so it covers the sidebar,
            // navbar, and page content as one continuous layer.
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('tambahBarangModal').classList.add('hidden');
        }

        @if(session('open_create_modal') || ($errors->any() && (old('nama_barang') !== null || old('kategori') !== null || old('harga') !== null)))
            openModal();
        @endif
    </script>
@endsection
