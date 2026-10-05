@extends('layouts.app')

@section('title', 'Pemesanan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data Pemesanan</h1>
                <p class="mt-2 text-sm text-slate-600">Pantau pesanan, nilai, dan status pembayaran.</p>
            </div>
            <a href="{{ route('pemesanan.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Tambah Pemesanan</a>
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pemesanan-list-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="pemesanan-list-heading" class="text-base font-semibold text-slate-900">Daftar Pemesanan</h2>
                <p class="mt-1 text-sm text-slate-500">Rincian nilai dan skema setiap pemesanan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[60rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Tanggal</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Skema</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Total Harga</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Angsuran</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pemesanan as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_pemesanan }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $item->tanggal_pemesanan }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $item->skema_pembayaran }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->angsuran, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    @if ($item->status_pemesanan === 'Lunas')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">{{ $item->status_pemesanan }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-950">{{ $item->status_pemesanan }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        <a href="{{ route('pemesanan.show', $item->id_pemesanan) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Detail</a>
                                        <a href="{{ route('pemesanan.edit', $item->id_pemesanan) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Edit</a>
                                        <form action="{{ route('pemesanan.destroy', $item->id_pemesanan) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100" onclick="return confirm('Yakin ingin menghapus pemesanan ini?')">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data pemesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
