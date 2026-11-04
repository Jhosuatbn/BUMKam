<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use App\Services\KirimaReminderService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('kirima:remind-installments {--dry-run : Tampilkan angsuran yang memenuhi syarat tanpa mengirim pesan} {--as-of= : Tanggal simulasi YYYY-MM-DD; hanya untuk dry-run}', function (KirimaReminderService $reminders) {
    $dryRun = (bool) $this->option('dry-run');
    $asOf = $this->option('as-of');
    if ($asOf && ! $dryRun) {
        $this->error('--as-of hanya boleh dipakai bersama --dry-run.');
        return self::FAILURE;
    }
    if ($asOf) {
        try {
            $parsedDate = \Carbon\CarbonImmutable::parse($asOf, config('kredit.zona_waktu', 'Asia/Jakarta'));
        } catch (\Throwable) {
            $this->error('--as-of harus memakai format YYYY-MM-DD.');
            return self::FAILURE;
        }
        if ($parsedDate->format('Y-m-d') !== $asOf) {
            $this->error('--as-of harus memakai format YYYY-MM-DD.');
            return self::FAILURE;
        }
        $asOf = $parsedDate->toDateString();
    }
    $result = $reminders->process($dryRun, $asOf ?: null);

    if ($result['disabled'] ?? false) {
        $this->warn('Pengiriman Kirima nonaktif. Atur KIRIMA_ENABLED=true setelah uji simulasi dan pengaturan API selesai.');
        return self::SUCCESS;
    }

    $this->info(($dryRun ? 'Simulasi'.($asOf ? " untuk {$asOf}" : '').' selesai. ' : 'Pemrosesan selesai. ')
        .'Layak: '.$result['eligible'].', terkirim: '.$result['sent'].', dilewati: '.$result['skipped'].', gagal: '.$result['failed'].'.');

    return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Kirim pengingat angsuran melalui Kirima atau jalankan simulasi');

Artisan::command('kirima:test {phone : Nomor WhatsApp uji dalam format 62...} {--send : Kirim satu pesan uji sungguhan}', function (string $phone) {
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '0')) {
        $digits = '62'.substr($digits, 1);
    }
    if (! preg_match('/^62\d{8,13}$/', $digits)) {
        $this->error('Gunakan nomor Indonesia yang valid, misalnya 6281234567890.');
        return self::FAILURE;
    }

    if (! $this->option('send')) {
        $this->line("Simulasi: satu pesan uji akan dikirim ke {$digits}. Tidak ada pesan yang dikirim.");
        $this->comment('Tambahkan opsi --send untuk mengirim sungguhan setelah API key dikonfigurasi.');
        return self::SUCCESS;
    }

    if (! config('kirima.enabled')) {
        $this->error('KIRIMA_ENABLED masih false. Aktifkan sementara di .env untuk mengirim pesan uji.');
        return self::FAILURE;
    }
    if (! config('kirima.client_id') || ! config('kirima.client_secret') || ! config('kirima.device_jid')) {
        $this->error('KIRIMA_CLIENT_ID, KIRIMA_CLIENT_SECRET, dan KIRIMA_DEVICE_JID harus dikonfigurasi.');
        return self::FAILURE;
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
                'phone' => $digits,
                'message' => 'Ini pesan uji koneksi Kirima dari BUMKam. Abaikan pesan ini.',
            ]);
    } catch (\Throwable $exception) {
        $this->error('Koneksi ke Kirima gagal: '.$exception->getMessage());
        return self::FAILURE;
    }

    if (! $response->successful() || $response->json('code') !== 'SUCCESS') {
        $this->error('Kirima menolak pesan uji (HTTP '.$response->status().'): '.\Illuminate\Support\Str::limit($response->body(), 500));
        return self::FAILURE;
    }

    $this->info("Pesan uji berhasil diterima Kirima untuk {$digits}.");
    return self::SUCCESS;
})->purpose('Uji koneksi Kirima; pesan sungguhan hanya dikirim dengan opsi --send');

