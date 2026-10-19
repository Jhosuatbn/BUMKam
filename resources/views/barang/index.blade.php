@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data Barang</h1>
                <p class="mt-2 text-sm text-slate-600">Informasi daftar barang dan harga BUMKam.</p>
            </div>

            {{-- Tombol Tambah hanya untuk Staff --}}
            @if (auth()->user()->peran !== 'Pimpinan')
                <a href="{{ route('barang.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                    Tambah Barang
                </a>
            @endif
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-list-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="barang-list-heading" class="text-base font-semibold text-slate-900">Daftar Barang</h2>
                <p class="mt-1 text-sm text-slate-500">Informasi barang yang tersedia.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">ID Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Kategori</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold sm:px-6">Harga</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($barang as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_barang }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->kategori }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>

                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        {{-- Pimpinan tetap boleh melihat detail --}}
                                        <a href="{{ route('barang.show', $item->id_barang) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">
                                            Detail
                                        </a>

                                        {{-- Edit dan Hapus hanya untuk Staff --}}
                                        @if (auth()->user()->peran !== 'Pimpinan')
                                            <a href="{{ route('barang.edit', $item->id_barang) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">
                                                Edit
                                            </a>

                                            <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100" onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
