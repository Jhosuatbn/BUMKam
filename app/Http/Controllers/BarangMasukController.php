<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\DetailBarangMasuk;
use App\Models\Supplier;
use App\Services\AuditTrailWriter;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BarangMasukController extends Controller
{
    private function staffOnly(): void { abort_unless(auth()->user()?->peran === 'Staff', 403); }

    public function index(Request $request)
    {
        $rows = BarangMasuk::with(['supplier','penerima','details.barang'])->when($request->filled('q'), fn ($q) => $q->where('id_barang_masuk','like','%'.$request->q.'%')->orWhereHas('supplier', fn ($s) => $s->where('nama_supplier','like','%'.$request->q.'%')))->latest('tanggal_masuk')->paginate(20)->withQueryString();
        return view('barang-masuk.index', compact('rows'));
    }

    public function create()
    {
        $this->staffOnly();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $barang = Barang::orderBy('nama_barang')->get();
        return view('barang-masuk.create', compact('suppliers','barang'));
    }

    public function store(Request $request, StockMovementService $movements, AuditTrailWriter $audit)
    {
        $this->staffOnly();
        $data = $request->validate(['id_supplier' => ['required','exists:supplier,id_supplier'], 'nomor_faktur' => ['nullable','string','max:100'], 'tanggal_masuk' => ['required','date'], 'catatan' => ['nullable','string','max:2000'], 'items' => ['required','array','min:1'], 'items.*.id_barang' => ['required','exists:barang,id_barang','distinct'], 'items.*.jumlah' => ['required','integer','min:1'], 'items.*.harga_satuan' => ['required','numeric','min:0']]);
        $receiptId = 'MSK-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        DB::transaction(function () use ($data, $request, $receiptId, $movements) {
            $receipt = BarangMasuk::create(['id_barang_masuk' => $receiptId, 'id_supplier' => $data['id_supplier'], 'nomor_faktur' => $data['nomor_faktur'] ?? null, 'tanggal_masuk' => $data['tanggal_masuk'], 'diterima_oleh' => $request->user()->id_pengguna, 'catatan' => $data['catatan'] ?? null]);
            foreach ($data['items'] as $item) {
                $barang = Barang::whereKey($item['id_barang'])->lockForUpdate()->firstOrFail();
                if ($barang->stok === null) $barang->update(['stok' => 0]);
                $qty = (int) $item['jumlah'];
                DetailBarangMasuk::create(['id_detail_masuk' => 'DMS-' . Str::upper(Str::random(16)), 'id_barang_masuk' => $receiptId, 'id_barang' => $barang->id_barang, 'jumlah' => $qty, 'harga_satuan' => $item['harga_satuan'], 'subtotal' => $qty * (float) $item['harga_satuan']]);
                $movements->apply($barang, $qty, $request->user()->id_pengguna, 'Barang Masuk', 'BarangMasuk', $receiptId, 'Penerimaan dari ' . Supplier::find($data['id_supplier'])->nama_supplier);
            }
        });
        $audit->record($request->user(), $request, 'create', 'Barang Masuk', 'Penerimaan ' . $receiptId . ' dicatat; stok diperbarui otomatis.');
        return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk dicatat dan stok diperbarui.');
    }

    public function show(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load(['supplier','penerima','details.barang']);
        return view('barang-masuk.show', ['receipt' => $barangMasuk]);
    }
}
