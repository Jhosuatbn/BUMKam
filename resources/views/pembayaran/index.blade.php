@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data Pembayaran</h1>
                <p class="mt-2 text-sm text-slate-600">Pantau nominal, sisa, dan status pembayaran.</p>
            </div>
            <a href="{{ route('pembayaran.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Tambah Pembayaran</a>
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pembayaran-list-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="pembayaran-list-heading" class="text-base font-semibold text-slate-900">Daftar Pembayaran</h2>
                <p class="mt-1 text-sm text-slate-500">Rincian pembayaran dan saldo setiap transaksi.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[68rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Tanggal</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nominal</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Total</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Sisa</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pembayaran as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_pembayaran }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->pemesanan->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $item->tanggal_pembayaran }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    @if ($item->status_pembayaran === 'Lunas')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">{{ $item->status_pembayaran }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-950">{{ $item->status_pembayaran }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        <a href="{{ route('pembayaran.show', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Detail</a>
                                        <a href="{{ route('pembayaran.edit', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Edit</a>
                                        <form action="{{ route('pembayaran.destroy', $item->id_pembayaran) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100" onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
