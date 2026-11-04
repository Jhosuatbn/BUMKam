<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\PengingatTagihan;
use App\Models\Pemesanan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KirimaReminderService
{
    public function __construct(private readonly MonitoringKreditService $monitoring)
    {
    }

    /** Send due-date reminders, or report eligible rows without sending in dry-run mode. */
    public function process(bool $dryRun = false, ?string $asOf = null): array
    {
        $timezone = config('kredit.zona_waktu', 'Asia/Jakarta');
        $today = $asOf
            ? CarbonImmutable::parse($asOf, $timezone)->startOfDay()
            : CarbonImmutable::today($timezone);
        $daysBefore = max(0, (int) config('kirima.reminder_days_before', 3));
        $targetDate = $today->addDays($daysBefore)->toDateString();
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        if (! $dryRun && ! config('kirima.enabled')) {
            return ['disabled' => true, 'eligible' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        if (! $dryRun && (! config('kirima.client_id') || ! config('kirima.client_secret') || ! config('kirima.device_jid'))) {
            throw new \RuntimeException('KIRIMA_CLIENT_ID, KIRIMA_CLIENT_SECRET, dan KIRIMA_DEVICE_JID harus dikonfigurasi.');
        }

        $orders = Pemesanan::query()
            ->with([
                'pelanggan',
                'pembayaran' => fn ($query) => $query->where('status_gateway', 'settlement')
                    ->when(\Illuminate\Support\Facades\Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))
                    ->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran'),
                'penyesuaianDenda',
                'dendaAngsuran',
            ])
            ->where('tenor', '>', 0)
            ->where(fn ($query) => $query
                ->whereNull('status_persetujuan')
                ->orWhere('status_persetujuan', 'Disetujui'))
            ->get();

        $eligible = 0;
        foreach ($orders as $order) {
            $customer = $order->pelanggan;
            if (! $customer || ! $customer->wa_reminder_opt_in || ! $customer->no_telepon) {
                continue;
}
            $rows = $this->monitoring->hitung($order)['jadwal'];
            foreach ($rows as $row) {
                $dueDate = CarbonImmutable::parse($row['tanggal_jatuh_tempo'], $timezone);
                if ($dueDate->toDateString() !== $targetDate || $row['status'] === 'Lunas') {
                    continue;
                }

                $eligible++;
                $message = $this->message($customer, $order, $row, $dueDate);
                if ($dryRun) {
                    $skipped++;
                    Log::info('Simulasi pengingat tagihan Kirima.', [
                        'id_pemesanan' => $order->id_pemesanan,
                        'nomor_angsuran' => $row['nomor'],
                        'nomor_penerima' => $this->phone($customer->no_telepon),
                    ]);
                    continue;
                }

                $log = PengingatTagihan::firstOrCreate(
                    [
                        'id_pemesanan' => $order->id_pemesanan,
                        'nomor_angsuran' => $row['nomor'],
                        'tanggal_jatuh_tempo' => $dueDate->toDateString(),
                        'jenis' => 'h-'.$daysBefore,
                    ],
                    ['status' => 'pending', 'pesan' => $message]
                );

                if (! $log->wasRecentlyCreated) {
                    $skipped++;
                    continue;
                }

                try {
                    $response = Http::asJson()
                        ->withHeaders([
                            'X-Client-Id' => config('kirima.client_id'),
                            'X-Client-Secret' => config('kirima.client_secret'),
                        ])
                        ->connectTimeout(5)
                        ->timeout(20)
                        ->post(config('kirima.api_url'), [
                            'device_jid' => config('kirima.device_jid'),
                            'phone' => $this->phone($customer->no_telepon),
                            'message' => $message,
                        ]);

                    if (! $response->successful() || $response->json('code') !== 'SUCCESS') {
                        throw new \RuntimeException('Kirima HTTP '.$response->status().': '.Str::limit($response->body(), 1000));
                    }

                    $log->update([
                        'status' => 'terkirim',
                        'respons_gateway' => Str::limit($response->body(), 4000),
                        'terkirim_pada' => now($timezone),
                    ]);
                    $sent++;
                } catch (\Throwable $exception) {
                    $log->update([
                        'status' => 'gagal',
                        'respons_gateway' => Str::limit($exception->getMessage(), 4000),
                    ]);
                    Log::error('Pengingat tagihan Kirima gagal dikirim.', [
                        'pengingat_tagihan_id' => $log->id,
                        'id_pemesanan' => $order->id_pemesanan,
                        'nomor_angsuran' => $row['nomor'],
                        'error' => $exception->getMessage(),
                    ]);
                    $failed++;
                }
            }
        }

        return compact('eligible', 'sent', 'skipped', 'failed') + ['disabled' => false];
    }

    private function message(Pelanggan $customer, Pemesanan $order, array $row, CarbonImmutable $dueDate): string
    {
        $money = number_format((float) $row['nominal'] - (float) $row['nominal_terbayar'], 0, ',', '.');
        $name = trim((string) $customer->nama_pelanggan);

        return "Halo {$name}, pengingat angsuran ke-{$row['nomor']} untuk pesanan {$order->id_pemesanan} sebesar Rp{$money} jatuh tempo pada {$dueDate->locale('id')->translatedFormat('j F Y')}. Jika sudah membayar, abaikan pesan ini. Terima kasih.";
    }

    private function phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

}
