<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanCreditScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_credit_receivable_and_fine_reports_exclude_unapproved_orders(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-REPORT-SCOPE', 'username' => 'report_scope_leader', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Laporan', 'peran' => 'Pimpinan',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-REPORT-SCOPE', 'nama_pelanggan' => 'Pelanggan Laporan', 'nik' => '2233445566778899',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji laporan kredit',
        ]);

        $this->createOrder($customer->id_pelanggan, 'APPROVED', 'Disetujui');
        $this->createOrder($customer->id_pelanggan, 'LEGACY', null);
        $this->createOrder($customer->id_pelanggan, 'PENDING', 'Menunggu Persetujuan');
        $this->createOrder($customer->id_pelanggan, 'REJECTED', 'Ditolak');
        $this->createOrder($customer->id_pelanggan, 'CANCELLED', 'Dibatalkan');

        $this->actingAs($leader)->get(route('laporan.index', ['tab' => 'kredit']))
            ->assertOk()
            ->assertViewHas('daftarKredit', fn ($orders) => $orders->count() === 2);

        $this->actingAs($leader)->get(route('laporan.index', ['tab' => 'piutang']))
            ->assertOk()
            ->assertViewHas('rows', fn ($orders) => $orders->count() === 2);

        $this->actingAs($leader)->get(route('laporan.index', ['tab' => 'denda']))
            ->assertOk()
            ->assertViewHas('rows', fn ($orders) => $orders->count() === 2);
    }

    private function createOrder(string $customerId, string $suffix, ?string $approval): void
    {
        Pemesanan::create([
            'id_pemesanan' => 'PSN-REPORT-'.$suffix,
            'id_pelanggan' => $customerId,
            'tanggal_pemesanan' => '2026-08-05',
            'skema_pembayaran' => 'Angsuran',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => 120000,
            'angsuran' => 10000,
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => $approval,
        ]);
    }
}
