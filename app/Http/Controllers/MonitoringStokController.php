<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Services\InventoryAvailabilityService;
use Illuminate\Http\Request;

class MonitoringStokController extends Controller
{
    public function index(Request $request, InventoryAvailabilityService $availability)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $critical = (int) config('inventory.critical_stock', 5);
        $barangDenganStok = Barang::whereNotNull('stok')->orderBy('stok')->get();
        $availability->withAvailability($barangDenganStok);
        $stokKritis = $barangDenganStok->filter(fn (Barang $item) => $item->stok_tersedia <= $critical)->sortBy('stok_tersedia')->values();
        $barangBelumDiatur = Barang::whereNull('stok')->orderBy('nama_barang')->get();
        $availability->withAvailability($stokKritis->merge($barangBelumDiatur));
        $laris = Barang::withSum(['detailPemesanan as total_terjual' => fn ($q) => $q->whereHas('pemesanan', fn ($order) => $order->whereIn('status_penyerahan', ['Diserahkan', 'Riwayat Lama'])->where(function ($status) { $status->whereNull('status_persetujuan')->orWhere('status_persetujuan', 'Disetujui'); }))], 'jumlah')
            ->orderByDesc('total_terjual')->limit(10)->get();
        $cutoff = now()->subDays((int) config('inventory.inactive_days', 90));
        $tidakBergerak = Barang::whereNotNull('stok')->whereDoesntHave('detailPemesanan', fn ($q) => $q->whereHas('pemesanan', fn ($order) => $order->whereDate('tanggal_pemesanan', '>=', $cutoff)->whereIn('status_penyerahan', ['Diserahkan', 'Riwayat Lama'])->where(function ($status) { $status->whereNull('status_persetujuan')->orWhere('status_persetujuan', 'Disetujui'); })))->orderByDesc('stok')->limit(10)->get();
        $availability->withAvailability($laris->merge($tidakBergerak));
        $search = trim($filters['q'] ?? '');
        $movements = PergerakanStok::with(['barang','pengguna'])
            ->when($search !== '', fn ($query) => $query->where(function ($matches) use ($search) {
                $matches->whereHas('barang', fn ($barang) => $barang->where('nama_barang', 'like', '%' . $search . '%'))
                    ->orWhere('referensi_id', 'like', '%' . $search . '%');
            }))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();
        return view('monitoring-stok.index', compact('critical','stokKritis','barangBelumDiatur','laris','tidakBergerak','movements'));
    }
}
