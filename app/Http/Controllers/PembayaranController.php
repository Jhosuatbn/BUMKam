<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\MidtransRefundEvent;
use App\Models\PembayaranReversal;
use App\Models\Pemesanan;
use App\Services\AlokasiPembayaranService;
use App\Services\AuditTrailWriter;
use App\Services\MonitoringKreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayaran = Pembayaran::with([
            'pemesanan.pelanggan',
            'pemesanan.pembayaran' => fn ($query) => $query
                ->whereIn('status_gateway', ['settlement', 'partial_refund'])
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ])
            ->orderByDesc('tanggal_pembayaran')
            ->orderByDesc('created_at')
            ->orderByDesc('id_pembayaran')
            ->paginate(15)
            ->withQueryString();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create(MonitoringKreditService $monitoring)
    {
        $pemesanan = Pemesanan::with('pelanggan')
            ->where('tenor', '>', 0)
            ->where(function ($query) {
                $query->whereNull('status_persetujuan')
                    ->orWhere('status_persetujuan', 'Disetujui');
            })
            ->whereDoesntHave('pembayaran', fn ($query) => $query->where('status_gateway', 'pending'))
            ->get();
        $pemesanan->each(function (Pemesanan $order) use ($monitoring) {
            $order->load($this->relasiPembayaranSettlement());
            $order->setAttribute('ringkasan_denda', $monitoring->hitung($order));
        });
        $pemesanan = $pemesanan->filter(fn (Pemesanan $order) => $order->ringkasan_denda['sisa_kewajiban'] > 0)->values();
        $idPembayaranPreview = $this->idPembayaranBerikutnya();

        $dendaAktif = $this->dendaTersedia();
        return view('pembayaran.create', compact('pemesanan', 'idPembayaranPreview', 'dendaAktif'));
    }

    public function store(Request $request, AlokasiPembayaranService $allocator)
    {
        $validated = $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
            'tanggal_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,online',
            'nominal' => 'nullable|numeric|gt:0',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        if ($validated['metode_pembayaran'] === 'online') {
            return $this->buatPembayaranOnline($validated);
        }

        $bukti = $request->file('bukti_pembayaran')?->store('bukti-pembayaran', 'local');

        try {
            DB::transaction(function () use ($validated, $bukti, $allocator) {
                $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                    ->with($this->relasiPembayaranSettlement())
                    ->lockForUpdate()
                    ->firstOrFail();
                $this->pastikanDapatDibayar($pemesanan);
                $maksimumDapatDibayar = $this->sisaTagihan($pemesanan)
                    + (float) app(MonitoringKreditService::class)->hitung($pemesanan)['total_denda_belum_dibayar'];
                $nominalDiminta = (float) ($validated['nominal'] ?? $pemesanan->angsuran);
                if (array_key_exists('nominal', $validated) && $nominalDiminta > $maksimumDapatDibayar) {
                    throw ValidationException::withMessages(['nominal' => 'Nominal melebihi sisa angsuran dan denda yang dapat dibayar.']);
                }
                $nominal = min($nominalDiminta, $maksimumDapatDibayar);

                if ($nominal <= 0) {
                    throw ValidationException::withMessages([
                        'id_pemesanan' => 'Pemesanan ini belum memiliki nominal angsuran yang valid.',
                    ]);
                }

                $payment = Pembayaran::create([
                    'id_pembayaran' => $this->idPembayaranBerikutnya(),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                    'nominal' => $nominal,
                    'total_pembayaran' => 0,
                    'sisa_pembayaran' => $this->sisaTagihan($pemesanan),
                    'status_pembayaran' => 'Belum Lunas',
                    'metode_pembayaran' => 'tunai',
                    'status_gateway' => 'settlement',
                    'bukti_pembayaran' => $bukti,
                ]);

                $alokasi = $this->dendaTersedia()
                    ? $allocator->alokasikan($pemesanan, $payment, $nominal)
                    : ['pokok' => $nominal, 'denda' => 0.0];
                $ringkasan = $this->ringkasanPembayaran($pemesanan, $alokasi['pokok'], $payment->id_pembayaran);
                $payment->update([
                    'total_pembayaran' => $ringkasan['total'],
                    'sisa_pembayaran' => $ringkasan['sisa'],
                    'status_pembayaran' => $ringkasan['status'],
                ]);

                $pemesanan->update(['status_pemesanan' => $ringkasan['status']]);
                $this->sinkronkanPembayaran($pemesanan);
            });
        } catch (Throwable $exception) {
            $this->hapusBuktiJikaTransaksiGagal($bukti, $validated['id_pemesanan']);
            throw $exception;
        }

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran tunai berhasil dicatat.');
    }

    private function hapusBuktiJikaTransaksiGagal(?string $path, string $idPemesanan): void
    {
        if (! $path) {
            return;
        }

        try {
            if (! Storage::disk('local')->delete($path)) {
                Log::warning('File bukti pembayaran gagal dibersihkan setelah transaksi gagal.', [
                    'id_pemesanan' => $idPemesanan,
                ]);
            }
        } catch (Throwable $cleanupException) {
            // Preserve the original transaction error; never log the private file path.
            Log::error('Terjadi kegagalan saat membersihkan bukti pembayaran setelah transaksi gagal.', [
                'id_pemesanan' => $idPemesanan,
                'cleanup_error' => $cleanupException::class,
            ]);
        }
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

    public function notifikasiMidtrans(Request $request, AlokasiPembayaranService $allocator)
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
        if (in_array($statusGateway, ['refund', 'partial_refund'], true)) {
            $refundAmount = $request->input('refund_amount');
            if ($statusGateway === 'refund' && ($refundAmount === null || $refundAmount === '')) {
                $refundAmount = $pembayaran->nominal;
            }
            if (! is_numeric($refundAmount)) {
                return response()->json(['message' => 'Nominal refund tidak valid.'], 422);
            }

            return $this->terapkanRefundMidtrans(
                $pembayaran->id_pembayaran,
                $statusGateway,
                (float) $refundAmount,
                $request->input('refund_chargeback_id') ?? $request->input('refund_key')
            );
        }

        $this->terapkanStatusMidtrans($pembayaran->id_pembayaran, $statusGateway, $allocator);

        return response()->json(['message' => 'Notifikasi diterima.']);
    }

    public function sinkronkanMidtrans(Request $request, Pembayaran $pembayaran, AlokasiPembayaranService $allocator)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        abort_unless($pembayaran->metode_pembayaran === 'online', 404);
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            return back()->withErrors(['pembayaran' => 'Kunci server Midtrans belum dikonfigurasi.']);
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(15)
                ->get($baseUrl . '/v2/' . rawurlencode($pembayaran->id_pembayaran) . '/status');
        } catch (\Throwable) {
            return back()->withErrors(['pembayaran' => 'Status Midtrans belum dapat diperiksa. Coba lagi nanti.']);
        }

        $status = (string) $response->json('transaction_status');
        $gross = number_format((float) $response->json('gross_amount'), 2, '.', '');
        if (! $response->successful()
            || (string) $response->json('order_id') !== $pembayaran->id_pembayaran
            || $gross !== number_format((float) $pembayaran->nominal, 2, '.', '')) {
            return back()->withErrors(['pembayaran' => 'Data status Midtrans tidak cocok dengan transaksi yang tersimpan.']);
        }

        $gatewayStatus = match ($status) {
            'settlement' => 'settlement',
            'capture' => $response->json('fraud_status', 'accept') === 'accept' ? 'settlement' : 'deny',
            'pending' => 'pending',
            'expire' => 'expire',
            'cancel' => 'cancel',
            'deny' => 'deny',
            'failure' => 'failure',
            'refund' => 'refund',
            'partial_refund' => 'partial_refund',
            default => null,
        };
        if ($gatewayStatus === null) {
            return back()->withErrors(['pembayaran' => 'Status transaksi Midtrans belum dikenali.']);
        }
        if (in_array($gatewayStatus, ['refund', 'partial_refund'], true)) {
            $refundAmount = $response->json('refund_amount');
            if ($gatewayStatus === 'refund' && ($refundAmount === null || $refundAmount === '')) {
                $refundAmount = $pembayaran->nominal;
            }
            if (! is_numeric($refundAmount)) {
                return back()->withErrors(['pembayaran' => 'Midtrans tidak mengembalikan nominal refund yang valid.']);
            }
            $result = $this->terapkanRefundMidtrans(
                $pembayaran->id_pembayaran,
                $gatewayStatus,
                (float) $refundAmount,
                $response->json('refund_chargeback_id') ?? $response->json('refund_key')
            );
            if ($result->getStatusCode() >= 400) {
                return back()->withErrors(['pembayaran' => 'Rekonsiliasi refund Midtrans gagal divalidasi.']);
            }
        } else {
            $this->terapkanStatusMidtrans($pembayaran->id_pembayaran, $gatewayStatus, $allocator);
        }

        return back()->with('success', 'Status transaksi berhasil disinkronkan dari Midtrans; alokasi refund yang tervalidasi sudah disesuaikan.');
    }

    private function terapkanStatusMidtrans(string $idPembayaran, string $statusGateway, AlokasiPembayaranService $allocator): void
    {
        DB::transaction(function () use ($idPembayaran, $statusGateway, $allocator) {
            $snapshot = Pembayaran::whereKey($idPembayaran)->firstOrFail();
            $pemesanan = Pemesanan::whereKey($snapshot->id_pemesanan)
                ->lockForUpdate()
                ->firstOrFail();
            $pembayaranTerkunci = Pembayaran::whereKey($idPembayaran)
                ->lockForUpdate()
                ->firstOrFail();

            // Status akhir tidak boleh mundur akibat callback lama yang datang terlambat.
            if (in_array($pembayaranTerkunci->status_gateway, ['settlement', 'partial_refund', 'refund', 'reversed'], true)) {
                return;
            }

            if ($statusGateway === 'settlement' && $pembayaranTerkunci->status_gateway !== 'settlement') {
                $pemesanan->load($this->relasiPembayaranSettlement());
                $pemesanan->setRelation('pembayaran', $pemesanan->pembayaran->reject(
                    fn (Pembayaran $item) => $item->id_pembayaran === $pembayaranTerkunci->id_pembayaran
                )->values());
                if ($this->dendaTersedia()) {
                    $allocator->alokasikan($pemesanan, $pembayaranTerkunci, (float) $pembayaranTerkunci->nominal);
                }
            }

            $pembayaranTerkunci->update([
                'status_gateway' => $statusGateway,
                'tanggal_pembayaran' => $statusGateway === 'settlement'
                    ? now()->toDateString()
                    : $pembayaranTerkunci->tanggal_pembayaran,
            ]);
            $this->sinkronkanPembayaran($pemesanan);
        });
    }

    private function terapkanRefundMidtrans(string $idPembayaran, string $status, float $nominalKumulatif, mixed $refundId)
    {
        if (! Schema::hasTable('alokasi_pembayaran') || ! Schema::hasTable('alokasi_pembayaran_pengembalian')) {
            return response()->json(['message' => 'Migration pencatatan alokasi refund belum diterapkan. Refund belum dicatat agar saldo kredit tidak keliru.'], 503);
        }
        $pembayaran = Pembayaran::whereKey($idPembayaran)->firstOrFail();
        abort_unless($pembayaran->metode_pembayaran === 'online', 404);
        $nominal = (float) $pembayaran->nominal;
        if ($nominalKumulatif <= 0 || $nominalKumulatif > $nominal + 0.01
            || ($status === 'refund' && abs($nominalKumulatif - $nominal) > 0.01)
            || ($status === 'partial_refund' && $nominalKumulatif >= $nominal - 0.01)) {
            return response()->json(['message' => 'Nominal refund tidak sesuai dengan transaksi.'], 422);
        }

        DB::transaction(function () use ($idPembayaran, $status, $nominalKumulatif, $refundId) {
            $snapshot = Pembayaran::whereKey($idPembayaran)->firstOrFail();
            $pemesanan = Pemesanan::whereKey($snapshot->id_pemesanan)->lockForUpdate()->firstOrFail();
            $locked = Pembayaran::whereKey($idPembayaran)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status_gateway, ['settlement', 'partial_refund', 'refund'], true)) {
                return;
            }
            if ($locked->status_gateway === 'refund' && $status === 'partial_refund') {
                return;
            }
            $cumulative = min((float) $locked->nominal, max((float) $locked->nominal_dikembalikan, $nominalKumulatif));
            $delta = round($cumulative - (float) $locked->nominal_dikembalikan, 2);
            if ($delta <= 0) return;
            $providerId = $refundId === null ? null : (string) $refundId;
            $eventKey = hash('sha256', implode('|', [$idPembayaran, $providerId ?? '', $status, number_format($nominalKumulatif, 2, '.', '')]));
            $event = MidtransRefundEvent::firstOrCreate(['event_key' => $eventKey], [
                'id_pembayaran' => $idPembayaran,
                'refund_provider_id' => $providerId,
                'nominal_refund' => $delta,
                'nominal_refund_kumulatif' => $cumulative,
            ]);
            if (! $event->wasRecentlyCreated) return;
            app(\App\Services\PengembalianAlokasiPembayaranService::class)->reversikan($locked, $event, $delta);
            $locked->update([
                'status_gateway' => $cumulative >= (float) $locked->nominal - 0.01 ? 'refund' : 'partial_refund',
                'nominal_dikembalikan' => $cumulative,
            ]);
            $this->sinkronkanPembayaran($pemesanan);
        });

        $message = $status === 'partial_refund'
            ? 'Refund tercatat dan alokasi pembayaran terakhir telah dibalik otomatis.'
            : 'Refund Midtrans tercatat.';
        return response()->json(['message' => $message]);
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
            $maksimumDapatDibayar = $this->sisaTagihan($pemesanan)
                + (float) app(MonitoringKreditService::class)->hitung($pemesanan)['total_denda_belum_dibayar'];
            $nominalDiminta = (float) ($validated['nominal'] ?? $pemesanan->angsuran);
            if (array_key_exists('nominal', $validated) && $nominalDiminta > $maksimumDapatDibayar) {
                throw ValidationException::withMessages(['nominal' => 'Nominal melebihi sisa angsuran dan denda yang dapat dibayar.']);
            }
            $nominal = min((int) round($nominalDiminta), (int) round($maksimumDapatDibayar));
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
        if ($pemesanan->status_persetujuan !== null
            && $pemesanan->status_persetujuan !== 'Disetujui') {
            throw ValidationException::withMessages([
                'id_pemesanan' => $pemesanan->status_persetujuan === 'Ditolak'
                    ? 'Pemesanan ini ditolak dan tidak dapat menerima pembayaran.'
                    : 'Pemesanan ini masih menunggu persetujuan Pimpinan.',
            ]);
        }

        if (app(MonitoringKreditService::class)->hitung($pemesanan)['sisa_kewajiban'] <= 0) {
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
        $query = Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->whereIn('status_gateway', ['settlement', 'partial_refund']);
        if (Schema::hasTable('alokasi_pembayaran')) $query->with('alokasiDenda');
        $pembayaran = $query->get();
        $terbayar = (float) $pembayaran->sum(fn (Pembayaran $item) => app(\App\Services\PengembalianAlokasiPembayaranService::class)->pokokEfektif($item));

        return round(max(0, $tagihan - $terbayar), 2);
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load('pemesanan.pelanggan');
        $pembayaran->load('reversal.pengguna');
        if (Schema::hasTable('midtrans_refund_events')) $pembayaran->load('refundEvents');
        else $pembayaran->setRelation('refundEvents', collect());
        if (Schema::hasTable('alokasi_pembayaran_pengembalian')) $pembayaran->load('pengembalianAlokasi.alokasi');
        else $pembayaran->setRelation('pengembalianAlokasi', collect());
        if (Schema::hasTable('alokasi_pembayaran')) $pembayaran->load('alokasiDenda');
        else $pembayaran->setRelation('alokasiDenda', collect());

        return view('pembayaran.show', compact('pembayaran'));
    }

    public function receipt(Pembayaran $pembayaran)
    {
        abort_unless(in_array($pembayaran->status_gateway, ['settlement', 'partial_refund', 'refund'], true), 404);
        $pembayaran->load('pemesanan.pelanggan');

        return view('pembayaran.receipt', compact('pembayaran'));
    }

    public function bukti(Pembayaran $pembayaran)
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
        abort_unless($pembayaran->bukti_pembayaran, 404);

        $disk = Storage::disk('local')->exists($pembayaran->bukti_pembayaran) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($pembayaran->bukti_pembayaran), 404);
        $extension = strtolower(pathinfo($pembayaran->bukti_pembayaran, PATHINFO_EXTENSION));
        $mime = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? null : 'application/pdf';
        $headers = array_filter([
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        return Storage::disk($disk)->response($pembayaran->bukti_pembayaran, 'bukti-'.$pembayaran->id_pembayaran.'.'.$extension, $headers, 'inline');
    }

    public function edit(Pembayaran $pembayaran)
    {
        abort(403, 'Riwayat pembayaran tidak dapat diedit. Gunakan reversal atau rekonsiliasi yang tercatat.');
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        abort(403, 'Riwayat pembayaran tidak dapat diedit. Gunakan reversal atau rekonsiliasi yang tercatat.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        abort_if(Schema::hasTable('alokasi_pembayaran') && $pembayaran->alokasiDenda()->exists(), 403, 'Pembayaran yang sudah memiliki rincian alokasi tidak dapat dihapus.');
        abort_if(in_array($pembayaran->status_gateway, ['settlement', 'partial_refund', 'refund', 'reversed'], true), 403, 'Pembayaran diterima atau memiliki histori koreksi; catat reversal atau rekonsiliasi tanpa menghapus riwayat.');
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
            Storage::disk('local')->delete($bukti);
        }

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function reverse(Request $request, Pembayaran $pembayaran, AuditTrailWriter $auditTrail)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        $validated = $request->validate(['alasan' => ['required', 'string', 'max:2000']]);
        if ($pembayaran->metode_pembayaran !== 'tunai' || $pembayaran->status_gateway !== 'settlement') {
            throw ValidationException::withMessages(['pembayaran' => 'Reversal hanya tersedia untuk pembayaran tunai yang berstatus diterima.']);
        }

        DB::transaction(function () use ($request, $pembayaran, $validated) {
            $snapshot = Pembayaran::whereKey($pembayaran->id_pembayaran)->firstOrFail();
            $pemesanan = Pemesanan::whereKey($snapshot->id_pemesanan)->lockForUpdate()->firstOrFail();
            $locked = Pembayaran::whereKey($pembayaran->id_pembayaran)->lockForUpdate()->firstOrFail();
            if ($locked->metode_pembayaran !== 'tunai' || $locked->status_gateway !== 'settlement') {
                throw ValidationException::withMessages(['pembayaran' => 'Pembayaran sudah berubah status atau bukan transaksi tunai.']);
            }

            PembayaranReversal::create([
                'id_pembayaran' => $locked->id_pembayaran,
                'nominal' => $locked->nominal,
                'alasan' => $validated['alasan'],
                'id_pengguna' => $request->user()->id_pengguna,
            ]);
            $locked->update(['status_gateway' => 'reversed']);
            $this->sinkronkanPembayaran($pemesanan);
        });

        $auditTrail->record($request->user(), $request, 'reverse', 'Pembayaran', 'Pembayaran tunai ' . $pembayaran->id_pembayaran . ' dibalikkan. Alasan: ' . $validated['alasan']);
        return redirect()->route('pembayaran.show', $pembayaran)->with('success', 'Reversal tercatat; histori pembayaran tetap tersimpan.');
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
            ->whereIn('status_gateway', ['settlement', 'partial_refund']);

        if ($idPembayaranDikecualikan !== null) {
            $query->where('id_pembayaran', '!=', $idPembayaranDikecualikan);
        }

        if (Schema::hasTable('alokasi_pembayaran')) $query->with('alokasiDenda');
        $payments = $query->get();
        $total = round((float) $payments->sum(fn (Pembayaran $item) => app(\App\Services\PengembalianAlokasiPembayaranService::class)->pokokEfektif($item)) + $nominalTambahan, 2);
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
            if (! in_array($item->status_gateway, ['settlement', 'partial_refund'], true)) {
                $item->updateQuietly([
                    'total_pembayaran' => $total,
                    'sisa_pembayaran' => $sisa,
                    'status_pembayaran' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
                ]);
                continue;
            }

            if (Schema::hasTable('alokasi_pembayaran')) $item->loadMissing('alokasiDenda');
            $nominalPokok = app(\App\Services\PengembalianAlokasiPembayaranService::class)->pokokEfektif($item);
            $total = round($total + $nominalPokok, 2);
            $sisa = round(max(0, $tagihan - $total), 2);
            $item->updateQuietly([
                'total_pembayaran' => $total,
                'sisa_pembayaran' => $sisa,
                'status_pembayaran' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
            ]);
        }

        $pemesanan->update([
            'status_pemesanan' => $riwayat->isNotEmpty() && $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
        ]);
        $pemesanan->unsetRelation('pembayaran');
        $pemesanan->load($this->relasiPembayaranSettlement());
        $ringkasanKredit = app(MonitoringKreditService::class)->hitung($pemesanan);
        $pemesanan->update(['status_pemesanan' => $ringkasanKredit['sisa_kewajiban'] <= 0 ? 'Lunas' : 'Belum Lunas']);
    }

    private function dendaTersedia(): bool
    {
        return app(\App\Services\DendaKeterlambatanService::class)->tersedia();
    }

    private function relasiPembayaranSettlement(): array
    {
        return [
            'pembayaran' => function ($query) {
                $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran');
                if (Schema::hasTable('alokasi_pembayaran')) $query->with('alokasiDenda');
            },
            ...(Schema::hasTable('penyesuaian_denda') ? ['penyesuaianDenda'] : []),
            ...(Schema::hasTable('denda_angsuran') ? ['dendaAngsuran'] : []),
        ];
    }
}
