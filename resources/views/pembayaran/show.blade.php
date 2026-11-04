@extends('layouts.sneat-dashboard')

@section('title', 'Detail Pembayaran')

@section('content')
    <div class="pembayaran-page space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pembayaran-detail-heading">
            <h2 id="pembayaran-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">Informasi Pembayaran</h2>
            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pembayaran</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->id_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pemesanan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->id_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Pelanggan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pembayaran->pemesanan->pelanggan->nama_pelanggan ?? '-' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tanggal Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->tanggal_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Nominal</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Total Pembayaran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Sisa Pembayaran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->sisa_pembayaran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->status_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status Transaksi</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->status_gateway === 'partial_refund' ? 'Refund sebagian · alokasi terakhir dibalik otomatis' : ucfirst($pembayaran->status_gateway ?? 'settlement') }}</dd></div>
                @if ((float) $pembayaran->nominal_dikembalikan > 0)
                    <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Refund Midtrans tercatat</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pembayaran->nominal_dikembalikan, 0, ',', '.') }}</dd></div>
                    @foreach ($pembayaran->refundEvents as $refundEvent)
                        <div class="border-b border-slate-100 py-3"><dt class="text-xs font-medium text-slate-500">Peristiwa refund · {{ $refundEvent->diterima_pada?->locale('id')->translatedFormat('d M Y, H:i') }}</dt><dd class="mt-1 text-sm text-slate-800">{{ $refundEvent->refund_provider_id ? 'Referensi ' . $refundEvent->refund_provider_id . ' · ' : '' }}Perubahan Rp {{ number_format($refundEvent->nominal_refund, 0, ',', '.') }} · Kumulatif Rp {{ number_format($refundEvent->nominal_refund_kumulatif, 0, ',', '.') }}</dd></div>
                    @endforeach
                @endif
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Rincian Alokasi</dt><dd class="mt-1 text-sm text-slate-900">
                    @forelse ($pembayaran->alokasiDenda as $alokasi)
                        <span class="mb-1 block">Angsuran {{ $alokasi->nomor_angsuran }}: pokok Rp {{ number_format($alokasi->alokasi_angsuran, 0, ',', '.') }} · denda Rp {{ number_format($alokasi->alokasi_denda, 0, ',', '.') }}</span>
                    @empty
                        @if ($pembayaran->status_gateway === 'settlement')Pembayaran lama: seluruh nominal dihitung sebagai pokok angsuran.@elseif ($pembayaran->status_gateway === 'reversed')Histori alokasi tetap disimpan; transaksi ini sudah dibalikkan.@elseif ($pembayaran->status_gateway === 'partial_refund')Sisa dana yang diterima tetap dihitung setelah alokasi terakhir dibalik otomatis.@else Belum dialokasikan sebelum pembayaran settlement.@endif
                    @endforelse
                </dd></div>
                @if ($pembayaran->status_gateway === 'partial_refund' || $pembayaran->status_gateway === 'refund')
                    <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Pembalikan alokasi refund</dt><dd class="mt-1 space-y-1 text-sm text-slate-900">
                        @forelse ($pembayaran->pengembalianAlokasi as $pengembalian)
                            <span class="block">Angsuran {{ $pengembalian->alokasi?->nomor_angsuran ?? '-' }}: pokok Rp {{ number_format($pengembalian->nominal_pokok_dibalik, 0, ',', '.') }} · denda Rp {{ number_format($pengembalian->nominal_denda_dibalik, 0, ',', '.') }}</span>
                        @empty <span>Belum ada alokasi yang dibalik.</span> @endforelse
                    </dd></div>
                @endif
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Metode Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pembayaran->metode_pembayaran === 'online' ? 'Online · Midtrans' : 'Tunai / di tempat' }}</dd></div>
                @if ($pembayaran->reversal)
                    <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Histori Reversal</dt><dd class="mt-1 text-sm text-slate-900">Rp {{ number_format($pembayaran->reversal->nominal, 0, ',', '.') }} · {{ $pembayaran->reversal->alasan }} · oleh {{ $pembayaran->reversal->pengguna?->nama_lengkap ?? 'Pimpinan' }} · {{ $pembayaran->reversal->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</dd></div>
                @endif
                <div class="border-b border-slate-100 py-4">
                    <dt class="text-sm font-medium text-slate-500">Bukti Pembayaran</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        @if ($pembayaran->bukti_pembayaran)
                            <a href="{{ route('pembayaran.bukti', $pembayaran) }}" target="_blank" rel="noopener" class="text-blue-800 underline">Buka bukti pembayaran</a>
                        @else
                            Belum diunggah
                        @endif
                    </dd>
                </div>
            </dl>
            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                @if (in_array($pembayaran->status_gateway, ['settlement', 'partial_refund', 'refund'], true))
                    <a href="{{ route('pembayaran.receipt', $pembayaran) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-emerald-700 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-50">Cetak Kwitansi</a>
                @endif
                @if ($pembayaran->metode_pembayaran === 'online' && $pembayaran->status_gateway === 'pending' && $pembayaran->snap_token)
                    <a href="{{ route('pembayaran.online', $pembayaran->id_pembayaran) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">Lanjut Bayar</a>
                @endif
                @if (auth()->user()?->peran === 'Pimpinan' && $pembayaran->metode_pembayaran === 'tunai' && $pembayaran->status_gateway === 'settlement')
                    <form action="{{ route('pembayaran.reverse', $pembayaran) }}" method="POST" class="flex w-full flex-col gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-end" onsubmit="return confirm('Catat reversal pembayaran tunai ini? Histori akan tetap tersimpan.')">
                        @csrf
                        <div class="min-w-0 flex-1"><label for="alasan-reversal" class="mb-1 block text-sm font-semibold text-slate-700">Alasan reversal</label><textarea id="alasan-reversal" name="alasan" required maxlength="2000" rows="2" class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Jelaskan alasan koreksi"></textarea></div>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Reversal Pembayaran</button>
                    </form>
                @endif
                @if (auth()->user()?->peran === 'Pimpinan' && $pembayaran->metode_pembayaran === 'online' && $pembayaran->status_gateway !== 'reversed')
                    <form action="{{ route('pembayaran.sync-midtrans', $pembayaran) }}" method="POST" class="border-t border-slate-200 pt-4">
                        @csrf
                        <button type="submit" class="bumkam-green-action inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white">Sinkronkan Status Midtrans</button>
                    </form>
                @endif
                <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-black shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Kembali</a>
            </div>
        </section>
    </div>
@endsection
