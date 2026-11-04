<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PergerakanStok;
use Illuminate\Http\Request;

class MonitoringStokController extends Controller
{
    public function index(Request $request)
    {
        $critical = (int) config('inventory.critical_stock', 5);
        $stokKritis = Barang::whereNotNull('stok')->where('stok', '<=', $critical)->orderBy('stok')->get();
        $barangBelumDiatur = Barang::whereNull('stok')->orderBy('nama_barang')->get();
        $laris = Barang::withSum(['detailPemesanan as total_terjual' => fn ($q) => $q->whereHas('pemesanan', fn ($order) => $order->where(function ($status) { $status->whereNull('status_persetujuan')->orWhere('status_persetujuan', '!=', 'Ditolak'); }))], 'jumlah')
            ->orderByDesc('total_terjual')->limit(10)->get();
        $cutoff = now()->subDays((int) config('inventory.inactive_days', 90));
        $tidakBergerak = Barang::whereNotNull('stok')->whereDoesntHave('detailPemesanan', fn ($q) => $q->whereHas('pemesanan', fn ($order) => $order->whereDate('tanggal_pemesanan', '>=', $cutoff)->where(function ($status) { $status->whereNull('status_persetujuan')->orWhere('status_persetujuan', '!=', 'Ditolak'); })))->orderByDesc('stok')->limit(10)->get();
        $movements = PergerakanStok::with(['barang','pengguna'])->when($request->filled('q'), fn ($q) => $q->whereHas('barang', fn ($b) => $b->where('nama_barang','like','%'.$request->q.'%'))->orWhere('referensi_id','like','%'.$request->q.'%'))->latest('created_at')->paginate(25)->withQueryString();
        return view('monitoring-stok.index', compact('critical','stokKritis','barangBelumDiatur','laris','tidakBergerak','movements'));
    }
}
