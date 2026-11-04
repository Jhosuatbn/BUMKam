<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use App\Services\MonitoringKreditService;
use App\Services\AlokasiPembayaranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DendaKeterlambatanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function order(int $tenor = 1, float $amount = 100000): Pemesanan
    {
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-DENDA', 'nama_pelanggan' => 'Uji Denda', 'nik' => '1111222233334444',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji',
        ]);

        return Pemesanan::create([
            'id_pemesanan' => 'PSN-DENDA', 'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => '2026-08-01', 'skema_pembayaran' => 'Angsuran', 'dp' => 0,
            'tenor' => $tenor, 'bunga' => 0, 'total_harga' => $amount * $tenor,
            'angsuran' => $amount, 'status_pemesanan' => 'Belum Lunas',
        ]);
    }

    private function ringkasan(Pemesanan $order): array
    {
        $order->load(['pembayaran.alokasiDenda', 'penyesuaianDenda']);
        return app(MonitoringKreditService::class)->hitung($order);
    }

    public function test_installment_states_and_grace_period(): void
    {
        $order = $this->order();
        Carbon::setTestNow('2026-08-31 10:00:00');
        $this->assertSame('Belum Jatuh Tempo', $this->ringkasan($order)['jadwal'][0]['status']);

        Carbon::setTestNow('2026-09-08 10:00:00');
        $grace = $this->ringkasan($order)['jadwal'][0];
        $this->assertSame('Dalam Masa Tenggang', $grace['status']);
        $this->assertSame(0, $grace['hari_keterlambatan']);
        $this->assertSame(0.0, $grace['denda_berjalan']);

        Carbon::setTestNow('2026-09-09 10:00:00');
        $late = $this->ringkasan($order)['jadwal'][0];
        $this->assertSame('Terlambat', $late['status']);
        $this->assertSame(1, $late['hari_keterlambatan']);
        $this->assertSame(100.0, $late['denda_berjalan']);
    }

    public function test_penalty_is_capped_and_never_applies_to_paid_installment(): void
    {
        $order = $this->order();
        Carbon::setTestNow('2027-01-01 12:00:00');
        $late = $this->ringkasan($order)['jadwal'][0];
        $this->assertSame(5000.0, $late['denda_berjalan']);

        Pembayaran::create([
            'id_pembayaran' => 'BYR-DENDA-01', 'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => '2027-01-01', 'nominal' => 100000, 'total_pembayaran' => 100000,
            'sisa_pembayaran' => 0, 'status_pembayaran' => 'Lunas', 'metode_pembayaran' => 'tunai', 'status_gateway' => 'settlement',
        ]);
        $paid = $this->ringkasan($order->fresh())['jadwal'][0];
        $this->assertSame('Lunas', $paid['status']);
        $this->assertSame(0.0, $paid['denda_berjalan']);
    }

    public function test_partial_settlement_reduces_penalty_basis_and_pending_does_not_count(): void
    {
        $order = $this->order();
        Carbon::setTestNow('2026-09-10 12:00:00');
        Pembayaran::create([
            'id_pembayaran' => 'BYR-DENDA-01', 'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => '2026-09-10', 'nominal' => 40000, 'total_pembayaran' => 40000,
            'sisa_pembayaran' => 60000, 'status_pembayaran' => 'Belum Lunas', 'metode_pembayaran' => 'tunai', 'status_gateway' => 'settlement',
        ]);
        Pembayaran::create([
            'id_pembayaran' => 'BYR-DENDA-02', 'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => '2026-09-10', 'nominal' => 50000, 'total_pembayaran' => 40000,
            'sisa_pembayaran' => 60000, 'status_pembayaran' => 'Belum Lunas', 'metode_pembayaran' => 'online', 'status_gateway' => 'pending',
        ]);
        $row = $this->ringkasan($order->fresh())['jadwal'][0];
        $this->assertSame(40000.0, $row['nominal_terbayar']);
        $this->assertSame(60000.0, $row['sisa_angsuran']);
        $this->assertSame(120.0, $row['denda_berjalan']);
    }

    public function test_late_payment_of_principal_preserves_accrued_penalty_as_a_separate_balance(): void
    {
        $order = $this->order();
        Carbon::setTestNow('2026-09-10 12:00:00');
        $order->load(['pembayaran.alokasiDenda', 'penyesuaianDenda']);
        $payment = Pembayaran::create([
            'id_pembayaran' => 'BYR-DENDA-03', 'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => '2026-09-10', 'nominal' => 100000, 'total_pembayaran' => 100000,
            'sisa_pembayaran' => 0, 'status_pembayaran' => 'Lunas', 'metode_pembayaran' => 'tunai', 'status_gateway' => 'settlement',
        ]);

        $allocation = app(AlokasiPembayaranService::class)->alokasikan($order, $payment, 100000);
        $this->assertSame(100000.0, $allocation['pokok']);
        $this->assertSame(0.0, $allocation['denda']);

        $fresh = $order->fresh()->load(['pembayaran.alokasiDenda', 'penyesuaianDenda']);
        $summary = app(MonitoringKreditService::class)->hitung($fresh);
        $this->assertSame(200.0, $summary['total_denda_belum_dibayar']);
        $this->assertSame(0.0, $summary['jadwal'][0]['sisa_angsuran']);
    }

    public function test_only_pimpinan_can_create_a_reasoned_and_audited_reduction(): void
    {
        $order = $this->order();
        Carbon::setTestNow('2026-09-10 12:00:00');
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-STAFF', 'username' => 'staff_uji', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Uji', 'peran' => 'Staff',
        ]);
        $this->actingAs($staff, 'web')->post(route('monitoring-kredit.denda.reduce', $order), [
            'nomor_angsuran' => 1, 'nominal_pengurangan' => 50, 'alasan' => 'Pelanggan mengalami musibah',
        ])->assertForbidden();

        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-PIMPINAN', 'username' => 'pimpinan_uji', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Uji', 'peran' => 'Pimpinan',
        ]);

        $response = $this->actingAs($leader, 'web')->post(route('monitoring-kredit.denda.reduce', $order), [
            'nomor_angsuran' => 1, 'nominal_pengurangan' => 50, 'alasan' => 'Pelanggan mengalami musibah',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('penyesuaian_denda', [
            'id_pemesanan' => $order->id_pemesanan, 'denda_awal' => 200, 'nominal_pengurangan' => 50,
            'id_pimpinan' => $leader->id_pengguna,
        ]);
        $this->assertDatabaseHas('audit_trails', ['id_pengguna' => $leader->id_pengguna, 'jenis_aktivitas' => 'penyesuaian_denda']);
    }
}
