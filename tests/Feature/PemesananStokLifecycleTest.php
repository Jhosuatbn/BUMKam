<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use App\Services\InventoryAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemesananStokLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_order_reserves_stock_and_handover_reduces_physical_stock_once(): void
    {
        [$staff, $leader, $barang, $order] = $this->makeOrder();

        $this->actingAs($leader)->put(route('pemesanan.approve', $order), [
            'status_persetujuan' => 'Disetujui',
        ])->assertRedirect(route('pemesanan.show', $order));

        $barang->refresh();
        $this->assertSame(10, (int) $barang->stok);
        $this->assertSame(6, app(InventoryAvailabilityService::class)->availableQuantity($barang));

        $this->actingAs($staff)->post(route('pemesanan.handover', $order))
            ->assertRedirect(route('pemesanan.show', $order));
        $this->assertSame(6, (int) $barang->fresh()->stok);
        $this->assertSame('Diserahkan', $order->fresh()->status_penyerahan);
        $this->assertDatabaseCount('pergerakan_stok', 1);

        $this->actingAs($staff)->post(route('pemesanan.handover', $order))
            ->assertSessionHasErrors('status_penyerahan');
        $this->assertDatabaseCount('pergerakan_stok', 1);
        $this->assertSame(6, (int) $barang->fresh()->stok);
    }

    public function test_pending_order_does_not_reduce_physical_stock_and_can_be_cancelled(): void
    {
        [$staff, , $barang, $order] = $this->makeOrder();

        $this->assertSame(10, (int) $barang->fresh()->stok);
        $this->assertDatabaseCount('pergerakan_stok', 0);

        $this->actingAs($staff)->post(route('pemesanan.cancel', $order), ['alasan_pembatalan' => 'Pelanggan mengubah rencana pembelian.'])
            ->assertRedirect(route('pemesanan.show', $order));
        $this->assertSame('Dibatalkan', $order->fresh()->status_persetujuan);
        $this->assertSame(10, (int) $barang->fresh()->stok);
        $this->assertDatabaseCount('pergerakan_stok', 0);
    }

    public function test_approval_is_rejected_if_available_stock_was_reserved_by_another_order(): void
    {
        [, $leader, $barang, $order] = $this->makeOrder();
        $other = Pemesanan::create([
            'id_pemesanan' => 'PSN-RESERVED-01',
            'id_pelanggan' => $order->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'Tanpa DP',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => 4,
            'angsuran' => 1,
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => 'Disetujui',
            'status_penyerahan' => 'Belum Diserahkan',
        ]);
        DetailPemesanan::create([
            'id_detail' => 'DTL-RESERVED-01',
            'id_pemesanan' => $other->id_pemesanan,
            'id_barang' => $barang->id_barang,
            'jumlah' => 7,
            'harga' => 1,
            'subtotal' => 7,
            'stok_dikurangi' => false,
        ]);

        $this->actingAs($leader)->put(route('pemesanan.approve', $order), [
            'status_persetujuan' => 'Disetujui',
        ])->assertSessionHasErrors('status_persetujuan');
        $this->assertSame('Menunggu Persetujuan', $order->fresh()->status_persetujuan);
        $this->assertSame(10, (int) $barang->fresh()->stok);
    }

    private function makeOrder(): array
    {
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-STOCK-01',
            'username' => 'staff_stock',
            'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Stok',
            'peran' => 'Staff',
        ]);
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-STOCK-02',
            'username' => 'leader_stock',
            'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Stok',
            'peran' => 'Pimpinan',
        ]);
        $pelanggan = Pelanggan::create([
            'id_pelanggan' => 'PLG-STOCK-01',
            'nama_pelanggan' => 'Pelanggan Stok',
            'nik' => '9911223344556677',
            'no_telepon' => '081234567891',
            'alamat' => 'Alamat uji',
        ]);
        $barang = Barang::create([
            'id_barang' => 'BRG-STOCK-01',
            'nama_barang' => 'Barang Uji',
            'kategori' => 'Elektronik',
            'harga' => 1,
            'stok' => 10,
        ]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-STOCK-01',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(),
            'skema_pembayaran' => 'Tanpa DP',
            'dp' => 0,
            'tenor' => 12,
            'bunga' => 0,
            'total_harga' => 4,
            'angsuran' => 1,
            'status_pemesanan' => 'Belum Lunas',
            'status_persetujuan' => 'Menunggu Persetujuan',
            'status_penyerahan' => 'Belum Diserahkan',
        ]);
        DetailPemesanan::create([
            'id_detail' => 'DTL-STOCK-01',
            'id_pemesanan' => $order->id_pemesanan,
            'id_barang' => $barang->id_barang,
            'jumlah' => 4,
            'harga' => 1,
            'subtotal' => 4,
            'stok_dikurangi' => false,
        ]);

        return [$staff, $leader, $barang, $order];
    }
}
