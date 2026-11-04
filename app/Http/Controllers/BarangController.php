<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Services\AuditTrailWriter;
use App\Services\InventoryAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request, InventoryAvailabilityService $availability)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');

        $barang = Barang::query()
            ->when($search !== '', fn ($query) => $query->where(function ($matches) use ($search) {
                $matches->where('id_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            }))
            ->orderBy('kategori')
            ->orderByRaw('CAST(SUBSTRING(id_barang, 4) AS UNSIGNED)')
            ->orderBy('id_barang')
            ->paginate(20)
            ->withQueryString();
        $availability->withAvailability($barang->getCollection());
        $kategoriList = ['Elektronik', 'Pertanian', 'Furniture', 'Kendaraan'];

        return view('barang.index', compact('barang', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = ['Elektronik', 'Pertanian', 'Furniture', 'Kendaraan'];
        return view('barang.create', compact('kategoriList'));
    }

    public function store(Request $request, AuditTrailWriter $auditTrail)
    {
        $request->validate([
            'kategori' => 'required|string|in:Elektronik,Pertanian,Furniture,Kendaraan',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
        ]);

        // Pemetaan Prefix ID berdasarkan Kategori
        $prefixes = [
            'Elektronik' => 'ELE',
            'Pertanian'  => 'PRT',
            'Furniture'  => 'FUR',
            'Kendaraan'  => 'KND',
        ];

        $prefix = $prefixes[$request->kategori] ?? 'BRG';

        // Urutkan berdasarkan angka setelah prefix (jadi ELE100 tidak dikira
        // lebih kecil daripada ELE99 hanya karena urutan teks).
        $lastNumber = Barang::where('id_barang', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(id_barang, 4) AS UNSIGNED)) as nomor_terakhir')
            ->value('nomor_terakhir');
        $nextNumber = ((int) $lastNumber) + 1;

        // Format angka menjadi 2 digit berurutan (01, 02, dst)
        $generatedId = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $generatedId) {
            Barang::create([
                'id_barang' => $generatedId,
                'nama_barang' => $request->nama_barang,
                'kategori' => $request->kategori,
                'harga' => $request->harga,
                'stok' => 0,
            ]);
        });
        $auditTrail->record($request->user(), $request, 'create', 'Barang', 'Barang ditambahkan: ' . $request->nama_barang . ' (' . $generatedId . ').');

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil ditambahkan dengan ID: ' . $generatedId);
    }

    public function show(Barang $barang)
    {
        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        $kategoriList = ['Elektronik', 'Pertanian', 'Furniture', 'Kendaraan'];
        return view('barang.edit', compact('barang', 'kategoriList'));
    }

    public function update(Request $request, Barang $barang, AuditTrailWriter $auditTrail)
    {
        $request->validate([
            'kategori' => 'required|string|in:Elektronik,Pertanian,Furniture,Kendaraan',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $barang) {
            $locked = Barang::whereKey($barang->id_barang)->lockForUpdate()->firstOrFail();
            $locked->update(['kategori' => $request->kategori, 'nama_barang' => $request->nama_barang, 'harga' => $request->harga]);
        });
        $auditTrail->record($request->user(), $request, 'update', 'Barang', 'Identitas barang diperbarui: ' . $barang->nama_barang . '.');

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if (
            $barang->detailPemesanan()->exists()
                || $barang->pergerakanStok()->exists()
                || $barang->detailBarangMasuk()->exists()
        ) {
            return back()->with('error', 'Barang yang memiliki riwayat pemesanan atau stok tidak dapat dihapus agar catatannya tetap utuh.');
        }

        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil dihapus.');
    }
}
