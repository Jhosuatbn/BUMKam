<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Replace operational records with synthetic, presentation-ready demo data.
 * Existing login accounts are deliberately preserved.
 */
class DemoPresentationSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now('Asia/Jakarta');
        $today = $now->startOfDay();
        $staff = DB::table('pengguna')->where('peran', 'Staff')->orderBy('id_pengguna')->pluck('id_pengguna')->all();
        $leader = DB::table('pengguna')->where('peran', 'Pimpinan')->value('id_pengguna');

        if ($staff === [] || ! $leader) {
            throw new \RuntimeException('Akun Staff dan Pimpinan harus tersedia sebelum demo data dibuat.');
        }

        $tables = [
            'alokasi_pembayaran_pengembalian', 'midtrans_refund_events', 'pembayaran_reversal',
            'alokasi_pembayaran', 'penyesuaian_denda', 'denda_angsuran', 'pengingat_tagihan',
            'riwayat_persetujuan_pemesanan', 'jaminan', 'detail_retur', 'retur_pelanggan',
            'detail_stok_opname', 'stok_opname', 'detail_barang_masuk', 'barang_masuk',
            'pergerakan_stok', 'pengeluaran_operasional', 'pembayaran', 'detail_pemesanan',
            'pemesanan', 'barang', 'supplier', 'pelanggan', 'audit_trails',
        ];

        DB::transaction(function () use ($tables, $now, $today, $staff, $leader) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tables as $table) {
                DB::table($table)->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->seedCustomers($now);
            $goods = $this->seedGoods($now);
            $suppliers = $this->seedSuppliers($now);
            $stock = array_fill_keys(array_column($goods, 'id_barang'), 0);
            $this->seedPurchases($now, $today, $staff, $goods, $suppliers, $stock);
            $orders = $this->seedOrders($now, $today, $staff, $leader, $goods, $stock);
            $this->seedPayments($now, $orders);
            $this->seedGuarantees($now, $orders, $staff, $leader);
            $this->seedReturns($now, $today, $staff, $leader, $orders, $goods, $stock);
            $this->seedStocktakes($now, $today, $staff, $leader, $goods);
            $this->seedExpenses($now, $today, $staff);
            $this->seedReminders($now, $today, $orders);
            $this->seedAudit($now, $staff, $leader);

            foreach ($stock as $id => $quantity) {
                DB::table('barang')->where('id_barang', $id)->update(['stok' => $id === 'PRT03' ? null : $quantity, 'updated_at' => $now]);
            }
        });

        $this->command?->info('Data demo presentasi berhasil dibuat: 20 pelanggan, 15 barang, 15 supplier, 15 penerimaan, 30 pemesanan, 15 pembayaran, 15 retur, 15 stok opname, 15 pengeluaran, dan 45 audit trail.');
        $this->command?->warn('Seluruh nomor dan transaksi adalah sintetis; tidak ada pembayaran Midtrans atau pesan WhatsApp yang dikirim.');
    }

    private function seedCustomers(CarbonImmutable $now): void
    {
        $names = ['Siti Aminah','Budi Santoso','Rina Kurnia','Ahmad Fauzi','Dewi Lestari','Mimi Yuldiharti','Fikri Zakasih','Nadia Putri','Rizky Pratama','Wulan Sari','Advan Saputra','Fitri Anita','Dimas Ramadhan','Lina Marlina','Rendi Saputra','Yuni Kartika','Farhan Akbar','Maya Safitri','Hendra Wijaya','Putri Anggraini'];
        foreach ($names as $i => $name) {
            DB::table('pelanggan')->insert([
                'id_pelanggan' => sprintf('DEMO-PLG-%02d', $i + 1), 'nama_pelanggan' => 'Demo - ' . $name,
                'nik' => sprintf('9900000000%06d', $i + 1), 'no_telepon' => sprintf('0812345%06d', $i + 1),
                'wa_reminder_opt_in' => $i < 15, 'wa_reminder_opted_in_at' => $i < 15 ? $now->subDays(30)->toDateTimeString() : null,
                'alamat' => sprintf('Jl. Rantau Bertuah No. %d, Kampung Tuah Rezeki', $i + 1), 'foto_ktp' => null,
                'created_at' => $now->subDays(30 + $i)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function seedGoods(CarbonImmutable $now): array
    {
        $items = [
            ['ELE01','Kulkas Hemat Energi','Elektronik',3500000], ['ELE02','Mesin Cuci 1 Tabung','Elektronik',2450000],
            ['ELE03','Televisi LED 32 Inci','Elektronik',2150000], ['ELE04','Rice Cooker 1.8 L','Elektronik',425000],
            ['ELE05','Kipas Angin Berdiri','Elektronik',385000], ['FUR01','Kursi Kantor Ergonomis','Furniture',1250000],
            ['FUR02','Meja Belajar Kayu','Furniture',875000], ['FUR03','Lemari Pakaian 2 Pintu','Furniture',2000000],
            ['FUR04','Rak Serbaguna','Furniture',550000], ['FUR05','Kursi Plastik','Furniture',95000],
            ['KND01','Sepeda Motor Niaga','Kendaraan',18500000], ['KND02','Sepeda Anak','Kendaraan',1250000],
            ['PRT01','Pompa Air Sawah','Pertanian',850000], ['PRT02','Mesin Semprot Punggung','Pertanian',625000],
            ['PRT03','Selang Irigasi 30 m','Pertanian',275000],
        ];
        foreach ($items as [$id, $name, $category, $price]) {
            DB::table('barang')->insert(['id_barang' => $id, 'nama_barang' => 'Demo - ' . $name, 'kategori' => $category, 'harga' => $price, 'stok' => 0, 'created_at' => $now->subDays(40)->toDateTimeString(), 'updated_at' => $now->toDateTimeString()]);
        }
        return array_map(fn ($row) => ['id_barang' => $row[0], 'nama_barang' => 'Demo - ' . $row[1], 'kategori' => $row[2], 'harga' => $row[3]], $items);
    }

    private function seedSuppliers(CarbonImmutable $now): array
    {
        $rows = [];
        for ($i = 1; $i <= 15; $i++) {
            $id = sprintf('DEMO-SUP-%02d', $i);
            $rows[] = $id;
            DB::table('supplier')->insert([
                'id_supplier' => $id, 'nama_supplier' => sprintf('Demo Supplier %02d - %s', $i, ['Sumber Makmur','Berkah Niaga','Mitra Sejahtera'][$i % 3]),
                'kontak_person' => 'Kontak Demo ' . $i, 'no_telepon' => sprintf('0821000%06d', $i),
                'email' => sprintf('supplier%02d@example.test', $i), 'alamat' => 'Alamat pemasok contoh, Pekanbaru',
                'catatan' => 'Data sintetis untuk presentasi.', 'is_active' => true,
                'created_at' => $now->subDays(60)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
        }
        return $rows;
    }

    private function seedPurchases(CarbonImmutable $now, CarbonImmutable $today, array $staff, array $goods, array $suppliers, array &$stock): void
    {
        for ($i = 0; $i < 15; $i++) {
            $id = sprintf('DEMO-MSK-%03d', $i + 1);
            // One legacy item deliberately remains without a stock count so
            // the stock-monitoring screen can demonstrate "belum diatur".
            $goodsRow = $goods[$i % 14];
            $qty = $i >= 12 ? 4 + ($i - 12) : 18 + ($i % 8);
            $before = $stock[$goodsRow['id_barang']];
            $stock[$goodsRow['id_barang']] += $qty;
            $date = $today->subDays(2 + $i * 3);
            $created = $date->setTime(9 + ($i % 7), 10)->toDateTimeString();
            DB::table('barang_masuk')->insert(['id_barang_masuk' => $id, 'id_supplier' => $suppliers[$i], 'nomor_faktur' => sprintf('INV-DEMO-%04d', $i + 1), 'tanggal_masuk' => $date->toDateString(), 'diterima_oleh' => $staff[$i % count($staff)], 'catatan' => 'Penerimaan barang demo untuk kebutuhan operasional.', 'created_at' => $created, 'updated_at' => $created]);
            $subtotal = $qty * $goodsRow['harga'];
            DB::table('detail_barang_masuk')->insert(['id_detail_masuk' => sprintf('DEMO-DMS-%03d', $i + 1), 'id_barang_masuk' => $id, 'id_barang' => $goodsRow['id_barang'], 'jumlah' => $qty, 'harga_satuan' => $goodsRow['harga'], 'subtotal' => $subtotal, 'created_at' => $created, 'updated_at' => $created]);
            DB::table('pergerakan_stok')->insert(['id_pergerakan' => sprintf('DEMO-STK-IN-%03d', $i + 1), 'id_barang' => $goodsRow['id_barang'], 'id_pengguna' => $staff[$i % count($staff)], 'jenis' => 'Barang Masuk', 'jumlah_perubahan' => $qty, 'stok_sebelum' => $before, 'stok_sesudah' => $stock[$goodsRow['id_barang']], 'referensi_tipe' => 'BarangMasuk', 'referensi_id' => $id, 'catatan' => 'Penerimaan dari supplier demo.', 'created_at' => $created]);
        }
    }

    private function seedOrders(CarbonImmutable $now, CarbonImmutable $today, array $staff, string $leader, array $goods, array &$stock): array
    {
        $orders = [];
        $customerIds = range(1, 20);
        for ($i = 0; $i < 30; $i++) {
            $id = sprintf('DEMO-PSN-%03d', $i + 1);
            $approved = $i < 15;
            $decision = $approved ? 'Disetujui' : ($i < 23 ? 'Menunggu Persetujuan' : 'Ditolak');
            $date = $today->subDays(75 + $i * 9);
            $product = $goods[$i % 14];
            $quantity = 1 + ($i % 2);
            $price = (float) $product['harga'];
            $total = $price * $quantity;
            $dp = $i % 3 === 0 ? round($total * 0.1, 2) : 0;
            $interest = round($total * 0.15, 2);
            $tenor = [12, 24, 36][$i % 3];
            $installment = round(($total + $interest - $dp) / $tenor, 2);
            $orderCreated = $date->setTime(10 + ($i % 7), 15)->toDateTimeString();
            $handoff = $approved ? 'Diserahkan' : 'Belum Diserahkan';
            $statusOrder = $approved && $i % 5 === 0 ? 'Lunas' : 'Belum Lunas';
            $customerIndex = ($i % count($customerIds)) + 1;
            DB::table('pemesanan')->insert([
                'id_pemesanan' => $id, 'id_pelanggan' => sprintf('DEMO-PLG-%02d', $customerIndex),
                'tanggal_pemesanan' => $date->toDateString(), 'skema_pembayaran' => $dp > 0 ? 'DP' : 'Tanpa DP',
                'dp' => $dp, 'tenor' => $tenor, 'jaminan' => 'Dokumen demo', 'bunga' => $interest,
                'total_harga' => $total, 'angsuran' => $installment, 'status_pemesanan' => $statusOrder,
                'status_penyerahan' => $handoff, 'diserahkan_oleh' => $approved ? $staff[$i % count($staff)] : null,
                'diserahkan_pada' => $approved ? $date->addDays(1)->setTime(14, 0)->toDateTimeString() : null,
                'status_persetujuan' => $decision, 'persetujuan_oleh' => $decision === 'Menunggu Persetujuan' ? null : $leader,
                'persetujuan_pada' => $decision === 'Menunggu Persetujuan' ? null : $date->addDays(1)->setTime(11, 0)->toDateTimeString(),
                'catatan_persetujuan' => $decision === 'Ditolak' ? 'Dokumen simulasi belum memenuhi kelengkapan.' : ($approved ? 'Pengajuan demo disetujui.' : null),
                'created_at' => $orderCreated, 'updated_at' => $orderCreated,
            ]);
            $detailId = sprintf('DEMO-DTL-%03d', $i + 1);
            DB::table('detail_pemesanan')->insert([
                'id_detail' => $detailId, 'id_pemesanan' => $id, 'id_barang' => $product['id_barang'],
                'jumlah' => $quantity, 'harga' => $price, 'subtotal' => $total, 'stok_dikurangi' => $approved,
                'created_at' => $orderCreated, 'updated_at' => $orderCreated,
            ]);
            if ($approved) {
                $before = $stock[$product['id_barang']];
                $stock[$product['id_barang']] = max(0, $before - $quantity);
                DB::table('pergerakan_stok')->insert([
                    'id_pergerakan' => sprintf('DEMO-STK-OUT-%03d', $i + 1), 'id_barang' => $product['id_barang'],
                    'id_pengguna' => $staff[$i % count($staff)], 'jenis' => 'Barang Keluar', 'jumlah_perubahan' => -$quantity,
                    'stok_sebelum' => $before, 'stok_sesudah' => $stock[$product['id_barang']], 'referensi_tipe' => 'Pemesanan',
                    'referensi_id' => $id, 'catatan' => 'Penyerahan barang pemesanan demo.', 'created_at' => $orderCreated,
                ]);
            }
            DB::table('riwayat_persetujuan_pemesanan')->insert([
                'id_pemesanan' => $id, 'id_pengguna' => $decision === 'Menunggu Persetujuan' ? $staff[$i % count($staff)] : $leader,
                'status_persetujuan' => $decision, 'catatan' => 'Riwayat persetujuan demo.',
                'created_at' => $decision === 'Menunggu Persetujuan' ? $orderCreated : $date->addDays(1)->setTime(11, 0)->toDateTimeString(),
            ]);
            $orders[] = ['id' => $id, 'approved' => $approved, 'date' => $date, 'product' => $product, 'detail_id' => $detailId, 'qty' => $quantity, 'total' => $total, 'dp' => $dp, 'interest' => $interest, 'tenor' => $tenor, 'installment' => $installment, 'created' => $orderCreated, 'index' => $i];
        }
        return $orders;
    }

    private function seedPayments(CarbonImmutable $now, array $orders): void
    {
        $sequence = 1;
        foreach (array_slice($orders, 0, 15) as $order) {
            $paidInstallments = $order['index'] % 5 === 0 ? $order['tenor'] : 1 + ($order['index'] % 4);
            $obligation = $order['total'] + $order['interest'] - $order['dp'];
            $nominal = $order['index'] % 5 === 0 ? round($obligation, 2) : round($order['installment'] * $paidInstallments, 2);
            $balance = max(0, round($obligation - $nominal, 2));
            $id = sprintf('DEMO-BYR-%03d', $sequence++);
            $date = $order['date']->addMonthsNoOverflow($paidInstallments);
            $gateway = $order['index'] % 5 === 4 ? 'pending' : 'settlement';
            $method = $order['index'] % 5 === 4 ? 'online' : 'tunai';
            DB::table('pembayaran')->insert([
                'id_pembayaran' => $id, 'id_pemesanan' => $order['id'], 'tanggal_pembayaran' => $date->toDateString(),
                'nominal' => $nominal, 'nominal_dikembalikan' => 0, 'total_pembayaran' => $nominal,
                'sisa_pembayaran' => $balance, 'status_pembayaran' => $gateway === 'pending' ? 'Menunggu Pembayaran' : ($balance <= 0 ? 'Lunas' : 'Belum Lunas'),
                'metode_pembayaran' => $method, 'status_gateway' => $gateway, 'snap_token' => null,
                'gateway_redirect_url' => null, 'bukti_pembayaran' => null,
                'created_at' => $date->setTime(12, 0)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
            if ($gateway === 'settlement') {
                for ($n = 1; $n <= $paidInstallments; $n++) {
                    DB::table('alokasi_pembayaran')->insert([
                        'id_pembayaran' => $id, 'nomor_angsuran' => $n, 'alokasi_angsuran' => $order['installment'],
                        'alokasi_denda' => 0, 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString(),
                    ]);
                }
            }
        }
    }

    private function seedGuarantees(CarbonImmutable $now, array $orders, array $staff, string $leader): void
    {
        foreach ($orders as $i => $order) {
            $status = ['Disetujui', 'Menunggu Verifikasi', 'Ditolak'][$i % 3];
            DB::table('jaminan')->insert([
                'id_jaminan' => sprintf('DEMO-JMN-%03d', $i + 1), 'id_pemesanan' => $order['id'],
                'jenis_dokumen' => $i % 2 === 0 ? 'BPKB' : 'Surat Tanah', 'nomor_dokumen' => sprintf('DOK-DEMO-%06d', $i + 1),
                'nama_pemilik' => null, 'file_path' => null, 'tanggal_penyerahan' => $order['date']->toDateString(),
                'status_verifikasi' => $status, 'alasan_penolakan' => $status === 'Ditolak' ? 'Nomor dokumen simulasi perlu diperiksa.' : null,
                'catatan' => 'Metadata jaminan contoh; tidak ada dokumen sensitif.', 'status_pengembalian' => 'Masih Disimpan',
                'tanggal_pengembalian' => null, 'diterima_oleh' => $staff[$i % count($staff)],
                'diverifikasi_oleh' => $status === 'Menunggu Verifikasi' ? null : $leader,
                'created_at' => $order['created'], 'updated_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function seedReturns(CarbonImmutable $now, CarbonImmutable $today, array $staff, string $leader, array $orders, array $goods, array &$stock): void
    {
        foreach (array_slice($orders, 0, 15) as $i => $order) {
            $id = sprintf('DEMO-RTR-%03d', $i + 1);
            $status = ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'][$i % 3];
            $condition = $i % 4 === 0 ? 'Rusak' : 'Layak Dijual';
            $putBack = $status === 'Disetujui' && $condition === 'Layak Dijual';
            DB::table('retur_pelanggan')->insert([
                'id_retur' => $id, 'id_pemesanan' => $order['id'], 'dicatat_oleh' => $staff[$i % count($staff)],
                'status' => $status, 'alasan' => ['Kemasan rusak saat diterima.','Barang tidak sesuai kebutuhan.','Pemeriksaan kualitas demo.'][$i % 3],
                'diputuskan_oleh' => $status === 'Menunggu Persetujuan' ? null : $leader,
                'diputuskan_pada' => $status === 'Menunggu Persetujuan' ? null : $today->subDays($i)->setTime(13, 30)->toDateTimeString(),
                'catatan_keputusan' => $status === 'Menunggu Persetujuan' ? null : ($status === 'Disetujui' ? 'Retur demo diterima.' : 'Retur demo ditolak setelah pemeriksaan.'),
                'created_at' => $today->subDays(15 + $i)->setTime(10, 0)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
            DB::table('detail_retur')->insert([
                'id_detail_retur' => sprintf('DEMO-DRT-%03d', $i + 1), 'id_retur' => $id,
                'id_detail_pemesanan' => $order['detail_id'], 'id_barang' => $order['product']['id_barang'],
                'jumlah' => 1, 'kondisi_barang' => $condition, 'keterangan' => 'Detail barang retur demo.',
                'masuk_stok' => $putBack, 'created_at' => $today->subDays(15 + $i)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
            if ($putBack) {
                $productId = $order['product']['id_barang'];
                $before = $stock[$productId];
                $stock[$productId]++;
                DB::table('pergerakan_stok')->insert([
                    'id_pergerakan' => sprintf('DEMO-STK-RET-%03d', $i + 1), 'id_barang' => $productId,
                    'id_pengguna' => $leader, 'jenis' => 'Retur Pelanggan', 'jumlah_perubahan' => 1,
                    'stok_sebelum' => $before, 'stok_sesudah' => $stock[$productId], 'referensi_tipe' => 'ReturPelanggan',
                    'referensi_id' => $id, 'catatan' => 'Barang demo layak jual dikembalikan ke stok.',
                    'created_at' => $today->subDays($i)->toDateTimeString(),
                ]);
            }
        }
    }

    private function seedStocktakes(CarbonImmutable $now, CarbonImmutable $today, array $staff, string $leader, array $goods): void
    {
        for ($i = 0; $i < 15; $i++) {
            $id = sprintf('DEMO-OPN-%03d', $i + 1);
            $status = ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'][$i % 3];
            $date = $today->subDays($i + 1);
            DB::table('stok_opname')->insert([
                'id_opname' => $id, 'tanggal_opname' => $date->toDateString(), 'dicatat_oleh' => $staff[$i % count($staff)],
                'status' => $status, 'alasan' => 'Pemeriksaan persediaan berkala untuk demo.',
                'diputuskan_oleh' => $status === 'Menunggu Persetujuan' ? null : $leader,
                'diputuskan_pada' => $status === 'Menunggu Persetujuan' ? null : $date->addHours(3)->toDateTimeString(),
                'catatan_keputusan' => $status === 'Menunggu Persetujuan' ? null : ($status === 'Disetujui' ? 'Hasil opname diterima.' : 'Selisih perlu pemeriksaan ulang.'),
                'created_at' => $date->setTime(9, 0)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
            foreach (array_slice($goods, 0, 5) as $j => $product) {
                $system = 8 + (($i + $j) % 15);
                DB::table('detail_stok_opname')->insert([
                    'id_detail_opname' => sprintf('DEMO-DOP-%03d-%02d', $i + 1, $j + 1), 'id_opname' => $id,
                    'id_barang' => $product['id_barang'], 'stok_sistem' => $system,
                    'stok_fisik' => max(0, $system + (($i + $j) % 3 === 0 ? -1 : 0)),
                    'created_at' => $date->setTime(9, 0)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
                ]);
            }
        }
    }

    private function seedExpenses(CarbonImmutable $now, CarbonImmutable $today, array $staff): void
    {
        $categories = ['Transportasi','Perlengkapan','Listrik & Air','Kebersihan','Administrasi'];
        for ($i = 0; $i < 15; $i++) {
            $date = $today->subDays($i * 2);
            DB::table('pengeluaran_operasional')->insert([
                'id_pengeluaran' => sprintf('DEMO-PGL-%03d', $i + 1), 'tanggal_pengeluaran' => $date->toDateString(),
                'kategori' => $categories[$i % count($categories)], 'keterangan' => 'Pengeluaran operasional demo #' . ($i + 1),
                'nominal' => 75000 + ($i * 27500), 'dicatat_oleh' => $staff[$i % count($staff)],
                'created_at' => $date->setTime(15, 0)->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function seedReminders(CarbonImmutable $now, CarbonImmutable $today, array $orders): void
    {
        foreach (array_slice($orders, 0, 15) as $i => $order) {
            $due = $today->addDays(1 + ($i % 14));
            DB::table('pengingat_tagihan')->insert([
                'id_pemesanan' => $order['id'], 'nomor_angsuran' => 1 + ($i % $order['tenor']),
                'tanggal_jatuh_tempo' => $due->toDateString(), 'jenis' => 'demo', 'status' => 'pending',
                'pesan' => 'SIMULASI DEMO: pengingat angsuran; tidak dikirim ke WhatsApp.',
                'respons_gateway' => null, 'terkirim_pada' => null, 'created_at' => $now->toDateTimeString(), 'updated_at' => $now->toDateTimeString(),
            ]);
        }
    }

    private function seedAudit(CarbonImmutable $now, array $staff, string $leader): void
    {
        $modules = ['Pelanggan','Barang','Supplier','Barang Masuk','Pemesanan','Pembayaran','Retur Pelanggan','Stok Opname','Pengeluaran Operasional','Jaminan','Persetujuan Pemesanan','Monitoring Kredit','Laporan','Audit Trail','Login'];
        for ($i = 0; $i < 45; $i++) {
            $isLeader = $i % 5 === 0;
            $module = $modules[$i % count($modules)];
            DB::table('audit_trails')->insert([
                'id_pengguna' => $isLeader ? $leader : $staff[$i % count($staff)],
                'nama_pengguna' => $isLeader ? 'Pimpinan BUMKam' : 'Staff ' . (1 + ($i % count($staff))),
                'peran_pengguna' => $isLeader ? 'Pimpinan' : 'Staff',
                'jenis_aktivitas' => ['create','update','view','login','approval'][$i % 5], 'modul' => $module,
                'deskripsi' => 'Aktivitas demo ' . strtolower($module) . ' #' . ($i + 1) . ' untuk presentasi.',
                'ip_address' => '127.0.0.1', 'old_value' => null,
                'new_value' => json_encode(['data_demo' => true], JSON_UNESCAPED_UNICODE),
                'created_at' => $now->subHours($i * 3)->toDateTimeString(),
            ]);
        }
    }
}
