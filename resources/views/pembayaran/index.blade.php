@extends('layouts.sneat-dashboard')

@section('title', 'Pembayaran')

@section('content')
    <div class="pembayaran-page space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pembayaran-list-heading">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                <div>
                    <h2 id="pembayaran-list-heading" class="text-base font-semibold text-slate-900">Daftar Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-500">Rincian pembayaran dan saldo setiap transaksi.</p>
                </div>
                @if (auth()->user()?->peran !== 'Pimpinan')
                    <a href="{{ route('pembayaran.create') }}" style="margin-top: 14px !important" class="inline-flex min-h-11 shrink-0 self-start items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"><i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Pembayaran</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="payment-list-table min-w-[68rem] divide-y divide-slate-200 text-left text-sm">
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
                                <td class="px-4 py-3.5 text-center sm:px-6">
                                    <div class="inline-flex items-center justify-center gap-3">
                                        <a href="{{ route('pembayaran.show', $item->id_pembayaran) }}" class="text-blue-700 transition hover:text-blue-800" title="Lihat detail {{ $item->id_pembayaran }}" aria-label="Lihat detail {{ $item->id_pembayaran }}">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z"/><circle cx="12" cy="12" r="2.5" stroke-width="2"/></svg>
                                        </a>
                                        @if ($item->metode_pembayaran === 'online' && $item->status_gateway === 'pending' && $item->snap_token)
                                            <a href="{{ route('pembayaran.online', $item->id_pembayaran) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Lanjut Bayar</a>
                                        @endif
                                        @if ($item->metode_pembayaran !== 'online')
                                            <a href="{{ route('pembayaran.edit', $item->id_pembayaran) }}" class="text-amber-600 transition hover:text-amber-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700" title="Edit pembayaran {{ $item->id_pembayaran }}" aria-label="Edit pembayaran {{ $item->id_pembayaran }}">
                                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 0 1 2.828 2.828L11.828 15H9v-2.828z"/></svg>
                                            </a>
                                        @endif
                                        @if ($item->metode_pembayaran !== 'online' || ! in_array($item->status_gateway, ['pending', 'settlement', 'refund', 'partial_refund'], true))
                                            <form action="{{ route('pembayaran.destroy', $item->id_pembayaran) }}" method="POST" data-confirm-delete="Yakin ingin menghapus catatan pembayaran ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 transition hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-700" title="Hapus pembayaran {{ $item->id_pembayaran }}" aria-label="Hapus pembayaran {{ $item->id_pembayaran }}">
                                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                                </button>
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
