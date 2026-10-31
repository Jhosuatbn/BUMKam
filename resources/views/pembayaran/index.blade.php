@extends('layouts.sneat-dashboard')

@section('title', 'Pembayaran')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pembayaran-list-heading">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="pembayaran-list-heading" class="text-base font-semibold text-slate-900">Daftar Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-500">Rincian pembayaran dan saldo setiap transaksi.</p>
                </div>
                @if (auth()->user()?->peran !== 'Pimpinan')
                    <a href="{{ route('pembayaran.create') }}" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"><i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Pembayaran</a>
                @endif
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
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Metode / Bukti</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pembayaran as $item)
                            @php
                                $riwayatPembayaran = $item->pemesanan?->pembayaran?->where('status_gateway', 'settlement')->sortBy([['tanggal_pembayaran', 'asc'], ['id_pembayaran', 'asc']])->values() ?? collect();
                                $posisiAngsuran = $riwayatPembayaran->search(fn ($pembayaranRiwayat) => $pembayaranRiwayat->id_pembayaran === $item->id_pembayaran);
                                $nomorAngsuran = $posisiAngsuran === false ? $riwayatPembayaran->count() + 1 : $posisiAngsuran + 1;
                                $sisaTerkini = (float) ($riwayatPembayaran->last()?->sisa_pembayaran ?? (($item->pemesanan?->total_harga ?? 0) + ($item->pemesanan?->bunga ?? 0) - ($item->pemesanan?->dp ?? 0)));
                                $jumlahJatuhTempo = 0;
                                if ($item->pemesanan?->tanggal_pemesanan) {
                                    $tanggalPemesanan = \Illuminate\Support\Carbon::parse($item->pemesanan->tanggal_pemesanan);
                                    for ($bulan = 1; $bulan <= (int) $item->pemesanan->tenor; $bulan++) {
                                        if ($tanggalPemesanan->copy()->addMonthsNoOverflow($bulan)->startOfDay()->lte(now()->startOfDay())) $jumlahJatuhTempo++;
                                    }
                                }
                                $menunggak = $sisaTerkini > 0 && $jumlahJatuhTempo > $riwayatPembayaran->count();
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_pembayaran }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->pemesanan->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ $item->tanggal_pembayaran }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums sm:px-6">Rp {{ number_format($sisaTerkini, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    @if ($item->metode_pembayaran === 'online' && $item->status_gateway !== 'settlement')
                                        @php($labelGateway = ['pending' => 'Menunggu Pembayaran', 'expire' => 'Kedaluwarsa', 'cancel' => 'Dibatalkan', 'deny' => 'Ditolak', 'failure' => 'Gagal', 'refund' => 'Dikembalikan', 'partial_refund' => 'Rekonsiliasi'][ $item->status_gateway ] ?? 'Perlu Diperiksa')
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-900">{{ $labelGateway }}</span>
                                    @elseif ($item->status_pembayaran === 'Lunas')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">{{ $item->status_pembayaran }}</span>
                                    @elseif ($menunggak)
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-900">Menunggak</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-950">{{ $item->status_pembayaran }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <span class="mb-1 block text-xs font-semibold text-slate-700">{{ $item->metode_pembayaran === 'online' ? 'Online · Midtrans' : 'Tunai / di tempat' }}</span>
                                    <span class="block text-xs font-semibold text-slate-700">Angsuran {{ $nomorAngsuran }} dari {{ $item->pemesanan?->tenor ?? '-' }}</span>
                                    @if ($item->bukti_pembayaran)
                                        <a href="{{ asset('storage/' . $item->bukti_pembayaran) }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-blue-800 hover:underline">Lihat bukti</a>
                                    @else
                                        <span class="mt-1 block text-xs text-slate-400">Belum ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        <a href="{{ route('pembayaran.show', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Detail</a>
                                        @if ($item->metode_pembayaran === 'online' && $item->status_gateway === 'pending' && $item->snap_token)
                                            <a href="{{ route('pembayaran.online', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Lanjut Bayar</a>
                                        @endif
                                        @if ($item->metode_pembayaran !== 'online')
                                            <a href="{{ route('pembayaran.edit', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Edit</a>
                                        @endif
                                        @if ($item->metode_pembayaran !== 'online' || ! in_array($item->status_gateway, ['pending', 'settlement', 'refund', 'partial_refund'], true))
                                            <form action="{{ route('pembayaran.destroy', $item->id_pembayaran) }}" method="POST" data-confirm-delete="Yakin ingin menghapus catatan pembayaran ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100" title="Hapus"><svg class="mr-1 inline size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
