<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
