<?php

namespace Tests\Feature;

use App\Models\AlokasiPembayaran;
use App\Models\AlokasiPembayaranPengembalian;
use App\Models\Jaminan;
use App\Models\MidtransRefundEvent;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JaminanPelunasanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_return_is_blocked_when_partial_refund_leaves_credit_balance(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        [$staff, $jaminan, $pemesanan] = $this->records();
        $payment = $this->payment($pemesanan, 100000, 'partial_refund', 10000);
        $allocation = AlokasiPembayaran::create([
            'id_pembayaran' => $payment->id_pembayaran,
            'nomor_angsuran' => 1,
            'alokasi_angsuran' => 100000,
            'alokasi_denda' => 0,
        ]);
        $event = MidtransRefundEvent::create([
            'id_pembayaran' => $payment->id_pembayaran,
            'event_key' => hash('sha256', 'jaminan-partial-refund'),
            'refund_provider_id' => 'RF-JAMINAN-01',
            'nominal_refund' => 10000,
            'nominal_refund_kumulatif' => 10000,
            'diterima_pada' => now(),
        ]);
        AlokasiPembayaranPengembalian::create([
            'id_midtrans_refund_event' => $event->id_midtrans_refund_event,
            'id_alokasi_pembayaran' => $allocation->id_alokasi_pembayaran,
            'id_pembayaran' => $payment->id_pembayaran,
            'nominal_pokok_dibalik' => 10000,
            'nominal_denda_dibalik' => 0,
        ]);

        $this->actingAs($staff)->put(route('jaminan.return', $jaminan), [
            'status_pengembalian' => 'Sudah Dikembalikan',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('jaminan', [
            'id_jaminan' => $jaminan->id_jaminan,
            'status_pengembalian' => 'Masih Disimpan',
        ]);
    }

    public function test_return_is_allowed_after_net_credit_obligation_is_paid(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        [$staff, $jaminan, $pemesanan] = $this->records();
        $this->payment($pemesanan, 100000, 'settlement');

        $this->actingAs($staff)->put(route('jaminan.return', $jaminan), [
            'status_pengembalian' => 'Sudah Dikembalikan',
        ])->assertRedirect();

        $this->assertDatabaseHas('jaminan', [
            'id_jaminan' => $jaminan->id_jaminan,
            'status_pengembalian' => 'Sudah Dikembalikan',
        ]);
    }

    public function test_detail_uses_the_same_net_balance_to_enable_return_action(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        [, $jaminan, $pemesanan] = $this->records();
        $this->payment($pemesanan, 100000, 'settlement');

        $this->actingAs(Pengguna::create([
            'id_pengguna' => 'PGN-JAM-LEAD',
            'username' => 'jaminan_lead',
            'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Uji',
            'peran' => 'Pimpinan',
        ]))->get(route('jaminan.show', $jaminan))
            ->assertOk()
            ->assertSee('Catat Pengembalian Dokumen');
    }

    private function records(): array
    {
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-JAM-STAFF',
            'username' => 'jaminan_staff',
            'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Uji',
            'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-JAM-TEST',
            'nama_pelanggan' => 'Pelanggan Uji',
            'nik' => '1234567890123456',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Uji',
        ]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-JAM-TEST',
            'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => '2026-10-05',
            'skema_pembayaran' => 'Angsuran',
            'dp' => 0,
            'tenor' => 1,
            'bunga' => 0,
            'total_harga' => 100000,
            'angsuran' => 100000,
            'status_pemesanan' => 'Belum Lunas',
        ]);
        $jaminan = Jaminan::create([
            'id_jaminan' => 'JMN-JAM-TEST',
            'id_pemesanan' => $order->id_pemesanan,
            'jenis_dokumen' => 'BPKB',
            'nomor_dokumen' => 'BPKB-TEST-01',
            'nama_pemilik' => 'Pelanggan Uji',
            'file_path' => null,
            'tanggal_penyerahan' => '2026-10-05',
            'status_verifikasi' => 'Disetujui',
            'status_pengembalian' => 'Masih Disimpan',
        ]);

        return [$staff, $jaminan, $order];
    }

    private function payment(Pemesanan $order, float $amount, string $gatewayStatus, float $refunded = 0): Pembayaran
    {
        return Pembayaran::create([
            'id_pembayaran' => 'BYR-JAM-'.strtoupper(substr($gatewayStatus, 0, 3)).'-'.($refunded > 0 ? 'REF' : 'OK'),
            'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => '2026-11-05',
            'nominal' => $amount,
            'nominal_dikembalikan' => $refunded,
            'total_pembayaran' => $amount - $refunded,
            'sisa_pembayaran' => 0,
            'status_pembayaran' => 'Lunas',
            'metode_pembayaran' => 'online',
            'status_gateway' => $gatewayStatus,
        ]);
    }
}
