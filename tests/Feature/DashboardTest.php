<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_database_backed_visualizations(): void
    {
        $user = Pengguna::create([
            'id_pengguna' => 'PGN-TEST',
            'username' => 'dashboard_test',
            'password' => bcrypt('admin123'),
            'nama_lengkap' => 'Test Admin',
            'peran' => 'Pimpinan',
        ]);

        $pelanggan = Pelanggan::create([
            'id_pelanggan' => 'PLG-TEST',
            'nama_pelanggan' => 'Pelanggan Uji',
            'nik' => '1234567890123456',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Uji',
        ]);

        $kulkas = Barang::create([
            'id_barang' => 'BRG-KULKAS',
            'nama_barang' => 'Kulkas',
            'kategori' => 'Elektronik',
            'harga' => 5000000,
        ]);

        $televisi = Barang::create([
            'id_barang' => 'BRG-TV',
            'nama_barang' => 'TV',
            'kategori' => 'Elektronik',
            'harga' => 3000000,
        ]);

        $pemesananPertama = Pemesanan::create([
            'id_pemesanan' => 'PM-TEST-01',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'DP',
            'dp' => 1000000,
            'tenor' => 6,
            'jaminan' => null,
            'bunga' => 0,
            'total_harga' => 5000000,
            'angsuran' => 666667,
            'status_pemesanan' => 'Belum Lunas',
        ]);

        $pemesananKedua = Pemesanan::create([
            'id_pemesanan' => 'PM-TEST-02',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'Tanpa DP',
            'dp' => 0,
            'tenor' => 6,
            'jaminan' => null,
            'bunga' => 0,
            'total_harga' => 3000000,
            'angsuran' => 500000,
            'status_pemesanan' => 'Lunas',
        ]);

        DetailPemesanan::create([
            'id_detail' => 'DT-TEST-01',
            'id_pemesanan' => $pemesananPertama->id_pemesanan,
            'id_barang' => $kulkas->id_barang,
            'jumlah' => 3,
            'harga' => 5000000,
            'subtotal' => 15000000,
        ]);

        DetailPemesanan::create([
            'id_detail' => 'DT-TEST-02',
            'id_pemesanan' => $pemesananKedua->id_pemesanan,
            'id_barang' => $kulkas->id_barang,
            'jumlah' => 2,
            'harga' => 5000000,
            'subtotal' => 10000000,
        ]);

        DetailPemesanan::create([
            'id_detail' => 'DT-TEST-03',
            'id_pemesanan' => $pemesananKedua->id_pemesanan,
            'id_barang' => $televisi->id_barang,
            'jumlah' => 1,
            'harga' => 3000000,
            'subtotal' => 3000000,
        ]);

        Pembayaran::create([
            'id_pembayaran' => 'BY-TEST-01',
            'id_pemesanan' => $pemesananPertama->id_pemesanan,
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal' => 1000000,
            'total_pembayaran' => 5000000,
            'sisa_pembayaran' => 4000000,
            'status_pembayaran' => 'Belum Lunas',
        ]);

        Pembayaran::create([
            'id_pembayaran' => 'BY-TEST-02',
            'id_pemesanan' => $pemesananKedua->id_pemesanan,
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal' => 3000000,
            'total_pembayaran' => 3000000,
            'sisa_pembayaran' => 0,
            'status_pembayaran' => 'Lunas',
        ]);

        $monthLabel = now()->locale('id')->translatedFormat('M');

        $response = $this->actingAs($user, 'web')->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Tren Pemesanan')
            ->assertSee($monthLabel.': 2 pemesanan')
            ->assertSee('Status Pembayaran')
            ->assertSee('1 lunas dan 1 belum lunas.')
            ->assertSee('Barang Paling Banyak Dipesan')
            ->assertSee('Kulkas')
            ->assertDontSee('Akses Cepat');
    }
}
