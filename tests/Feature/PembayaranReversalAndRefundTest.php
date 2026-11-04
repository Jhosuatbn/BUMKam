<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use App\Services\MonitoringKreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranReversalAndRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_pimpinan_can_reverse_cash_settlement_and_history_is_kept(): void
    {
        [$staff, $leader, $order] = $this->records();
        $payment = $this->payment($order, 'tunai', 'settlement', 100000);

        $this->actingAs($staff)->post(route('pembayaran.reverse', $payment), ['alasan' => 'Kesalahan pencatatan'])
            ->assertForbidden();
        $this->actingAs($leader)->post(route('pembayaran.reverse', $payment), ['alasan' => 'Nominal salah dicatat'])
            ->assertRedirect(route('pembayaran.show', $payment));

        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $payment->id_pembayaran,
            'status_gateway' => 'reversed',
            'nominal' => 100000,
        ]);
        $this->assertDatabaseHas('pembayaran_reversal', [
            'id_pembayaran' => $payment->id_pembayaran,
            'nominal' => 100000,
            'alasan' => 'Nominal salah dicatat',
            'id_pengguna' => $leader->id_pengguna,
        ]);
        $summary = app(MonitoringKreditService::class)->hitung($order->fresh());
        $this->assertSame(0.0, $summary['jadwal'][0]['nominal_terbayar']);

        $this->actingAs($staff)->delete(route('pembayaran.destroy', $payment))->assertForbidden();
        $this->assertDatabaseHas('pembayaran', ['id_pembayaran' => $payment->id_pembayaran]);
    }

    public function test_payment_receipt_shows_the_net_amount_after_refund(): void
    {
        [$staff, , $order] = $this->records();
        $payment = $this->payment($order, 'online', 'partial_refund', 100000);
        $payment->update(['nominal_dikembalikan' => 25000]);

        $this->actingAs($staff)->get(route('pembayaran.receipt', $payment))
            ->assertOk()
            ->assertSee('Dana bersih setelah refund')
            ->assertSee('75.000');

        $payment->update(['status_gateway' => 'pending']);
        $this->get(route('pembayaran.receipt', $payment))->assertNotFound();
    }

    public function test_signed_midtrans_partial_refund_reverses_latest_allocation_once(): void
    {
        [, , $order] = $this->records();
        $payment = $this->payment($order, 'online', 'settlement', 100000);
        AlokasiPembayaran::create([
            'id_pembayaran' => $payment->id_pembayaran,
            'nomor_angsuran' => 1,
            'alokasi_angsuran' => 80000,
            'alokasi_denda' => 20000,
        ]);
        $serverKey = 'midtrans-test-server-key';
        config()->set('services.midtrans.server_key', $serverKey);
        $payload = [
            'order_id' => $payment->id_pembayaran,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'partial_refund',
            'refund_amount' => '25000.00',
            'refund_chargeback_id' => 'RF-001',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        $this->postJson(route('pembayaran.midtrans.notification'), $payload)->assertOk();
        $this->postJson(route('pembayaran.midtrans.notification'), $payload)->assertOk();

        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $payment->id_pembayaran,
            'status_gateway' => 'partial_refund',
            'nominal_dikembalikan' => 25000,
        ]);
        $this->assertDatabaseCount('midtrans_refund_events', 1);
        $this->assertDatabaseCount('alokasi_pembayaran_pengembalian', 1);
        $this->assertDatabaseHas('alokasi_pembayaran_pengembalian', [
            'id_pembayaran' => $payment->id_pembayaran,
            'nominal_pokok_dibalik' => 5000,
            'nominal_denda_dibalik' => 20000,
        ]);
        $summary = app(MonitoringKreditService::class)->hitung($order->fresh());
        $this->assertSame(75000.0, $summary['jadwal'][0]['nominal_terbayar']);

        $payload['transaction_status'] = 'refund';
        $payload['refund_amount'] = '100000.00';
        $payload['refund_chargeback_id'] = 'RF-002';
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);
        $this->postJson(route('pembayaran.midtrans.notification'), $payload)->assertOk();
        $this->postJson(route('pembayaran.midtrans.notification'), $payload)->assertOk();

        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $payment->id_pembayaran,
            'status_gateway' => 'refund',
            'nominal_dikembalikan' => 100000,
        ]);
        $this->assertDatabaseCount('midtrans_refund_events', 2);
        $this->assertDatabaseCount('alokasi_pembayaran_pengembalian', 2);
        $summary = app(MonitoringKreditService::class)->hitung($order->fresh());
        $this->assertSame(0.0, $summary['jadwal'][0]['nominal_terbayar']);
    }

    private function records(): array
    {
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-PAY-STAFF', 'username' => 'staff_payment', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Pembayaran', 'peran' => 'Staff',
        ]);
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-PAY-LEAD', 'username' => 'leader_payment', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Pembayaran', 'peran' => 'Pimpinan',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-PAY-TEST', 'nama_pelanggan' => 'Pelanggan Pembayaran',
            'nik' => '8877665544332211', 'no_telepon' => '081234567890', 'alamat' => 'Alamat uji',
        ]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-PAY-TEST', 'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => now()->subMonthNoOverflow()->toDateString(),
            'skema_pembayaran' => 'Tanpa DP', 'dp' => 0, 'tenor' => 1, 'bunga' => 0,
            'total_harga' => 100000, 'angsuran' => 100000, 'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => 'Disetujui', 'status_penyerahan' => 'Diserahkan',
        ]);

        return [$staff, $leader, $order];
    }

    private function payment(Pemesanan $order, string $method, string $gatewayStatus, int $amount): Pembayaran
    {
        return Pembayaran::create([
            'id_pembayaran' => $method === 'tunai' ? 'BYR-PAY-CASH' : 'BYR-PAY-ONLINE',
            'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal' => $amount,
            'total_pembayaran' => 0,
            'sisa_pembayaran' => 100000,
            'status_pembayaran' => 'Belum Lunas',
            'metode_pembayaran' => $method,
            'status_gateway' => $gatewayStatus,
        ]);
    }
}