Artisan::command('kirima:test-reminder {phone : Nomor WhatsApp Anda dalam format 62...} {--send : Kirim satu contoh reminder sungguhan}', function (string $phone) {
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '0')) {
        $digits = '62'.substr($digits, 1);
    }
    if (! preg_match('/^62\d{8,13}$/', $digits)) {
        $this->error('Gunakan nomor Indonesia yang valid, misalnya 6281234567890.');
        return self::FAILURE;
    }

    $timezone = config('kredit.zona_waktu', 'Asia/Jakarta');
    $dueDate = \Carbon\CarbonImmutable::today($timezone)
        ->addDays(max(0, (int) config('kirima.reminder_days_before', 3)))
        ->locale('id')
        ->translatedFormat('j F Y');
    $message = "Halo, ini simulasi pengingat angsuran untuk pesanan DEMO-TEST. Angsuran ke-1 sebesar Rp350.000 jatuh tempo pada {$dueDate}. Ini pesan uji; tidak ada pembayaran yang perlu dilakukan.";

    if (! $this->option('send')) {
        $this->line("Simulasi reminder ke {$digits}: {$message}");
        $this->comment('Tidak ada pesan dikirim. Tambahkan --send untuk mengirim satu pesan sungguhan.');
        return self::SUCCESS;
    }

    if (! config('kirima.enabled')) {
        $this->error('KIRIMA_ENABLED masih false. Aktifkan sementara di .env untuk mengirim uji reminder.');
        return self::FAILURE;
    }
    if (! config('kirima.client_id') || ! config('kirima.client_secret') || ! config('kirima.device_jid')) {
        $this->error('KIRIMA_CLIENT_ID, KIRIMA_CLIENT_SECRET, dan KIRIMA_DEVICE_JID harus dikonfigurasi.');
        return self::FAILURE;
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
                'phone' => $digits,
                'message' => $message,
            ]);
    } catch (\Throwable $exception) {
        $this->error('Koneksi ke Kirima gagal: '.$exception->getMessage());
        return self::FAILURE;
    }

    if (! $response->successful() || $response->json('code') !== 'SUCCESS') {
        $this->error('Kirima menolak reminder uji (HTTP '.$response->status().'): '.\Illuminate\Support\Str::limit($response->body(), 500));
        return self::FAILURE;
    }

    $this->info("Reminder uji diterima Kirima untuk {$digits}.");
    return self::SUCCESS;
})->purpose('Pratinjau atau kirim satu contoh reminder WhatsApp tanpa mengubah data angsuran');

