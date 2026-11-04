<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Services\StockMovementService;
use App\Services\AuditTrailWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        // Fitur Pencarian Barang
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_barang', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        $barang = $query
            ->orderBy('kategori')
            ->orderByRaw('CAST(SUBSTRING(id_barang, 4) AS UNSIGNED)')
            ->orderBy('id_barang')
            ->get();
        $kategoriList = ['Elektronik', 'Pertanian', 'Furniture', 'Kendaraan'];

        return view('barang.index', compact('barang', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = ['Elektronik', 'Pertanian', 'Furniture', 'Kendaraan'];
        return view('barang.create', compact('kategoriList'));
    }

    public function store(Request $request, StockMovementService $movements, AuditTrailWriter $auditTrail)
    {
        $request->validate([
            'kategori' => 'required|string|in:Elektronik,Pertanian,Furniture,Kendaraan',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
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

        DB::transaction(function () use ($request, $generatedId, $movements) {
            $barang = Barang::create([
                'id_barang' => $generatedId,
                'nama_barang' => $request->nama_barang,
                'kategori' => $request->kategori,
                'harga' => $request->harga,
                'stok' => $request->filled('stok') ? 0 : null,
            ]);
            if ($request->filled('stok')) {
                $movements->apply($barang, (int) $request->input('stok'), $request->user()->id_pengguna, 'Stok Awal', 'Barang', $generatedId, 'Saldo awal saat barang dibuat.');
            }
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

    public function update(Request $request, Barang $barang, StockMovementService $movements, AuditTrailWriter $auditTrail)
    {
        $request->validate([
            'kategori' => 'required|string|in:Elektronik,Pertanian,Furniture,Kendaraan',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $barang, $movements) {
            $locked = Barang::whereKey($barang->id_barang)->lockForUpdate()->firstOrFail();
            $locked->update(['kategori' => $request->kategori, 'nama_barang' => $request->nama_barang, 'harga' => $request->harga]);
            if ($request->filled('stok')) {
                $target = (int) $request->input('stok');
                $wasUnconfigured = $locked->stok === null;
                if ($wasUnconfigured) {
                    $locked->update(['stok' => 0]);
                    $locked->refresh();
                }
                $delta = $target - (int) $locked->stok;
                if ($delta !== 0 || $wasUnconfigured) $movements->apply($locked, $delta, $request->user()->id_pengguna, $wasUnconfigured ? 'Stok Awal' : 'Penyesuaian Stok', 'Barang', $locked->id_barang, $wasUnconfigured ? 'Saldo awal ditetapkan melalui edit barang.' : 'Penyesuaian manual melalui edit barang.');
            } elseif ($locked->stok !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages(['stok' => 'Stok yang sudah diketahui tidak dapat dikosongkan. Gunakan 0 bila stok barang habis.']);
            }
        });
        $auditTrail->record($request->user(), $request, 'update', 'Barang', 'Data barang diperbarui: ' . $barang->nama_barang . '; perubahan stok tercatat bila ada.');

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
