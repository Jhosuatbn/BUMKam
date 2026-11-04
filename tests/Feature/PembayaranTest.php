<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_id_and_installment_amount_are_generated_from_the_order(): void
    {
        $user = Pengguna::create([
            'id_pengguna' => 'PGN-ADMIN',
            'username' => 'admin_pembayaran',
            'password' => bcrypt('admin123'),
            'nama_lengkap' => 'Admin Pembayaran',
            'peran' => 'Admin',
        ]);

        $pelanggan = Pelanggan::create([
            'id_pelanggan' => 'PLG-TEST',
            'nama_pelanggan' => 'Pelanggan Uji',
            'nik' => '1234567890123456',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Uji',
        ]);

        $pemesanan = Pemesanan::create([
            'id_pemesanan' => 'PSN-TEST-01',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'DP',
            'dp' => 100000,
            'tenor' => 3,
            'jaminan' => null,
            'bunga' => 150000,
            'total_harga' => 1000000,
            'angsuran' => 350000,
            'status_pemesanan' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($user, 'web')
            ->post(route('pembayaran.store'), [
                'id_pembayaran' => 'ID-YANG-DIKIRIM-USER',
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => now()->toDateString(),
                'nominal' => 1,
            ]);

        $response->assertRedirect(route('pembayaran.index'));
        $this->assertDatabaseCount('pembayaran', 1);

        $pembayaran = Pembayaran::firstOrFail();
        $this->assertMatchesRegularExpression('/^BYR-\d{6}-01$/', $pembayaran->id_pembayaran);
        $this->assertSame(350000.0, (float) $pembayaran->nominal);
        $this->assertSame(350000.0, (float) $pembayaran->total_pembayaran);
        $this->assertSame(700000.0, (float) $pembayaran->sisa_pembayaran);
        $this->assertSame('Belum Lunas', $pemesanan->fresh()->status_pemesanan);
    }

    public function test_create_form_shows_a_generated_payment_id_and_searchable_orders(): void
    {
        $user = Pengguna::create([
            'id_pengguna' => 'PGN-ADMIN',
            'username' => 'admin_form_pembayaran',
            'password' => bcrypt('admin123'),
            'nama_lengkap' => 'Admin Pembayaran',
            'peran' => 'Admin',
        ]);

        $pelanggan = Pelanggan::create([
            'id_pelanggan' => 'PLG-TEST',
            'nama_pelanggan' => 'Pelanggan Uji',
            'nik' => '1234567890123456',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Uji',
        ]);

        Pemesanan::create([
            'id_pemesanan' => 'PSN-TEST-01',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'Tanpa DP',
            'dp' => 0,
            'tenor' => 2,
            'jaminan' => null,
            'bunga' => 0,
            'total_harga' => 1000000,
            'angsuran' => 500000,
            'status_pemesanan' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($user, 'web')->get(route('pembayaran.create'));

        $response->assertOk()
            ->assertSee('ID dibuat otomatis saat pembayaran disimpan.')
            ->assertSee('Cari ID pemesanan atau nama pelanggan...')
            ->assertSee('Pelanggan Uji')
            ->assertSee('data-installment-select', false);
    }

    public function test_uploaded_proof_is_removed_when_payment_transaction_fails(): void
    {
        Storage::fake('local');
        $user = Pengguna::create([
            'id_pengguna' => 'PGN-PAY-FAIL', 'username' => 'staff_payment_fail', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Pembayaran', 'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-PAY-FAIL', 'nama_pelanggan' => 'Pelanggan Uji', 'nik' => '9876543210987654',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat Uji',
        ]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-PAY-FAIL', 'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(), 'skema_pembayaran' => 'Angsuran', 'dp' => 0,
            'tenor' => 3, 'bunga' => 0, 'total_harga' => 900000, 'angsuran' => 300000,
            'status_pemesanan' => 'Belum Lunas',
        ]);

        $this->actingAs($user)->from(route('pembayaran.create'))->post(route('pembayaran.store'), [
            'id_pemesanan' => $order->id_pemesanan,
            'tanggal_pembayaran' => now()->toDateString(),
            'metode_pembayaran' => 'tunai',
            'nominal' => 999999999,
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('pembayaran.create'))
            ->assertSessionHasErrors('nominal');

        $this->assertDatabaseCount('pembayaran', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('bukti-pembayaran'));
    }
}