Artisan::command('kirima:seed-test-installment {phone : Nomor uji WhatsApp dalam format 62...}', function (string $phone) {
    if (app()->environment('production')) {
        $this->error('Perintah data uji dinonaktifkan di environment production.');
        return self::FAILURE;
    }
    if (! config('kirima.enabled')) {
        $this->error('KIRIMA_ENABLED harus true agar record uji dapat dipakai menguji pengiriman otomatis.');
        return self::FAILURE;
    }

    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '0')) {
        $digits = '62'.substr($digits, 1);
    }
    if (! preg_match('/^62\d{8,13}$/', $digits)) {
        $this->error('Gunakan nomor Indonesia yang valid, misalnya 6281234567890.');
        return self::FAILURE;
    }
    if (\App\Models\Pemesanan::where('id_pemesanan', 'like', 'KRMTEST-%')->exists()) {
        $this->error('Data uji Kirima sudah ada. Hapus data uji sebelumnya sebelum membuat record baru.');
        return self::FAILURE;
    }

    $timezone = config('kredit.zona_waktu', 'Asia/Jakarta');
    $now = \Carbon\CarbonImmutable::now($timezone);
    $nextCheck = $now->startOfDay()->setTimeFromTimeString(config('kirima.send_at', '09:00'));
    if ($nextCheck->lessThanOrEqualTo($now)) {
        $nextCheck = $nextCheck->addDay();
    }
    $dueDate = $nextCheck->startOfDay()->addDays(max(0, (int) config('kirima.reminder_days_before', 3)));

    $tenor = null;
    $orderDate = null;
    for ($candidateTenor = 1; $candidateTenor <= 12; $candidateTenor++) {
        $candidateDate = $dueDate->subMonthsNoOverflow($candidateTenor);
        if ($candidateDate->addMonthsNoOverflow($candidateTenor)->toDateString() === $dueDate->toDateString()) {
            $tenor = $candidateTenor;
            $orderDate = $candidateDate;
            break;
        }
    }
    if ($tenor === null) {
        $this->error('Tanggal uji tidak cocok dengan kalender angsuran bulanan.');
        return self::FAILURE;
    }

    $orderId = 'KRMTEST-'.$dueDate->format('Ymd');
    try {
        \Illuminate\Support\Facades\DB::transaction(function () use ($digits, $timezone, $tenor, $orderDate, $orderId) {
            $nik = null;
            do {
                $nik = '9'.str_pad((string) random_int(0, 999999999999999), 15, '0', STR_PAD_LEFT);
            } while (\App\Models\Pelanggan::where('nik', $nik)->exists());

            $customer = \App\Models\Pelanggan::create([
                'id_pelanggan' => \App\Models\Pelanggan::generateId(),
                'nama_pelanggan' => 'TEST KIRIMA REMINDER',
                'nik' => $nik,
                'no_telepon' => $digits,
                'wa_reminder_opt_in' => true,
                'wa_reminder_opted_in_at' => now($timezone),
                'alamat' => 'DATA UJI KIRIMA - HAPUS SETELAH TES',
            ]);

            $angsuran = 350000;
            \App\Models\Pemesanan::create([
                'id_pemesanan' => $orderId,
                'id_pelanggan' => $customer->id_pelanggan,
                'tanggal_pemesanan' => $orderDate->toDateString(),
                'skema_pembayaran' => 'Tanpa DP',
                'dp' => 0,
                'tenor' => $tenor,
                'bunga' => 0,
                'total_harga' => $angsuran * $tenor,
                'angsuran' => $angsuran,
                'status_pemesanan' => 'Belum Lunas',
                'status_persetujuan' => 'Disetujui',
                'persetujuan_pada' => now($timezone),
            ]);
        });
    } catch (\Throwable $exception) {
        $this->error('Record uji gagal dibuat: '.$exception->getMessage());
        return self::FAILURE;
    }

    $this->info("Record uji {$orderId} berhasil dibuat untuk pemeriksaan {$nextCheck->format('Y-m-d H:i')} ({$timezone}).");
    $this->line("Jatuh tempo angsuran: {$dueDate->format('Y-m-d')} (H-".max(0, (int) config('kirima.reminder_days_before', 3)).').');
    $this->warn('Record uji akan muncul pada monitoring/laporan kredit sampai dibersihkan; tidak ada stok yang diubah.');
    $this->comment("Setelah pengujian, jalankan: php artisan kirima:remove-test-installment {$orderId}");
    return self::SUCCESS;
})->purpose('Tambahkan satu record kredit uji H-3 untuk menguji pengiriman otomatis Kirima');

Artisan::command('kirima:remove-test-installment {orderId : ID KRMTEST yang ditampilkan saat membuat data uji}', function (string $orderId) {
    if (! str_starts_with($orderId, 'KRMTEST-')) {
        $this->error('Penghapusan hanya diizinkan untuk ID dengan awalan KRMTEST-.');
        return self::FAILURE;
    }

    $order = \App\Models\Pemesanan::with('pelanggan')->whereKey($orderId)->first();
    if (! $order || $order->pelanggan?->nama_pelanggan !== 'TEST KIRIMA REMINDER') {
        $this->error('Record uji Kirima tidak ditemukan atau pelanggan bukan data uji.');
        return self::FAILURE;
    }
    if ($order->detailPemesanan()->exists() || $order->pembayaran()->exists()) {
        $this->error('Record uji memiliki detail atau pembayaran; tidak dihapus otomatis.');
        return self::FAILURE;
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
        \App\Models\PengingatTagihan::where('id_pemesanan', $order->id_pemesanan)->delete();
        $customer = $order->pelanggan;
        $order->delete();
        if ($customer && ! $customer->pemesanan()->exists()) {
            $customer->delete();
        }
    });

    $this->info("Record uji {$orderId} dan catatan pengingat terkait berhasil dihapus.");
    return self::SUCCESS;
})->purpose('Hapus record uji Kirima dan catatan pengingatnya setelah pengujian');

Schedule::command('kirima:remind-installments')
    ->dailyAt(config('kirima.send_at', '09:00'))
    ->timezone(config('kredit.zona_waktu', 'Asia/Jakarta'))
    ->withoutOverlapping();
