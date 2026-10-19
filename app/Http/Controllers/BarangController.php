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

        $barang = $query->latest()->get();

        return view('barang.index', compact('barang'));
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
        ]);

        // Pemetaan Prefix ID berdasarkan Kategori
        $prefixes = [
            'Elektronik' => 'ELE',
            'Pertanian'  => 'PRT',
            'Furniture'  => 'FUR',
            'Kendaraan'  => 'KND',
        ];

        $prefix = $prefixes[$request->kategori] ?? 'BRG';

        // Generasi ID Barang Otomatis & Cek Agar Tidak Bentrok
        do {
            $randomNumber = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $generatedId = $prefix . $randomNumber;
        } while (Barang::where('id_barang', $generatedId)->exists());

        Barang::create([
            'id_barang'   => $generatedId,
            'nama_barang' => $request->nama_barang,
            'kategori'    => $request->kategori,
            'harga'       => $request->harga,
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
        ]);

        $barang->update([
            'kategori' => $request->kategori,
            'nama_barang' => $request->nama_barang,
            'harga' => $request->harga,
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