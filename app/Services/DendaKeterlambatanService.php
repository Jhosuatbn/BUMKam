<?php

namespace App\Services;

use App\Models\Pemesanan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

class DendaKeterlambatanService
{
    public function tersedia(): bool
    {
        return Schema::hasTable('denda_angsuran')
            && Schema::hasTable('penyesuaian_denda')
            && Schema::hasTable('alokasi_pembayaran');
    }

    public function hitung(Pemesanan $pemesanan, array $jadwal): array
    {
        if (! $this->tersedia()) {
            foreach ($jadwal as &$angsuran) {
                $angsuran['sisa_angsuran'] = max(0, round($angsuran['nominal'] - $angsuran['nominal_terbayar'], 2));
                $angsuran['hari_keterlambatan'] = 0;
                $angsuran['denda_awal'] = 0.0;
                $angsuran['pengurangan_denda'] = 0.0;
                $angsuran['denda_dibayar'] = 0.0;
                $angsuran['denda_berjalan'] = 0.0;
            }
            unset($angsuran);

            return [
                'jadwal' => $jadwal,
                'total_denda_dikenakan' => 0.0,
                'total_denda_dibayar' => 0.0,
                'total_denda_belum_dibayar' => 0.0,
                'total_denda_dikurangi' => 0.0,
                'hari_terlambat_maksimum' => 0,
            ];
        }

        $timezone = config('kredit.zona_waktu', 'Asia/Jakarta');
        $graceDays = max(0, (int) config('kredit.denda.masa_tenggang_hari', 7));
        $dailyRate = max(0, (float) config('kredit.denda.tarif_harian', 0.001));
        $maximumRate = max(0, (float) config('kredit.denda.batas_maksimum', 0.05));
        $today = CarbonImmutable::today($timezone);
        $penyesuaian = $pemesanan->penyesuaianDenda
            ->groupBy('nomor_angsuran')
            ->map(fn ($items) => (float) $items->sum('nominal_pengurangan'));
        $dendaDitetapkan = $pemesanan->dendaAngsuran->keyBy('nomor_angsuran');
        $alokasiDenda = $pemesanan->pembayaran
            ->where('status_gateway', 'settlement')
            ->flatMap(fn ($payment) => $payment->alokasiDenda)
            ->groupBy('nomor_angsuran')
            ->map(fn ($items) => (float) $items->sum('alokasi_denda'));

        foreach ($jadwal as &$angsuran) {
            $sisaPokok = max(0, round($angsuran['nominal'] - $angsuran['nominal_terbayar'], 2));
            $due = $angsuran['tanggal_jatuh_tempo']->startOfDay();
            $graceEnds = $due->addDays($graceDays);
            $hariTerlambat = $sisaPokok > 0 && $today->gt($graceEnds)
                ? (int) $graceEnds->diffInDays($today)
                : 0;
            $dendaMaksimal = round($sisaPokok * $maximumRate, 2);
            $dendaAwalBerjalan = min($dendaMaksimal, round($sisaPokok * $dailyRate * $hariTerlambat, 2));
            $dendaAwal = $sisaPokok <= 0
                ? (float) ($dendaDitetapkan[$angsuran['nomor']]->denda_awal ?? 0)
                : $dendaAwalBerjalan;
            $pengurangan = min($dendaAwal, (float) ($penyesuaian[$angsuran['nomor']] ?? 0));
            $dibayar = min(max(0, $dendaAwal - $pengurangan), (float) ($alokasiDenda[$angsuran['nomor']] ?? 0));
            $angsuran['sisa_angsuran'] = $sisaPokok;
            $angsuran['hari_keterlambatan'] = $hariTerlambat;
            $angsuran['denda_awal'] = $dendaAwal;
            $angsuran['pengurangan_denda'] = $pengurangan;
            $angsuran['denda_dibayar'] = $dibayar;
            $angsuran['denda_berjalan'] = max(0, round($dendaAwal - $pengurangan - $dibayar, 2));
            $angsuran['status'] = $sisaPokok <= 0 && $angsuran['denda_berjalan'] <= 0
                ? 'Lunas'
                : ($hariTerlambat > 0 || $angsuran['denda_berjalan'] > 0 ? 'Terlambat' : ($today->gt($due) ? 'Dalam Masa Tenggang' : ($today->isSameDay($due) ? 'Jatuh Tempo Hari Ini' : 'Belum Jatuh Tempo')));
        }
        unset($angsuran);

        $totalDenda = round(array_sum(array_column($jadwal, 'denda_awal')), 2);
        $totalPengurangan = round(array_sum(array_column($jadwal, 'pengurangan_denda')), 2);
        $totalDendaDibayar = round(array_sum(array_column($jadwal, 'denda_dibayar')), 2);
        $totalDendaSisa = round(array_sum(array_column($jadwal, 'denda_berjalan')), 2);

        return [
            'jadwal' => $jadwal,
            'total_denda_dikenakan' => $totalDenda,
            'total_denda_dibayar' => $totalDendaDibayar,
            'total_denda_belum_dibayar' => $totalDendaSisa,
            'total_denda_dikurangi' => $totalPengurangan,
            'hari_terlambat_maksimum' => max(array_column($jadwal, 'hari_keterlambatan') ?: [0]),
        ];
    }
}
