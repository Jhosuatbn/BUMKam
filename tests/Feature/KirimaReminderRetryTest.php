<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\PengingatTagihan;
use App\Services\KirimaReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KirimaReminderRetryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_failed_reminder_is_retried_once_before_its_due_date_without_duplicate_log(): void
    {
        Carbon::setTestNow('2026-11-01 09:00:00');
        config([
            'kirima.enabled' => true,
            'kirima.client_id' => 'test-client',
            'kirima.client_secret' => 'test-secret',
            'kirima.device_jid' => 'test-device',
            'kirima.reminder_days_before' => 3,
            'kirima.api_url' => 'https://kirima.test/messages',
            'kredit.zona_waktu' => 'Asia/Jakarta',
        ]);
        Http::fakeSequence('https://kirima.test/messages')
            ->push(['code' => 'FAILED'], 500)
            ->push(['code' => 'SUCCESS'], 200);

        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-KIRIMA-RETRY',
            'nama_pelanggan' => 'Pelanggan Uji Reminder',
            'nik' => '5566778899001122',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat uji reminder',
            'wa_reminder_opt_in' => true,
            'wa_reminder_opted_in_at' => '2026-10-01 09:00:00',
        ]);
        Pemesanan::create([
            'id_pemesanan' => 'PSN-KIRIMA-RETRY',
            'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => '2026-10-04',
            'skema_pembayaran' => 'Angsuran',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => 120000,
            'angsuran' => 10000,
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => 'Disetujui',
        ]);

        $firstRun = app(KirimaReminderService::class)->process(false, '2026-11-01');
        $this->assertSame(1, $firstRun['failed']);
        $this->assertDatabaseHas('pengingat_tagihan', [
            'id_pemesanan' => 'PSN-KIRIMA-RETRY',
            'status' => 'gagal',
        ]);

        Carbon::setTestNow('2026-11-02 09:00:00');
        $retryRun = app(KirimaReminderService::class)->process(false, '2026-11-02');

        $this->assertSame(1, $retryRun['sent']);
        $this->assertDatabaseCount('pengingat_tagihan', 1);
        $this->assertDatabaseHas('pengingat_tagihan', [
            'id_pemesanan' => 'PSN-KIRIMA-RETRY',
            'status' => 'terkirim',
        ]);
        Http::assertSentCount(2);
    }
}
