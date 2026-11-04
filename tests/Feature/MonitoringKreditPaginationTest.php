<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringKreditPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_credit_list_chunks_calculation_and_keeps_summary_and_filtered_pagination(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-MON-STAFF', 'username' => 'monitoring_staff', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Monitoring', 'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-MON-PAGE', 'nama_pelanggan' => 'Pelanggan Monitoring', 'nik' => '9911223344556677',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji monitoring',
        ]);

        for ($index = 1; $index <= 17; $index++) {
            Pemesanan::create([
                'id_pemesanan' => 'PSN-MON-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'id_pelanggan' => $customer->id_pelanggan,
                'tanggal_pemesanan' => '2026-11-05',
                'skema_pembayaran' => 'Angsuran',
                'dp' => 0,
                'tenor' => 12,
                'bunga' => 0,
                'total_harga' => 120000,
                'angsuran' => 10000,
                'status_pemesanan' => 'Belum Lunas',
            ]);
        }

        $this->actingAs($staff)->get(route('monitoring-kredit.index', [
            'q' => 'Pelanggan Monitoring',
            'status' => 'Belum Jatuh Tempo',
            'page' => 2,
        ]))->assertOk()
            ->assertViewHas('ringkasan', function (array $summary) {
                return $summary['pelanggan_aktif'] === 1
                    && $summary['kredit_belum_lunas'] === 17
                    && $summary['total_piutang'] === 2040000.0;
            })
            ->assertViewHas('kredit', function ($credits) {
                return $credits->total() === 17
                    && $credits->currentPage() === 2
                    && $credits->count() === 2;
            });
    }

    public function test_credit_monitoring_excludes_unapproved_declined_and_cancelled_applications(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-MON-SCOPE', 'username' => 'monitoring_scope_staff', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Monitoring Scope', 'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-MON-SCOPE', 'nama_pelanggan' => 'Pelanggan Scope', 'nik' => '8877665544332211',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji scope',
        ]);

        $this->createCreditOrder($customer->id_pelanggan, 'APPROVED', 'Disetujui');
        $this->createCreditOrder($customer->id_pelanggan, 'LEGACY', null);
        $pending = $this->createCreditOrder($customer->id_pelanggan, 'PENDING', 'Menunggu Persetujuan');
        $this->createCreditOrder($customer->id_pelanggan, 'REJECTED', 'Ditolak');
        $this->createCreditOrder($customer->id_pelanggan, 'CANCELLED', 'Dibatalkan');

        $this->actingAs($staff)->get(route('monitoring-kredit.index'))
            ->assertOk()
            ->assertViewHas('kredit', fn ($credits) => $credits->total() === 2)
            ->assertViewHas('ringkasan', fn (array $summary) => $summary['kredit_belum_lunas'] === 2 && $summary['total_piutang'] === 200000.0);

        $this->actingAs($staff)->get(route('monitoring-kredit.show', $pending))->assertNotFound();

        $this->actingAs($staff)->get(route('monitoring-kredit.jadwal'))
            ->assertOk()
            ->assertViewHas('jadwal', fn ($schedule) => $schedule->count() === 24);

        $this->actingAs($staff)->get(route('monitoring-kredit.denda'))
            ->assertOk()
            ->assertViewHas('dataDenda', fn ($rows) => $rows->count() === 2);
    }

    private function createCreditOrder(string $customerId, string $suffix, ?string $approval): Pemesanan
    {
        return Pemesanan::create([
            'id_pemesanan' => 'PSN-SCOPE-'.$suffix,
            'id_pelanggan' => $customerId,
            'tanggal_pemesanan' => '2026-11-05',
            'skema_pembayaran' => 'Angsuran',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => 100000,
            'angsuran' => 100000 / 12,
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => $approval,
        ]);
    }
}
