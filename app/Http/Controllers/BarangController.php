<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

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

    public function store(Request $request)
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

        Barang::create([
            'id_barang'   => $generatedId,
            'nama_barang' => $request->nama_barang,
            'kategori'    => $request->kategori,
            'harga'       => $request->harga,
            'stok'        => $request->input('stok'),
        ]);

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

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'kategori' => 'required|string|in:Elektronik,Pertanian,Furniture,Kendaraan',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
        ]);

        $barang->update([
            'kategori' => $request->kategori,
            'nama_barang' => $request->nama_barang,
            'harga' => $request->harga,
            'stok' => $request->input('stok'),
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil dihapus.');
    }
}
