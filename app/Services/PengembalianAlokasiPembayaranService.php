<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
use App\Models\AlokasiPembayaranPengembalian;
use App\Models\MidtransRefundEvent;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PengembalianAlokasiPembayaranService
{
    /** Refund dikembalikan dari alokasi terakhir; dalam satu angsuran denda dibalik sebelum pokok. */
    public function reversikan(Pembayaran $pembayaran, MidtransRefundEvent $event, float $nominal): void
    {
        $sisa = (int) round($nominal * 100);
        $alokasi = AlokasiPembayaran::where('id_pembayaran', $pembayaran->id_pembayaran)
            ->orderByDesc('nomor_angsuran')->orderByDesc('id_alokasi_pembayaran')->lockForUpdate()->get();
        if ($alokasi->isEmpty() && $sisa > 0) {
            throw ValidationException::withMessages(['pembayaran' => 'Refund belum dapat diterapkan karena rincian alokasi pembayaran tidak tersedia.']);
        }

        foreach ($alokasi as $item) {
            if ($sisa <= 0) break;
            $reversed = AlokasiPembayaranPengembalian::where('id_alokasi_pembayaran', $item->id_alokasi_pembayaran)->lockForUpdate()->get();
            $fineLeft = max(0, (int) round(((float) $item->alokasi_denda - (float) $reversed->sum('nominal_denda_dibalik')) * 100));
            $principalLeft = max(0, (int) round(((float) $item->alokasi_angsuran - (float) $reversed->sum('nominal_pokok_dibalik')) * 100));
            $fine = min($sisa, $fineLeft);
            $sisa -= $fine;
            $principal = min($sisa, $principalLeft);
            $sisa -= $principal;
            if ($fine || $principal) {
                AlokasiPembayaranPengembalian::create([
                    'id_midtrans_refund_event' => $event->id_midtrans_refund_event,
                    'id_alokasi_pembayaran' => $item->id_alokasi_pembayaran,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'nominal_pokok_dibalik' => $principal / 100,
                    'nominal_denda_dibalik' => $fine / 100,
                ]);
            }
        }
        if ($sisa > 0) {
            throw ValidationException::withMessages(['pembayaran' => 'Nominal refund melebihi sisa alokasi pembayaran yang belum dikembalikan.']);
        }
    }

    public function pokokEfektif(Pembayaran $pembayaran): float
    {
        if (! Schema::hasTable('alokasi_pembayaran')) return max(0, (float) $pembayaran->nominal - (float) ($pembayaran->nominal_dikembalikan ?? 0));
        if (! $pembayaran->relationLoaded('alokasiDenda')) $pembayaran->load('alokasiDenda');
        if ($pembayaran->alokasiDenda->isEmpty()) return max(0, (float) $pembayaran->nominal - (float) $pembayaran->nominal_dikembalikan);
        $principal = (float) $pembayaran->alokasiDenda->sum('alokasi_angsuran');
        $returned = Schema::hasTable('alokasi_pembayaran_pengembalian')
            ? (float) AlokasiPembayaranPengembalian::where('id_pembayaran', $pembayaran->id_pembayaran)->sum('nominal_pokok_dibalik')
            : 0.0;
        return max(0, round($principal - $returned, 2));
    }

    public function dendaEfektif(Pembayaran $pembayaran): float
    {
        if (! Schema::hasTable('alokasi_pembayaran')) return 0.0;
        if (! $pembayaran->relationLoaded('alokasiDenda')) $pembayaran->load('alokasiDenda');
        $fine = (float) $pembayaran->alokasiDenda->sum('alokasi_denda');
        $returned = Schema::hasTable('alokasi_pembayaran_pengembalian')
            ? (float) AlokasiPembayaranPengembalian::where('id_pembayaran', $pembayaran->id_pembayaran)->sum('nominal_denda_dibalik')
            : 0.0;
        return max(0, round($fine - $returned, 2));
    }
}
