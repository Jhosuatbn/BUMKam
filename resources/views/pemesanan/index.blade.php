@extends('layouts.sneat-dashboard')

@section('title', 'Pemesanan')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pemesanan-list-heading">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="pemesanan-list-heading" class="text-base font-semibold text-slate-900">Daftar Pemesanan</h2>
                    <p class="mt-1 text-sm text-slate-500">Rincian nilai dan skema setiap pemesanan.</p>
                </div>
                @if (auth()->user()?->peran !== 'Pimpinan')
                    <a href="{{ route('pemesanan.create') }}" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"><i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Pemesanan</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Tanggal</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Skema</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Total Harga</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Angsuran</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Status</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-center font-semibold sm:px-6">Aksi</th>
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
                                <td class="px-4 py-3.5 text-center sm:px-6">
                                    <div class="inline-flex items-center justify-center gap-3">
                                        <a href="{{ route('pemesanan.edit', $item->id_pemesanan) }}" class="text-amber-600 transition hover:text-amber-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700" title="Edit pemesanan {{ $item->id_pemesanan }}" aria-label="Edit pemesanan {{ $item->id_pemesanan }}">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 0 1 2.828 2.828L11.828 15H9v-2.828z"/></svg>
                                        </a>
                                        <form action="{{ route('pemesanan.destroy', $item->id_pemesanan) }}" method="POST" class="inline-block" data-confirm-delete="Yakin ingin menghapus pemesanan ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 transition hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-700" title="Hapus pemesanan {{ $item->id_pemesanan }}" aria-label="Hapus pemesanan {{ $item->id_pemesanan }}">
                                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                            </button>
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
