<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryAvailabilityService
{
    /** Quantity physically reserved by approved, not-yet-handed-over orders. */
    public function reservedQuantity(string $barangId): int
    {
        return (int) DB::table('detail_pemesanan as detail')
            ->join('pemesanan as orders', 'orders.id_pemesanan', '=', 'detail.id_pemesanan')
            ->where('detail.id_barang', $barangId)
            ->where('detail.stok_dikurangi', false)
            ->where('orders.status_persetujuan', 'Disetujui')
            ->where('orders.status_penyerahan', 'Belum Diserahkan')
            ->sum('detail.jumlah');
    }

    public function availableQuantity(Barang $barang): ?int
    {
        if ($barang->stok === null) {
            return null;
        }

        return max(0, (int) $barang->stok - $this->reservedQuantity($barang->id_barang));
    }

    /** @param Collection<int, Barang> $barang */
    public function withAvailability(Collection $barang): Collection
    {
        $ids = $barang->pluck('id_barang')->all();
        $reserved = DB::table('detail_pemesanan as detail')
            ->join('pemesanan as orders', 'orders.id_pemesanan', '=', 'detail.id_pemesanan')
            ->whereIn('detail.id_barang', $ids)
            ->where('detail.stok_dikurangi', false)
            ->where('orders.status_persetujuan', 'Disetujui')
            ->where('orders.status_penyerahan', 'Belum Diserahkan')
            ->groupBy('detail.id_barang')
            ->select('detail.id_barang', DB::raw('SUM(detail.jumlah) as jumlah_dipesan'))
            ->pluck('jumlah_dipesan', 'id_barang');

        return $barang->each(function (Barang $item) use ($reserved) {
            $quantity = (int) ($reserved[$item->id_barang] ?? 0);
            $item->setAttribute('stok_dipesan', $quantity);
            $item->setAttribute('stok_tersedia', $item->stok === null ? null : max(0, (int) $item->stok - $quantity));
        });
    }
}
