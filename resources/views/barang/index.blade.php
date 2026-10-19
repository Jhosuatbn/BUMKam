
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Daftar Barang
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola dan lihat data barang BUMKam.
            </p>
        </div>

        @if (auth()->check() && auth()->user()->peran !== 'Pimpinan')
            <a href="{{ route('barang.create') }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                Tambah Barang
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            No
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            ID Barang
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Nama Barang
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Harga
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Stok
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($barang as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">
                                {{ $item->id_barang }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                {{ $item->nama_barang }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                {{ $item->stok }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                    <a href="{{ route('barang.show', $item->id_barang) }}"
                                       class="font-semibold text-blue-800 underline-offset-4 hover:underline">
                                        Detail
                                    </a>

                                    @if (auth()->check() && auth()->user()->peran !== 'Pimpinan')
                                        <a href="{{ route('barang.edit', $item->id_barang) }}"
                                           class="font-semibold text-blue-800 underline-offset-4 hover:underline">
                                            Edit
                                        </a>

                                        <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800"
                                                    onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                Belum ada data barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
