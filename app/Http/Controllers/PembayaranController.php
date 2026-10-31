<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayaran = Pembayaran::with([
            'pemesanan.pelanggan',
            'pemesanan.pembayaran',
        ])->get();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create()
    {
        $pemesanan = Pemesanan::with('pelanggan')
            ->where('status_pemesanan', '!=', 'Lunas')
            ->whereDoesntHave('pembayaran', fn ($query) => $query->where('status_gateway', 'pending'))
            ->get();
        $idPembayaranPreview = $this->idPembayaranBerikutnya();

        return view('pembayaran.create', compact('pemesanan', 'idPembayaranPreview'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
            'tanggal_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,online',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        if ($validated['metode_pembayaran'] === 'online') {
            return $this->buatPembayaranOnline($validated);
        }

        $bukti = $request->file('bukti_pembayaran')?->store('bukti-pembayaran', 'public');

        DB::transaction(function () use ($validated, $bukti) {
            $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                ->lockForUpdate()
                ->firstOrFail();
            $this->pastikanDapatDibayar($pemesanan);
            $nominal = min(
                round((float) $pemesanan->angsuran, 2),
                $this->sisaTagihan($pemesanan)
            );

            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'id_pemesanan' => 'Pemesanan ini belum memiliki nominal angsuran yang valid.',
                ]);
            }

            $ringkasan = $this->ringkasanPembayaran($pemesanan, $nominal);

            Pembayaran::create([
                'id_pembayaran' => $this->idPembayaranBerikutnya(),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'nominal' => $nominal,
                'total_pembayaran' => $ringkasan['total'],
                'sisa_pembayaran' => $ringkasan['sisa'],
                'status_pembayaran' => $ringkasan['status'],
                'metode_pembayaran' => 'tunai',
                'status_gateway' => 'settlement',
                'bukti_pembayaran' => $bukti,
            ]);

            $pemesanan->update([
                'status_pemesanan' => $ringkasan['status'],
            ]);
            $this->sinkronkanPembayaran($pemesanan);
        });

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran tunai berhasil dicatat.');
    }

    public function lanjutOnline(Pembayaran $pembayaran)
    {
        abort_unless($pembayaran->metode_pembayaran === 'online', 404);

        if (! in_array($pembayaran->status_gateway, ['pending'], true) || ! $pembayaran->snap_token) {
            return redirect()->route('pembayaran.show', $pembayaran->id_pembayaran);
        }

        return view('pembayaran.online', [
            'pembayaran' => $pembayaran->load('pemesanan.pelanggan'),
            'clientKey' => config('services.midtrans.client_key'),
            'isProduction' => config('services.midtrans.is_production'),
        ]);
    }

    public function notifikasiMidtrans(Request $request)
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signature = (string) $request->input('signature_key');

        if ($serverKey === '' || $orderId === '' || $signature === '') {
            return response()->json(['message' => 'Notifikasi tidak valid.'], 403);
        }

        $signatureExpected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if (! hash_equals($signatureExpected, $signature)) {
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        // Midtrans's "Test notification URL" uses a synthetic order ID that is
        // intentionally not present in our payments table. Acknowledge the test
        // only after verifying its signature, without changing payment records.
        if (str_starts_with($orderId, 'payment_notif_test_')) {
            return response()->json(['message' => 'Notifikasi tes diterima.']);
        }

        $pembayaran = Pembayaran::whereKey($orderId)->first();
        if (! $pembayaran || $pembayaran->metode_pembayaran !== 'online') {
            return response()->json(['message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        $grossAmountTerverifikasi = number_format((float) $grossAmount, 2, '.', '');
        $nominalTersimpan = number_format((float) $pembayaran->nominal, 2, '.', '');
        if ($grossAmountTerverifikasi !== $nominalTersimpan) {
            return response()->json(['message' => 'Nominal notifikasi tidak cocok.'], 403);
        }

        $statusTransaksi = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status', 'accept');
        $statusGateway = match ($statusTransaksi) {
            'settlement' => 'settlement',
            'capture' => $fraudStatus === 'accept' ? 'settlement' : 'deny',
            'pending' => 'pending',
            'expire' => 'expire',
            'cancel' => 'cancel',
            'deny' => 'deny',
            'failure' => 'failure',
            'refund' => 'refund',
            'partial_refund' => 'partial_refund',
            default => null,
        };

        if ($statusGateway === null) {
            return response()->json(['message' => 'Status transaksi tidak dikenali.'], 422);
        }
        if ($statusGateway === 'partial_refund') {
            // Pengembalian sebagian perlu rekonsiliasi nominal tersendiri; jangan ubah saldo otomatis.
            return response()->json(['message' => 'Notifikasi diterima untuk rekonsiliasi.']);
        }

        DB::transaction(function () use ($pembayaran, $statusGateway) {
            $pemesanan = Pemesanan::whereKey($pembayaran->id_pemesanan)
                ->lockForUpdate()
                ->firstOrFail();
            $pembayaranTerkunci = Pembayaran::whereKey($pembayaran->id_pembayaran)
                ->lockForUpdate()
                ->firstOrFail();

            // Abaikan notifikasi lama yang datang setelah transaksi berhasil.
            if ($pembayaranTerkunci->status_gateway === 'settlement'
                && ! in_array($statusGateway, ['refund', 'partial_refund'], true)) {
                return;
            }
            if ($pembayaranTerkunci->status_gateway === 'refund' && $statusGateway !== 'refund') {
                return;
            }

            $pembayaranTerkunci->update([
                'status_gateway' => $statusGateway,
                'tanggal_pembayaran' => $statusGateway === 'settlement'
                    ? now()->toDateString()
                    : $pembayaranTerkunci->tanggal_pembayaran,
            ]);
            $this->sinkronkanPembayaran($pemesanan);
        });

        return response()->json(['message' => 'Notifikasi diterima.']);
    }

    private function buatPembayaranOnline(array $validated)
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $clientKey = (string) config('services.midtrans.client_key');
        if ($serverKey === '' || $clientKey === '') {
            return back()->withInput()->withErrors([
                'metode_pembayaran' => 'Pembayaran online belum siap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.',
            ]);
        }

        $pembayaran = DB::transaction(function () use ($validated) {
            $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                ->lockForUpdate()
                ->firstOrFail();
            $this->pastikanDapatDibayar($pemesanan);

            // Midtrans menerima nominal Rupiah bulat; nominal angsuran di sini dibatasi sisa tagihan.
            $nominal = min(
                (int) round((float) $pemesanan->angsuran),
                (int) round($this->sisaTagihan($pemesanan))
            );
            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'id_pemesanan' => 'Sisa tagihan pada pemesanan ini sudah tidak dapat dibayar.',
                ]);
            }

            $ringkasan = $this->ringkasanPembayaran($pemesanan, 0);

            return Pembayaran::create([
                'id_pembayaran' => $this->idPembayaranBerikutnya(),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'nominal' => $nominal,
                'total_pembayaran' => $ringkasan['total'],
                'sisa_pembayaran' => $ringkasan['sisa'],
                'status_pembayaran' => 'Belum Lunas',
                'metode_pembayaran' => 'online',
                'status_gateway' => 'pending',
                'bukti_pembayaran' => null,
            ]);
        });

        $pemesanan = $pembayaran->pemesanan()->with('pelanggan')->firstOrFail();
        $isProduction = (bool) config('services.midtrans.is_production');
        $endpoint = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(20)
                ->post($endpoint, [
                    'transaction_details' => [
                        'order_id' => $pembayaran->id_pembayaran,
                        'gross_amount' => (int) $pembayaran->nominal,
                    ],
                    'item_details' => [[
                        'id' => $pemesanan->id_pemesanan,
                        'price' => (int) $pembayaran->nominal,
                        'quantity' => 1,
                        'name' => 'Angsuran ' . $pemesanan->id_pemesanan,
                    ]],
                    'customer_details' => [
                        'first_name' => $pemesanan->pelanggan?->nama_pelanggan ?? 'Pelanggan BUMKam',
                        'phone' => $pemesanan->pelanggan?->no_telepon,
                    ],
                    // Override the Midtrans dashboard example URL so customers return to BUMKam.
                    // The redirect is for navigation only; payment status still comes from webhook.
                    'callbacks' => [
                        'finish' => route('pembayaran.index'),
                        'error' => route('pembayaran.index'),
                    ],
                ]);
        } catch (\Throwable $exception) {
            $pembayaran->update(['status_gateway' => 'failure']);

            return redirect()->route('pembayaran.index')->withErrors([
                'pembayaran' => 'Tidak dapat menghubungi Midtrans. Periksa koneksi dan kunci Sandbox Anda.',
            ]);
        }

        $snapToken = $response->json('token');
        $redirectUrl = $response->json('redirect_url');
        if (! $response->successful() || ! $snapToken || ! $redirectUrl) {
            $pembayaran->update(['status_gateway' => 'failure']);

            return redirect()->route('pembayaran.index')->withErrors([
                'pembayaran' => 'Midtrans belum dapat membuat transaksi. Periksa kunci Sandbox dan konfigurasi akun.',
            ]);
        }

        $pembayaran->update([
            'snap_token' => $snapToken,
            'gateway_redirect_url' => $redirectUrl,
        ]);

        return view('pembayaran.online', [
            'pembayaran' => $pembayaran->load('pemesanan.pelanggan'),
            'clientKey' => $clientKey,
            'isProduction' => $isProduction,
        ]);
    }

    private function pastikanDapatDibayar(Pemesanan $pemesanan): void
    {
        if ($pemesanan->status_pemesanan === 'Lunas' || $this->sisaTagihan($pemesanan) <= 0) {
            throw ValidationException::withMessages([
                'id_pemesanan' => 'Pemesanan ini sudah lunas.',
            ]);
        }

        $adaPembayaranMenunggu = Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->where('metode_pembayaran', 'online')
            ->where('status_gateway', 'pending')
            ->exists();
        if ($adaPembayaranMenunggu) {
            throw ValidationException::withMessages([
                'id_pemesanan' => 'Pemesanan ini sudah memiliki pembayaran online yang menunggu. Selesaikan atau tunggu transaksi tersebut terlebih dahulu.',
            ]);
        }
    }

    private function sisaTagihan(Pemesanan $pemesanan): float
    {
        $tagihan = max(0, (float) $pemesanan->total_harga + (float) $pemesanan->bunga - (float) $pemesanan->dp);
        $terbayar = (float) Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->where('status_gateway', 'settlement')
            ->sum('nominal');

        return round(max(0, $tagihan - $terbayar), 2);
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load('pemesanan.pelanggan');

        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(Pembayaran $pembayaran)
    {
        abort_if($pembayaran->metode_pembayaran === 'online', 403, 'Riwayat pembayaran online tidak dapat diedit.');
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pembayaran.edit', compact('pembayaran', 'pemesanan'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        abort_if($pembayaran->metode_pembayaran === 'online', 403, 'Riwayat pembayaran online tidak dapat diedit.');
        $validated = $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
            'tanggal_pembayaran' => 'required|date',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);
        $buktiLama = $pembayaran->bukti_pembayaran;
        $buktiBaru = $request->file('bukti_pembayaran')?->store('bukti-pembayaran', 'public');
        $idPemesananSebelumnya = $pembayaran->id_pemesanan;

        DB::transaction(function () use ($validated, $pembayaran, $idPemesananSebelumnya, $buktiBaru) {
            $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                ->lockForUpdate()
                ->firstOrFail();
            $nominal = round((float) $pemesanan->angsuran, 2);

            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'id_pemesanan' => 'Pemesanan ini belum memiliki nominal angsuran yang valid.',
                ]);
            }

            $ringkasan = $this->ringkasanPembayaran(
                $pemesanan,
                $nominal,
                $pembayaran->id_pembayaran
            );

            $pembayaran->update([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'nominal' => $nominal,
                'total_pembayaran' => $ringkasan['total'],
                'sisa_pembayaran' => $ringkasan['sisa'],
                'status_pembayaran' => $ringkasan['status'],
                'bukti_pembayaran' => $buktiBaru ?? $pembayaran->bukti_pembayaran,
            ]);

            $pemesanan->update([
                'status_pemesanan' => $ringkasan['status'],
            ]);

            if ($idPemesananSebelumnya !== $pemesanan->id_pemesanan) {
                $pemesananLama = Pemesanan::whereKey($idPemesananSebelumnya)
                    ->lockForUpdate()
                    ->firstOrFail();
                $this->perbaruiStatusPemesanan($pemesananLama);
                $this->sinkronkanPembayaran($pemesananLama);
            }
            $this->sinkronkanPembayaran($pemesanan);
        });

        if ($buktiBaru && $buktiLama) {
            Storage::disk('public')->delete($buktiLama);
        }

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        if ($pembayaran->metode_pembayaran === 'online'
            && in_array($pembayaran->status_gateway, ['pending', 'settlement', 'refund', 'partial_refund'], true)) {
            throw ValidationException::withMessages([
                'pembayaran' => 'Transaksi online yang masih menunggu atau sudah berhasil tidak dapat dihapus.',
            ]);
        }

        $bukti = $pembayaran->bukti_pembayaran;
        DB::transaction(function () use ($pembayaran) {
            $pemesanan = Pemesanan::whereKey($pembayaran->id_pemesanan)
                ->lockForUpdate()
                ->firstOrFail();

            $pembayaran->delete();
            $this->perbaruiStatusPemesanan($pemesanan);
            $this->sinkronkanPembayaran($pemesanan);
        });
        if ($bukti) {
            Storage::disk('public')->delete($bukti);
        }

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    private function idPembayaranBerikutnya(): string
    {
        $prefix = 'BYR-'.now()->format('dmy').'-';
        $ids = Pembayaran::where('id_pembayaran', 'like', $prefix.'%')
            ->pluck('id_pembayaran');
        $nomorTerakhir = 0;

        foreach ($ids as $id) {
            $bagianNomor = substr($id, strlen($prefix));

            if (ctype_digit($bagianNomor)) {
                $nomorTerakhir = max($nomorTerakhir, (int) $bagianNomor);
            }
        }

        do {
            $nomorTerakhir++;
            $idPembayaran = $prefix.str_pad(
                (string) $nomorTerakhir,
                2,
                '0',
                STR_PAD_LEFT
            );
        } while (Pembayaran::whereKey($idPembayaran)->exists());

        return $idPembayaran;
    }

    private function ringkasanPembayaran(
        Pemesanan $pemesanan,
        float $nominalTambahan,
        ?string $idPembayaranDikecualikan = null
    ): array {
        $query = Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->where('status_gateway', 'settlement');

        if ($idPembayaranDikecualikan !== null) {
            $query->where('id_pembayaran', '!=', $idPembayaranDikecualikan);
        }

        $total = round((float) $query->sum('nominal') + $nominalTambahan, 2);
        $tagihanAngsuran = max(
            0,
            round(
                (float) $pemesanan->total_harga
                + (float) $pemesanan->bunga
                - (float) $pemesanan->dp,
                2
            )
        );
        $sisa = round(max(0, $tagihanAngsuran - $total), 2);

        return [
            'total' => $total,
            'sisa' => $sisa,
            'status' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
        ];
    }

    private function perbaruiStatusPemesanan(Pemesanan $pemesanan): void
    {
        $ringkasan = $this->ringkasanPembayaran($pemesanan, 0);
        $pemesanan->update([
            'status_pemesanan' => $ringkasan['status'],
        ]);
    }

    /** Hitung ulang akumulasi dan saldo tiap catatan secara kronologis. */
    private function sinkronkanPembayaran(Pemesanan $pemesanan): void
    {
        $tagihan = max(
            0,
            round(
                (float) $pemesanan->total_harga
                + (float) $pemesanan->bunga
                - (float) $pemesanan->dp,
                2
            )
        );
        $total = 0;
        $sisa = $tagihan;

        $riwayat = Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->orderBy('tanggal_pembayaran')
            ->orderBy('id_pembayaran')
            ->get();

        foreach ($riwayat as $item) {
            if ($item->status_gateway !== 'settlement') {
                $item->updateQuietly([
                    'total_pembayaran' => $total,
                    'sisa_pembayaran' => $sisa,
                    'status_pembayaran' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
                ]);
                continue;
            }

            $total = round($total + (float) $item->nominal, 2);
            $sisa = round(max(0, $tagihan - $total), 2);
            $item->updateQuietly([
                'total_pembayaran' => $total,
                'sisa_pembayaran' => $sisa,
                'status_pembayaran' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
            ]);
        }

        $pemesanan->update([
            'status_pemesanan' => $riwayat->isNotEmpty() && $sisa <= 0
                ? 'Lunas'
                : 'Belum Lunas',
        ]);
    }
}
