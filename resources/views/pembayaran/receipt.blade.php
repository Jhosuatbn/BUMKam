@extends('layouts.sneat-dashboard')

@section('title', 'Kwitansi Pembayaran')

@section('content')
    @php
        $nominalRefund = (float) ($pembayaran->nominal_dikembalikan ?? 0);
        $nominalBersih = max(0, (float) $pembayaran->nominal - $nominalRefund);
    @endphp
    <div class="mx-auto max-w-3xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10 print:rounded-none print:border-0 print:p-0 print:shadow-none">
            <header class="flex items-center gap-4 border-b-2 border-emerald-700 pb-5">
                <img src="{{ asset('assets/img/bumkam-mark.png') }}" alt="Logo BUMKam Tuah Rezeki" class="h-16 w-16 object-contain">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">BUMKam Tuah Rezeki</h1>
                    <p class="mb-0 text-sm text-slate-600">Badan Usaha Milik Kampung · Rantau Bertuah</p>
                </div>
            </header>
            <div class="flex flex-wrap items-start justify-between gap-3 py-6">
                <div><h2 class="text-2xl font-bold text-slate-900">Kwitansi Pembayaran</h2><p class="mt-1 text-sm text-slate-500">Bukti penerimaan pembayaran yang tercatat di sistem.</p></div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800">{{ $pembayaran->status_gateway === 'refund' ? 'Refund penuh' : ($pembayaran->status_gateway === 'partial_refund' ? 'Refund sebagian' : 'Diterima') }}</span>
            </div>
            <dl class="grid grid-cols-1 gap-x-8 sm:grid-cols-2">
                @foreach ([['Nomor Kwitansi', $pembayaran->id_pembayaran], ['Nomor Pemesanan', $pembayaran->id_pemesanan], ['Nama Pelanggan', $pembayaran->pemesanan?->pelanggan?->nama_pelanggan ?? '-'], ['Tanggal Pembayaran', \Illuminate\Support\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d/m/Y')], ['Metode', $pembayaran->metode_pembayaran === 'online' ? 'Online · Midtrans' : 'Tunai / di tempat'], ['Status Gateway', ucfirst($pembayaran->status_gateway)]] as [$label, $value])
                    <div class="border-b border-slate-200 py-4"><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words font-semibold text-slate-900">{{ $value }}</dd></div>
                @endforeach
            </dl>
            <div class="mt-6 rounded-xl border border-slate-200 p-5">
                <div class="flex justify-between gap-4 py-2 text-sm"><span>Nominal transaksi</span><strong>Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong></div>
                @if ($nominalRefund > 0)<div class="flex justify-between gap-4 border-t border-slate-100 py-2 text-sm"><span>Refund tercatat</span><strong>− Rp {{ number_format($nominalRefund, 0, ',', '.') }}</strong></div>@endif
                <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-base"><span class="font-semibold">Dana bersih setelah refund</span><strong>Rp {{ number_format($nominalBersih, 0, ',', '.') }}</strong></div>
            </div>
            <p class="mt-6 text-xs text-slate-500">Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB. Kwitansi ini dibuat berdasarkan catatan transaksi aplikasi.</p>
            <div class="mt-6 flex justify-end gap-3 print:hidden">
                <a href="{{ route('pembayaran.show', $pembayaran) }}" class="bumkam-cancel-button inline-flex min-h-10 items-center rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-slate-50">Kembali</a>
                <button type="button" onclick="window.print()" class="bumkam-green-action inline-flex min-h-10 items-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition"><i class="icon-base bx bx-printer me-1" aria-hidden="true"></i>Cetak</button>
            </div>
        </section>
    </div>
    <style>@media print { @page { size: A4 portrait; margin: 18mm; } body { background: #fff !important; } }</style>
@endsection
