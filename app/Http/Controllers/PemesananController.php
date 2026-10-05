<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    public function index()
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pemesanan.index', compact('pemesanan'));
    }

    public function create()
    {
        $pelanggan = Pelanggan::all();
        $barang = Barang::all();

        return view('pemesanan.create', compact('pelanggan', 'barang'));
    }

    public function store(Request $request)
{
    $request->validate([
        'id_pemesanan' => 'required|string|max:20|unique:pemesanan,id_pemesanan',
        'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
        'tanggal_pemesanan' => 'required|date',

        'id_barang' => 'required|exists:barang,id_barang',
        'jumlah' => 'required|integer|min:1',

        'skema_pembayaran' => 'required|in:DP,Sebagian,Tanpa DP',
        'dp' => 'nullable|numeric|min:0',
        'tenor' => 'required|integer|min:1',
        'jaminan' => 'nullable|string',
        'bunga' => 'nullable|numeric|min:0',
    ]);

    // Ambil barang yang dipilih
    $barang = Barang::findOrFail($request->id_barang);

    // Hitung subtotal
    $subtotal = $barang->harga * $request->jumlah;

    // Total harga
    $totalHarga = $subtotal;

    // Nilai DP
    $dp = $request->dp ?? 0;

    // Bunga
    $bunga = $request->bunga ?? 0;

    // Hitung angsuran
    if ($request->skema_pembayaran === 'DP') {

        $angsuran = ($totalHarga - $dp + $bunga) / $request->tenor;

    } elseif ($request->skema_pembayaran === 'Sebagian') {

        $angsuran = ($totalHarga - $dp + $bunga) / $request->tenor;

    } else {

        // Tanpa DP
        $angsuran = ($totalHarga + $bunga) / $request->tenor;
    }

    // Simpan pemesanan
    $pemesanan = Pemesanan::create([
        'id_pemesanan' => $request->id_pemesanan,
        'id_pelanggan' => $request->id_pelanggan,
        'tanggal_pemesanan' => $request->tanggal_pemesanan,
        'skema_pembayaran' => $request->skema_pembayaran,
        'dp' => $dp,
        'tenor' => $request->tenor,
        'jaminan' => $request->jaminan,
        'bunga' => $bunga,
        'total_harga' => $totalHarga,
        'angsuran' => $angsuran,
        'status_pemesanan' => 'Belum Lunas',
    ]);

    // Simpan detail barang
    \App\Models\DetailPemesanan::create([
        'id_detail' => 'DTL-' . uniqid(),
        'id_pemesanan' => $pemesanan->id_pemesanan,
        'id_barang' => $barang->id_barang,
        'jumlah' => $request->jumlah,
        'harga' => $barang->harga,
        'subtotal' => $subtotal,
    ]);

    return redirect()
        ->route('pemesanan.index')
        ->with('success', 'Pemesanan berhasil ditambahkan.');
}

    public function show(Pemesanan $pemesanan)
    {
        $pemesanan->load([
            'pelanggan',
            'detailPemesanan.barang'
        ]);

        return view('pemesanan.show', compact('pemesanan'));
    }

    public function edit(Pemesanan $pemesanan)
{
    $pelanggan = Pelanggan::all();
    $barang = Barang::all();

    $pemesanan->load('detailPemesanan.barang');

    return view('pemesanan.edit', compact(
        'pemesanan',
        'pelanggan',
        'barang'
    ));
}

    public function update(Request $request, Pemesanan $pemesanan)
{
    $request->validate([
        'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
        'tanggal_pemesanan' => 'required|date',

        'id_barang' => 'required|exists:barang,id_barang',
        'jumlah' => 'required|integer|min:1',

        'skema_pembayaran' => 'required|in:DP,Sebagian,Tanpa DP',
        'dp' => 'nullable|numeric|min:0',
        'tenor' => 'required|integer|min:1',
        'jaminan' => 'nullable|string',
        'bunga' => 'nullable|numeric|min:0',
    ]);

    // Ambil barang yang dipilih
    $barang = Barang::findOrFail($request->id_barang);

    // Hitung subtotal
    $subtotal = $barang->harga * $request->jumlah;

    // Total harga
    $totalHarga = $subtotal;

    // DP
    $dp = $request->dp ?? 0;

    // Bunga
    $bunga = $request->bunga ?? 0;

    // Hitung angsuran
    if (
        $request->skema_pembayaran === 'DP' ||
        $request->skema_pembayaran === 'Sebagian'
    ) {
        $angsuran = ($totalHarga - $dp + $bunga) / $request->tenor;
    } else {
        $angsuran = ($totalHarga + $bunga) / $request->tenor;
    }

    // Update pemesanan
    $pemesanan->update([
        'id_pelanggan' => $request->id_pelanggan,
        'tanggal_pemesanan' => $request->tanggal_pemesanan,
        'skema_pembayaran' => $request->skema_pembayaran,
        'dp' => $dp,
        'tenor' => $request->tenor,
        'jaminan' => $request->jaminan,
        'bunga' => $bunga,
        'total_harga' => $totalHarga,
        'angsuran' => $angsuran,
    ]);

    // Update detail pemesanan
    $detail = $pemesanan->detailPemesanan->first();

    if ($detail) {
        $detail->update([
            'id_barang' => $barang->id_barang,
            'jumlah' => $request->jumlah,
            'harga' => $barang->harga,
            'subtotal' => $subtotal,
        ]);
    }

    return redirect()
        ->route('pemesanan.index')
        ->with('success', 'Pemesanan berhasil diperbarui.');
}

    public function destroy(Pemesanan $pemesanan)
{
    // Hapus detail barang terlebih dahulu
    $pemesanan->detailPemesanan()->delete();

    // Hapus pemesanan
    $pemesanan->delete();

    return redirect()
        ->route('pemesanan.index')
        ->with('success', 'Pemesanan berhasil dihapus.');
}
}