@extends('layouts.sneat-dashboard')

@section('title', 'Pembayaran Online')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Pembayaran Online</h1>
            <p class="mt-2 text-sm text-slate-600">Selesaikan angsuran melalui kanal pembayaran Midtrans.</p>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="icon-base bx bx-credit-card-front icon-lg" aria-hidden="true"></i></span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $pembayaran->pemesanan?->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Pemesanan {{ $pembayaran->id_pemesanan }} · Pembayaran {{ $pembayaran->id_pembayaran }}</p>
                </div>
            </div>

            <div class="mt-6 flex items-end justify-between gap-4 rounded-xl bg-slate-50 p-4">
                <span class="text-sm font-medium text-slate-600">Nominal angsuran</span>
                <strong class="text-xl font-bold tabular-nums text-slate-950">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong>
            </div>

            <div id="payment-message" role="status" class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Status pembayaran masih menunggu konfirmasi resmi dari Midtrans. Status tidak berubah hanya karena jendela pembayaran ditutup.
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button id="pay-online-button" type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    <i class="icon-base bx bx-lock-alt" aria-hidden="true"></i> Bayar Sekarang
                </button>
                <a href="{{ route('pembayaran.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali ke Pembayaran</a>
            </div>
        </section>
    </div>

    <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        const paymentButton = document.getElementById('pay-online-button');
        const paymentMessage = document.getElementById('payment-message');
        paymentButton?.addEventListener('click', () => {
            if (!window.snap) {
                paymentMessage.textContent = 'Halaman pembayaran belum dapat dimuat. Periksa koneksi internet lalu muat ulang halaman.';
                return;
            }

            window.snap.pay(@json($pembayaran->snap_token), {
                onSuccess: () => {
                    paymentMessage.textContent = 'Pembayaran berhasil dikirim. Menunggu konfirmasi server Midtrans...';
                },
                onPending: () => {
                    paymentMessage.textContent = 'Pembayaran masih menunggu penyelesaian. Ikuti instruksi pada halaman Midtrans.';
                },
                onError: () => {
                    paymentMessage.textContent = 'Pembayaran belum berhasil. Silakan coba kembali atau pilih bayar tunai di tempat.';
                },
                onClose: () => {
                    paymentMessage.textContent = 'Jendela ditutup. Jika pembayaran belum diselesaikan, Anda dapat melanjutkan lagi dari daftar pembayaran.';
                }
            });
        });
    </script>
@endsection
