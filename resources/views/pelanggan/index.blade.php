@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data Pelanggan</h1>
                <p class="mt-2 text-sm text-slate-600">Kelola informasi pelanggan BUMKam.</p>
            </div>
            <a href="{{ route('pelanggan.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Tambah Pelanggan</a>
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">{{ session('error') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pelanggan-list-heading">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:px-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 id="pelanggan-list-heading" class="text-base font-semibold text-slate-900">Daftar Pelanggan</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi kontak pelanggan yang tersimpan
                        @if ($pelanggan->total() > 0)
                            ({{ $pelanggan->total() }} data).
                        @else
                            .
                        @endif
                    </p>
                </div>

                {{-- Pencarian pelanggan --}}
                <form action="{{ route('pelanggan.index') }}" method="GET" role="search" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
                    <label for="q" class="sr-only">Cari pelanggan</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, ID, NIK, atau no. telepon..." class="block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Cari</button>
                        @if (request()->filled('q'))
                            <a href="{{ route('pelanggan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[58rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">NIK</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">No. Telepon</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Alamat</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pelanggan as $p)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $p->id_pelanggan }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $p->nama_pelanggan }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">{{ $p->nik }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $p->no_telepon }}</td>
                                <td class="max-w-xs px-4 py-3.5 sm:px-6">{{ $p->alamat }}</td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Detail</a>
                                        <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Edit</a>
                                        <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-700" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                    @if (request()->filled('q'))
                                        Tidak ada pelanggan yang cocok dengan "{{ request('q') }}".
                                    @else
                                        Belum ada data pelanggan.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi halaman --}}
            @if ($pelanggan->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection