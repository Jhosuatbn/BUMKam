<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk demo dashboard. Seeder ini berdiri sendiri,
 * tidak dipanggil otomatis oleh DatabaseSeeder, dan aman dijalankan ulang.
 */
class DemoDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $pelangganDemo = $this->buatPelangganDemo();
        $barangDemo = $this->buatBarangDemo();
        $bulanAwal = CarbonImmutable::now()->startOfMonth()->subMonths(5);

        foreach (range(0, 5) as $indexBulan) {
            $bulanAktif = $bulanAwal->addMonths($indexBulan);
            $jumlahPemesananBulanIni = 1 + ($indexBulan % 3);
            $jumlahPemesananTahunLalu = 1 + (($indexBulan + 1) % 2);

            foreach ([
                [$bulanAktif, $jumlahPemesananBulanIni],
                [$bulanAktif->subYear(), $jumlahPemesananTahunLalu],
            ] as [$bulan, $jumlahPemesanan]) {
                for ($urutan = 1; $urutan <= $jumlahPemesanan; $urutan++) {
                    $tanggal = $bulan->day(min(8 + ($urutan * 5), $bulan->daysInMonth));
                    $idPeriode = $tanggal->format('ym');
                    $idPemesanan = sprintf('DEMO-PSN-%s-%02d', $idPeriode, $urutan);
                    $barisBarang = [];
                    $totalHarga = 0;

                    for ($baris = 0; $baris < 1 + ($urutan % 2); $baris++) {
                        $barang = $barangDemo[($indexBulan + $urutan + $baris) % count($barangDemo)];
                        $jumlah = 1 + (($indexBulan + $baris) % 3);
                        $harga = (float) $barang->harga;
                        $subtotal = round($harga * $jumlah, 2);
                        $totalHarga += $subtotal;
                        $barisBarang[] = compact('barang', 'jumlah', 'harga', 'subtotal');
                    }

                    $totalHarga = round($totalHarga, 2);
                    $skema = $urutan % 3 === 0 ? 'Sebagian' : 'Tanpa DP';
                    $dp = $skema === 'Sebagian' ? round($totalHarga * 0.1, 2) : 0;
                    $bunga = round($totalHarga * 0.15, 2);
                    $tagihan = round($totalHarga + $bunga - $dp, 2);
                    $tenor = [12, 24, 36][($indexBulan + $urutan) % 3];
                    $angsuran = round($tagihan / $tenor, 2);
                    $statusBayar = ($indexBulan + $urutan) % 3;
                    $jumlahDibayar = match ($statusBayar) {
                        0 => $tagihan,
                        1 => min($angsuran, $tagihan),
                        default => 0,
                    };
                    $statusPemesanan = $jumlahDibayar >= $tagihan ? 'Lunas' : 'Belum Lunas';

                    $pemesanan = Pemesanan::firstOrCreate(
                        ['id_pemesanan' => $idPemesanan],
                        [
                            'id_pelanggan' => $pelangganDemo[($indexBulan + $urutan) % count($pelangganDemo)]->id_pelanggan,
                            'tanggal_pemesanan' => $tanggal->toDateString(),
                            'skema_pembayaran' => $skema,
                            'dp' => $dp,
                            'tenor' => $tenor,
                            'jaminan' => null,
                            'bunga' => $bunga,
                            'total_harga' => $totalHarga,
                            'angsuran' => $angsuran,
                            'status_pemesanan' => $statusPemesanan,
                        ]
                    );

                    foreach ($barisBarang as $indexBaris => $item) {
                        $idDetail = sprintf('DEMO-DTL-%s-%02d-%d', $idPeriode, $urutan, $indexBaris + 1);
                        DetailPemesanan::firstOrCreate(
                            ['id_detail' => $idDetail],
                            [
                                'id_pemesanan' => $pemesanan->id_pemesanan,
                                'id_barang' => $item['barang']->id_barang,
                                'jumlah' => $item['jumlah'],
                                'harga' => $item['harga'],
                                'subtotal' => $item['subtotal'],
                                'stok_dikurangi' => false,
                            ]
                        );
                    }

                    if ($jumlahDibayar > 0) {
                        $idPembayaran = sprintf('DEMO-BYR-%s-%02d', $idPeriode, $urutan);
                        $sisa = round(max(0, $tagihan - $jumlahDibayar), 2);
                        Pembayaran::firstOrCreate(
                            ['id_pembayaran' => $idPembayaran],
                            [
                                'id_pemesanan' => $pemesanan->id_pemesanan,
                                'tanggal_pembayaran' => $tanggal->addMonthNoOverflow()->toDateString(),
                                'nominal' => $jumlahDibayar,
                                'total_pembayaran' => $jumlahDibayar,
                                'sisa_pembayaran' => $sisa,
                                'status_pembayaran' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
                                'bukti_pembayaran' => null,
                            ]
                        );
                    }
                }
            }
        }
    }

    /** @return array<int, Pelanggan> */
    private function buatPelangganDemo(): array
    {
        $namaPelanggan = [
            'Demo - Siti Aminah',
            'Demo - Budi Santoso',
            'Demo - Rina Kurnia',
            'Demo - Ahmad Fauzi',
            'Demo - Dewi Lestari',
        ];

        return collect($namaPelanggan)->map(function ($nama, $index) {
            $pelanggan = Pelanggan::where('nama_pelanggan', $nama)->first();
            if ($pelanggan) {
                return $pelanggan;
            }

            return Pelanggan::create([
                'id_pelanggan' => Pelanggan::generateId(),
                'nama_pelanggan' => $nama,
                'nik' => sprintf('990000000000%04d', $index + 1),
                'no_telepon' => sprintf('081234560%04d', $index + 1),
                'alamat' => 'Alamat contoh untuk data demo BUMKam.',
                'foto_ktp' => null,
            ]);
        })->all();
    }

    /** @return array<int, Barang> */
    private function buatBarangDemo(): array
    {
        $barang = [
            ['nama' => 'Demo - Laptop Usaha', 'kategori' => 'Elektronik', 'harga' => 6500000, 'stok' => 12],
            ['nama' => 'Demo - Kursi Kantor', 'kategori' => 'Furniture', 'harga' => 1250000, 'stok' => 18],
            ['nama' => 'Demo - Pompa Air', 'kategori' => 'Pertanian', 'harga' => 850000, 'stok' => 0],
            ['nama' => 'Demo - Sepeda Motor', 'kategori' => 'Kendaraan', 'harga' => 18500000, 'stok' => 7],
        ];
        $prefixKategori = [
            'Elektronik' => 'ELE',
            'Pertanian' => 'PRT',
            'Furniture' => 'FUR',
            'Kendaraan' => 'KND',
        ];

        return collect($barang)->map(function ($data) use ($prefixKategori) {
            $existing = Barang::where('nama_barang', $data['nama'])->first();
            if ($existing) {
                return $existing;
            }

            $prefix = $prefixKategori[$data['kategori']];
            $nomorTerakhir = Barang::where('id_barang', 'like', $prefix . '%')
                ->selectRaw('MAX(CAST(SUBSTRING(id_barang, 4) AS UNSIGNED)) as nomor_terakhir')
                ->value('nomor_terakhir');

            return Barang::create([
                'id_barang' => $prefix . str_pad((string) (((int) $nomorTerakhir) + 1), 2, '0', STR_PAD_LEFT),
                'nama_barang' => $data['nama'],
                'kategori' => $data['kategori'],
                'harga' => $data['harga'],
                'stok' => $data['stok'],
            ]);
        })->all();
    }
}
