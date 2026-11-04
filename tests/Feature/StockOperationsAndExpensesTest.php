<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOperationsAndExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_adds_only_approved_sellable_quantity_to_stock(): void
    {
        [$staff, $leader, $order, $product, $detail] = $this->stockRecords();
        $this->actingAs($staff)->post(route('retur.store'), [
            'id_pemesanan' => $order->id_pemesanan,
            'alasan' => 'Barang dikembalikan pelanggan',
            'items' => [['id_detail' => $detail->id_detail, 'jumlah' => 1, 'kondisi_barang' => 'Layak Dijual']],
        ])->assertRedirect(route('retur.index'));
        $return = \App\Models\ReturPelanggan::firstOrFail();

        $this->actingAs($leader)->post(route('retur.decide', $return), [
            'keputusan' => 'Disetujui', 'catatan_keputusan' => 'Barang diperiksa dan layak jual',
            'masuk_stok' => [$return->details()->first()->id_detail_retur => '1'],
        ])->assertRedirect();

        $this->assertSame(6, (int) $product->fresh()->stok);
        $this->assertDatabaseHas('detail_retur', ['id_retur' => $return->id_retur, 'masuk_stok' => true]);
        $this->assertDatabaseHas('pergerakan_stok', ['referensi_tipe' => 'ReturPelanggan', 'referensi_id' => $return->id_retur, 'jumlah_perubahan' => 1]);
    }

    public function test_stocktake_requires_leader_approval_and_audit_movement(): void
    {
        [$staff, $leader, , $product] = $this->stockRecords();
        $this->actingAs($staff)->post(route('stok-opname.store'), [
            'tanggal_opname' => now()->toDateString(), 'alasan' => 'Pemeriksaan stok berkala',
            'items' => [['id_barang' => $product->id_barang, 'stok_fisik' => 7]],
        ])->assertRedirect(route('stok-opname.index'));
        $opname = \App\Models\StokOpname::firstOrFail();

        $this->actingAs($leader)->post(route('stok-opname.decide', $opname), [
            'keputusan' => 'Disetujui', 'catatan_keputusan' => 'Hasil hitung sesuai bukti fisik',
        ])->assertRedirect();

        $this->assertSame(7, (int) $product->fresh()->stok);
        $this->assertDatabaseHas('pergerakan_stok', ['referensi_tipe' => 'StokOpname', 'referensi_id' => $opname->id_opname, 'jumlah_perubahan' => 2]);
    }

    public function test_damaged_return_cannot_be_added_to_inventory(): void
    {
        [$staff, $leader, $order, $product, $detail] = $this->stockRecords();
        $this->actingAs($staff)->post(route('retur.store'), [
            'id_pemesanan' => $order->id_pemesanan,
            'alasan' => 'Barang mengalami kerusakan',
            'items' => [['id_detail' => $detail->id_detail, 'jumlah' => 1, 'kondisi_barang' => 'Rusak']],
        ])->assertRedirect(route('retur.index'));
        $return = \App\Models\ReturPelanggan::firstOrFail();
        $detailReturn = $return->details()->firstOrFail();

        $this->actingAs($leader)->post(route('retur.decide', $return), [
            'keputusan' => 'Disetujui', 'catatan_keputusan' => 'Barang diterima dalam kondisi rusak',
            'masuk_stok' => [$detailReturn->id_detail_retur => '1'],
        ])->assertSessionHasErrors('masuk_stok');

        $this->assertSame(5, (int) $product->fresh()->stok);
        $this->assertSame('Menunggu Persetujuan', $return->fresh()->status);
    }

    public function test_stale_stocktake_cannot_apply_a_snapshot_after_stock_moved(): void
    {
        [$staff, $leader, , $product] = $this->stockRecords();
        $this->actingAs($staff)->post(route('stok-opname.store'), [
            'tanggal_opname' => now()->toDateString(), 'alasan' => 'Pemeriksaan stok berkala',
            'items' => [['id_barang' => $product->id_barang, 'stok_fisik' => 7]],
        ])->assertRedirect(route('stok-opname.index'));
        $opname = \App\Models\StokOpname::firstOrFail();
        $product->update(['stok' => 6]);

        $this->actingAs($leader)->post(route('stok-opname.decide', $opname), [
            'keputusan' => 'Disetujui', 'catatan_keputusan' => 'Persetujuan setelah pemeriksaan',
        ])->assertSessionHasErrors('keputusan');

        $this->assertSame(6, (int) $product->fresh()->stok);
        $this->assertSame('Menunggu Persetujuan', $opname->fresh()->status);
    }

    public function test_staff_can_record_expense_but_pimpinan_is_read_only(): void
    {
        [$staff, $leader] = $this->users();
        $this->actingAs($staff)->post(route('pengeluaran.store'), [
            'tanggal_pengeluaran' => now()->toDateString(), 'kategori' => 'Transportasi',
            'keterangan' => 'Pengiriman barang ke pelanggan', 'nominal' => 25000,
        ])->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseHas('pengeluaran_operasional', ['kategori' => 'Transportasi', 'nominal' => 25000, 'dicatat_oleh' => $staff->id_pengguna]);
        $this->actingAs($leader)->get(route('pengeluaran.create'))->assertForbidden();
    }

    private function stockRecords(): array
    {
        [$staff, $leader] = $this->users();
        $customer = Pelanggan::create(['id_pelanggan' => 'PLG-STOCK-TEST', 'nama_pelanggan' => 'Pelanggan Stok', 'nik' => '1122334455667788', 'no_telepon' => '081234567890', 'alamat' => 'Alamat uji']);
        $product = Barang::create(['id_barang' => 'BRG-STOCK-TEST', 'nama_barang' => 'Kulkas Uji', 'kategori' => 'Elektronik', 'harga' => 100000, 'stok' => 5]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-STOCK-TEST', 'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => now()->subDays(10)->toDateString(), 'skema_pembayaran' => 'Tunai',
            'dp' => 0, 'tenor' => 0, 'bunga' => 0, 'total_harga' => 200000, 'angsuran' => 0,
            'status_pemesanan' => 'Belum Lunas', 'status_persetujuan' => 'Disetujui', 'status_penyerahan' => 'Diserahkan',
            'diserahkan_oleh' => $staff->id_pengguna, 'diserahkan_pada' => now(),
        ]);
        $detail = DetailPemesanan::create(['id_detail' => 'DTL-STOCK-TEST', 'id_pemesanan' => $order->id_pemesanan, 'id_barang' => $product->id_barang, 'jumlah' => 2, 'harga' => 100000, 'subtotal' => 200000, 'stok_dikurangi' => true]);
        return [$staff, $leader, $order, $product, $detail];
    }

    private function users(): array
    {
        $staff = Pengguna::create(['id_pengguna' => 'PGN-STOCK-STAFF', 'username' => 'stock_staff', 'password' => bcrypt('secret'), 'nama_lengkap' => 'Staff Stok', 'peran' => 'Staff']);
        $leader = Pengguna::create(['id_pengguna' => 'PGN-STOCK-LEAD', 'username' => 'stock_leader', 'password' => bcrypt('secret'), 'nama_lengkap' => 'Pimpinan Stok', 'peran' => 'Pimpinan']);
        return [$staff, $leader];
    }
}
