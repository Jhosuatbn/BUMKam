<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\PergerakanStok;
use Illuminate\Support\Str;

class StockMovementService
{
    public function apply(Barang $barang, int $delta, ?string $actorId, string $kind, ?string $referenceType = null, ?string $referenceId = null, ?string $note = null): void
    {
        if ($barang->stok === null) {
            throw new \InvalidArgumentException('Stok barang belum diatur.');
        }
        $before = (int) $barang->stok;
        $after = $before + $delta;
        if ($after < 0) {
            throw new \InvalidArgumentException('Stok tidak boleh kurang dari nol.');
        }
        $barang->update(['stok' => $after]);
        PergerakanStok::create([
            'id_pergerakan' => 'STK-' . Str::upper(Str::random(20)),
            'id_barang' => $barang->id_barang,
            'id_pengguna' => $actorId,
            'jenis' => $kind,
            'jumlah_perubahan' => $delta,
            'stok_sebelum' => $before,
            'stok_sesudah' => $after,
            'referensi_tipe' => $referenceType,
            'referensi_id' => $referenceId,
            'catatan' => $note,
            'created_at' => now(),
        ]);
    }
}
