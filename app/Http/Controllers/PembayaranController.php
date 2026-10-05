<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayaran = Pembayaran::with('pemesanan.pelanggan')->get();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create()
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pembayaran.create', compact('pemesanan'));
    }

    public function store(Request $request)
{
    $request->validate([
        'id_pembayaran' => 'required|string|max:20|unique:pembayaran,id_pembayaran',
        'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
        'tanggal_pembayaran' => 'required|date',
        'nominal' => 'required|numeric|min:1',
    ]);

    $pemesanan = Pemesanan::findOrFail($request->id_pemesanan);

    // Ambil total pembayaran sebelumnya
    $pembayaranSebelumnya = Pembayaran::where(
        'id_pemesanan',
        $pemesanan->id_pemesanan
    )->sum('nominal');

    // Tambahkan pembayaran saat ini
    $totalPembayaran = $pembayaranSebelumnya + $request->nominal;

    // Hitung sisa pembayaran
    $sisaPembayaran = $pemesanan->total_harga - $totalPembayaran;

    // Tentukan status
    if ($sisaPembayaran <= 0) {
        $sisaPembayaran = 0;
        $statusPembayaran = 'Lunas';
    } else {
        $statusPembayaran = 'Belum Lunas';
    }

    Pembayaran::create([
        'id_pembayaran' => $request->id_pembayaran,
        'id_pemesanan' => $request->id_pemesanan,
        'tanggal_pembayaran' => $request->tanggal_pembayaran,
        'nominal' => $request->nominal,
        'total_pembayaran' => $totalPembayaran,
        'sisa_pembayaran' => $sisaPembayaran,
        'status_pembayaran' => $statusPembayaran,
    ]);

    // Update status pemesanan
    $pemesanan->update([
        'status_pemesanan' => $statusPembayaran
    ]);

    return redirect()
        ->route('pembayaran.index')
        ->with('success', 'Pembayaran berhasil ditambahkan.');
}

    public function show(Pembayaran $pembayaran)
{
    $pembayaran->load('pemesanan.pelanggan');

    return view('pembayaran.show', compact('pembayaran'));
}

    public function edit(Pembayaran $pembayaran)
{
    $pemesanan = Pemesanan::with('pelanggan')->get();

    return view('pembayaran.edit', compact(
        'pembayaran',
        'pemesanan'
    ));
}

    public function update(Request $request, Pembayaran $pembayaran)
{
    $request->validate([
        'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
        'tanggal_pembayaran' => 'required|date',
        'nominal' => 'required|numeric|min:1',
    ]);

    $pemesanan = Pemesanan::findOrFail($request->id_pemesanan);

    // Ambil pembayaran lain pada pemesanan yang sama
    $pembayaranSebelumnya = Pembayaran::where(
        'id_pemesanan',
        $pemesanan->id_pemesanan
    )
    ->where('id_pembayaran', '!=', $pembayaran->id_pembayaran)
    ->sum('nominal');

    $totalPembayaran = $pembayaranSebelumnya + $request->nominal;

    $sisaPembayaran = $pemesanan->total_harga - $totalPembayaran;

    if ($sisaPembayaran <= 0) {
        $sisaPembayaran = 0;
        $statusPembayaran = 'Lunas';
    } else {
        $statusPembayaran = 'Belum Lunas';
    }

    $pembayaran->update([
        'id_pemesanan' => $request->id_pemesanan,
        'tanggal_pembayaran' => $request->tanggal_pembayaran,
        'nominal' => $request->nominal,
        'total_pembayaran' => $totalPembayaran,
        'sisa_pembayaran' => $sisaPembayaran,
        'status_pembayaran' => $statusPembayaran,
    ]);

    $pemesanan->update([
        'status_pemesanan' => $statusPembayaran
    ]);

    return redirect()
        ->route('pembayaran.index')
        ->with('success', 'Pembayaran berhasil diperbarui.');
}

    public function destroy(Pembayaran $pembayaran)
{
    $pemesanan = $pembayaran->pemesanan;

    $pembayaran->delete();

    // Hitung ulang total pembayaran setelah data dihapus
    $totalPembayaran = Pembayaran::where(
        'id_pemesanan',
        $pemesanan->id_pemesanan
    )->sum('nominal');

    $sisaPembayaran = $pemesanan->total_harga - $totalPembayaran;

    if ($sisaPembayaran <= 0) {
        $sisaPembayaran = 0;
        $statusPembayaran = 'Lunas';
    } else {
        $statusPembayaran = 'Belum Lunas';
    }

    $pemesanan->update([
        'status_pemesanan' => $statusPembayaran
    ]);

    return redirect()
        ->route('pembayaran.index')
        ->with('success', 'Pembayaran berhasil dihapus.');
}
}