<?php

namespace App\Services;

use App\Models\Pemesanan;
use Carbon\CarbonImmutable;

class MonitoringKreditService
{
    /**
     * Susun jadwal angsuran dari tanggal pemesanan dan alokasikan hanya
     * pembayaran yang sudah terverifikasi (status_gateway = settlement).
     */
    public function hitung(Pemesanan $pemesanan): array
    {
        $tenor = max(0, (int) $pemesanan->tenor);
        $totalKewajibanSen = max(0, (int) round((
            (float) $pemesanan->total_harga
            + (float) $pemesanan->bunga
            - (float) $pemesanan->dp
        ) * 100));
        $angsuranSen = max(0, (int) round((float) $pemesanan->angsuran * 100));
        $pembayaranTerverifikasi = $pemesanan->pembayaran->values();
        $totalTerbayarSen = (int) round((float) $pembayaranTerverifikasi->sum('nominal') * 100);
        $indeksPembayaran = 0;
        $sisaPembayaranSen = $pembayaranTerverifikasi->isNotEmpty()
            ? (int) round((float) $pembayaranTerverifikasi[0]->nominal * 100)
            : 0;
        $sisaKewajibanSen = max(0, $totalKewajibanSen - $totalTerbayarSen);

        $tanggalPesan = CarbonImmutable::parse($pemesanan->tanggal_pemesanan);
        // Due dates are evaluated using WIB, the local operating timezone for this BUMKam.
        $hariIni = CarbonImmutable::today('Asia/Jakarta');
        $jadwal = [];

        for ($nomor = 1; $nomor <= $tenor; $nomor++) {
            $nilaiSen = $nomor === $tenor
                ? max(0, $totalKewajibanSen - ($angsuranSen * ($tenor - 1)))
                : $angsuranSen;
            $tanggalJatuhTempo = $tanggalPesan->addMonthsNoOverflow($nomor);
            $terbayarSen = 0;
            $tanggalPembayaran = null;
            $idTransaksi = [];

            while ($terbayarSen < $nilaiSen && $indeksPembayaran < $pembayaranTerverifikasi->count()) {
                if ($sisaPembayaranSen <= 0) {
                    $indeksPembayaran++;
                    if ($indeksPembayaran >= $pembayaranTerverifikasi->count()) {
                        break;
                    }
                    $sisaPembayaranSen = (int) round((float) $pembayaranTerverifikasi[$indeksPembayaran]->nominal * 100);
                    continue;
                }

                $pembayaranAktif = $pembayaranTerverifikasi[$indeksPembayaran];
                $alokasiSen = min($nilaiSen - $terbayarSen, $sisaPembayaranSen);
                $terbayarSen += $alokasiSen;
                $sisaPembayaranSen -= $alokasiSen;
                $tanggalPembayaran = $pembayaranAktif->tanggal_pembayaran;
                $idTransaksi[] = $pembayaranAktif->id_pembayaran;
            }

            if ($nilaiSen === 0 || $terbayarSen >= $nilaiSen) {
                $status = 'Lunas';
            } elseif ($tanggalJatuhTempo->lt($hariIni)) {
                $status = 'Terlambat';
            } elseif ($tanggalJatuhTempo->isSameDay($hariIni)) {
                $status = 'Jatuh Tempo Hari Ini';
            } else {
                $status = 'Belum Jatuh Tempo';
            }

            $jadwal[] = [
                'nomor' => $nomor,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'nominal' => $nilaiSen / 100,
                'nominal_terbayar' => $terbayarSen / 100,
                'tanggal_pembayaran' => $tanggalPembayaran,
                'id_transaksi' => array_values(array_unique($idTransaksi)),
                'status' => $status,
            ];
        }

        $angsuranBelumLunas = collect($jadwal)->where('status', '!=', 'Lunas');
        $angsuranTerbayar = $tenor - $angsuranBelumLunas->count();
        $jadwalBelumLunas = $angsuranBelumLunas->first();

        if ($sisaKewajibanSen <= 0 || $tenor === 0) {
            $statusKredit = 'Lunas';
        } elseif (collect($jadwal)->contains('status', 'Terlambat')) {
            $statusKredit = 'Terlambat';
        } elseif (collect($jadwal)->contains('status', 'Jatuh Tempo Hari Ini')) {
            $statusKredit = 'Jatuh Tempo Hari Ini';
        } else {
            $statusKredit = 'Belum Jatuh Tempo';
        }

        return [
            'jadwal' => $jadwal,
            'total_kewajiban' => $totalKewajibanSen / 100,
            'total_terbayar' => $totalTerbayarSen / 100,
            'sisa_kewajiban' => $sisaKewajibanSen / 100,
            'angsuran_terbayar' => $angsuranTerbayar,
            'angsuran_belum_dibayar' => $tenor - $angsuranTerbayar,
            'angsuran_jatuh_tempo_hari_ini' => collect($jadwal)->where('status', 'Jatuh Tempo Hari Ini')->count(),
            'angsuran_terlambat' => collect($jadwal)->where('status', 'Terlambat')->count(),
            'tanggal_jatuh_tempo_berikutnya' => $jadwalBelumLunas['tanggal_jatuh_tempo'] ?? null,
            'status' => $statusKredit,
        ];
    }
}
