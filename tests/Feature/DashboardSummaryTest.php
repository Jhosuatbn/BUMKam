<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_staff_dashboard_loads_without_pimpinan_summary_data(): void
    {
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-DASH-STAFF', 'username' => 'dashboard_staff', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Dashboard', 'peran' => 'Staff',
        ]);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Pembayaran Diterima');
    }

    public function test_pimpinan_dashboard_loads_credit_summaries_without_activity_card(): void
    {
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-DASH-LEAD', 'username' => 'dashboard_leader', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Dashboard', 'peran' => 'Pimpinan',
        ]);

        $this->actingAs($leader)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Piutang Berjalan')
            ->assertDontSee('Aktivitas Operasional');
    }

    public function test_credit_dashboard_totals_exclude_unapproved_rejected_and_cancelled_orders(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-DASH-CREDIT', 'username' => 'dashboard_credit_leader', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Kredit', 'peran' => 'Pimpinan',
        ]);

        $this->creditOrder('APPROVED', 'Disetujui', 111100);
        $this->creditOrder('LEGACY', null, 222200);
        $this->creditOrder('PENDING', 'Menunggu Persetujuan', 333300);
        $this->creditOrder('REJECTED', 'Ditolak', 444400);
        $this->creditOrder('CANCELLED', 'Dibatalkan', 555500);

        $this->actingAs($leader)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalKredit', 2)
            ->assertViewHas('piutangBerjalan', 333300.0);
    }

    public function test_dashboard_period_boundaries_follow_the_credit_timezone(): void
    {
        // UTC is still October, while Asia/Jakarta has entered November.
        Carbon::setTestNow('2026-10-31 17:30:00');
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-DASH-TZ', 'username' => 'dashboard_timezone_staff', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Zona Waktu', 'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-DASH-TZ', 'nama_pelanggan' => 'Pelanggan Zona Waktu', 'nik' => '4433221144332211',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji zona waktu',
        ]);
        $this->dashboardOrder($customer->id_pelanggan, 'OCT', '2026-10-31');
        $this->dashboardOrder($customer->id_pelanggan, 'NOV', '2026-11-01');

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('transaksiBulanIni', 1)
            ->assertViewHas('transaksiBulanLalu', 1);
    }

    private function creditOrder(string $suffix, ?string $approval, float $amount): void
    {
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-DASH-'.$suffix,
            'nama_pelanggan' => 'Pelanggan '.$suffix,
            'nik' => '1111'.str_pad((string) abs(crc32($suffix)), 12, '0', STR_PAD_LEFT),
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat uji dashboard',
        ]);

        Pemesanan::create([
            'id_pemesanan' => 'PSN-DASH-'.$suffix,
            'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => '2026-11-05',
            'skema_pembayaran' => 'Angsuran',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => $amount,
            'angsuran' => round($amount / 12, 2),
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => $approval,
        ]);
    }

    private function dashboardOrder(string $customerId, string $suffix, string $date): void
    {
        Pemesanan::create([
            'id_pemesanan' => 'PSN-DASH-TZ-'.$suffix,
            'id_pelanggan' => $customerId,
            'tanggal_pemesanan' => $date,
            'skema_pembayaran' => 'Tanpa DP',
            'dp' => 0,
            'tenor' => 0,
            'bunga' => 0,
            'total_harga' => 50000,
            'angsuran' => 0,
            'status_pemesanan' => 'Lunas',
        ]);
    }
}
