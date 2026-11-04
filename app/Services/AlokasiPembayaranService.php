<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\DendaAngsuran;
use Illuminate\Validation\ValidationException;

class AlokasiPembayaranService
{
    public function __construct(private readonly MonitoringKreditService $monitoring)
    {
    }

    /** Alokasi berurutan: sisa pokok angsuran lalu denda angsuran yang sama. */
    public function alokasikan(Pemesanan $pemesanan, Pembayaran $pembayaran, float $nominal): array
    {
        $jadwal = $this->monitoring->hitung($pemesanan)['jadwal'];
        $sisaPembayaran = round($nominal, 2);
        $pokokTerbayar = 0.0;
        $dendaTerbayar = 0.0;
        $alokasi = [];

        foreach ($jadwal as $angsuran) {
            if ($sisaPembayaran <= 0) break;
            $pokok = min($sisaPembayaran, (float) $angsuran['sisa_angsuran']);
            $sisaPembayaran = round($sisaPembayaran - $pokok, 2);
            if ($angsuran['sisa_angsuran'] > 0 && $pokok >= $angsuran['sisa_angsuran'] && $angsuran['denda_awal'] > 0) {
                DendaAngsuran::firstOrCreate(
                    ['id_pemesanan' => $pemesanan->id_pemesanan, 'nomor_angsuran' => $angsuran['nomor']],
                    ['denda_awal' => $angsuran['denda_awal'], 'ditetapkan_pada' => now(config('kredit.zona_waktu', 'Asia/Jakarta'))]
                );
            }
            $denda = min($sisaPembayaran, (float) $angsuran['denda_berjalan']);
            $sisaPembayaran = round($sisaPembayaran - $denda, 2);
            if ($pokok > 0 || $denda > 0) {
                $alokasi[] = ['nomor_angsuran' => $angsuran['nomor'], 'alokasi_angsuran' => $pokok, 'alokasi_denda' => $denda];
                $pokokTerbayar += $pokok;
                $dendaTerbayar += $denda;
            }
        }

        if ($sisaPembayaran > 0) {
            throw ValidationException::withMessages(['nominal' => 'Nominal melebihi sisa angsuran dan denda yang dapat dibayar.']);
        }

        foreach ($alokasi as $item) {
            AlokasiPembayaran::create(['id_pembayaran' => $pembayaran->id_pembayaran, ...$item]);
        }

        return ['pokok' => round($pokokTerbayar, 2), 'denda' => round($dendaTerbayar, 2), 'alokasi' => $alokasi];
    }
}
