<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailStokOpname;
use App\Models\StokOpname;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StokOpnameController extends Controller
{
    public function index()
    {
        $rows = StokOpname::with(['pencatat', 'pemutus'])->withCount('details')->latest('tanggal_opname')->paginate(20)->withQueryString();
        return view('stok-opname.index', compact('rows'));
    }

    public function create()
    {
        $this->staffOnly();
        $barang = Barang::whereNotNull('stok')->orderBy('nama_barang')->get();
        return view('stok-opname.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $this->staffOnly();
        $data = $request->validate([
            'tanggal_opname' => ['required', 'date'],
            'alasan' => ['required', 'string', 'min:5', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_barang' => ['required', 'exists:barang,id_barang', 'distinct'],
            'items.*.stok_fisik' => ['required', 'integer', 'min:0'],
        ]);
        $id = 'OPN-' . Str::upper(Str::random(20));
        DB::transaction(function () use ($data, $request, $id) {
            $header = StokOpname::create([
                'id_opname' => $id,
                'tanggal_opname' => $data['tanggal_opname'],
                'dicatat_oleh' => $request->user()->id_pengguna,
                'alasan' => $data['alasan'],
            ]);
            foreach ($data['items'] as $item) {
                $barang = Barang::whereKey($item['id_barang'])->lockForUpdate()->firstOrFail();
                if ($barang->stok === null) throw ValidationException::withMessages(['items' => 'Barang tanpa pengaturan stok tidak dapat dimasukkan pada stok opname.']);
                DetailStokOpname::create([
                    'id_detail_opname' => 'DOP-' . Str::upper(Str::random(20)),
                    'id_opname' => $header->id_opname,
                    'id_barang' => $barang->id_barang,
                    'stok_sistem' => $barang->stok,
                    'stok_fisik' => $item['stok_fisik'],
                ]);
            }
        });
        return redirect()->route('stok-opname.index')->with('success', 'Hasil stok opname tersimpan dan menunggu persetujuan Pimpinan.');
    }

    public function show(StokOpname $stokOpname)
    {
        $stokOpname->load(['details.barang', 'pencatat', 'pemutus']);
        return view('stok-opname.show', ['opname' => $stokOpname]);
    }

    public function decide(Request $request, StokOpname $stokOpname, StockMovementService $movements)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        $data = $request->validate([
            'keputusan' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_keputusan' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        DB::transaction(function () use ($request, $stokOpname, $data, $movements) {
            $header = StokOpname::whereKey($stokOpname->id_opname)->lockForUpdate()->with('details')->firstOrFail();
            if ($header->status !== 'Menunggu Persetujuan') throw ValidationException::withMessages(['keputusan' => 'Stok opname ini sudah diputuskan.']);
            if ($data['keputusan'] === 'Disetujui') {
                foreach ($header->details as $detail) {
                    $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->firstOrFail();
                    if ($barang->stok === null || (int) $barang->stok !== (int) $detail->stok_sistem) {
                        throw ValidationException::withMessages(['keputusan' => "Stok {$barang->nama_barang} berubah sejak pencatatan. Tolak opname ini dan lakukan penghitungan ulang."]);
                    }
                    $delta = (int) $detail->stok_fisik - (int) $barang->stok;
                    $movements->apply($barang, $delta, $request->user()->id_pengguna, 'Stok Opname', 'StokOpname', $header->id_opname, $data['catatan_keputusan']);
                }
            }
            $header->update([
                'status' => $data['keputusan'],
                'diputuskan_oleh' => $request->user()->id_pengguna,
                'diputuskan_pada' => now(),
                'catatan_keputusan' => $data['catatan_keputusan'],
            ]);
        });
        return back()->with('success', 'Keputusan stok opname tersimpan; semua selisih tercatat pada pergerakan stok.');
    }

    private function staffOnly(): void
    {
        abort_unless(auth()->user()?->peran === 'Staff', 403);
    }
}
