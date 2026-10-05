<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Pemesanan;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPelanggan = Pelanggan::count();
        $totalBarang = Barang::count();
        $totalPemesanan = Pemesanan::count();
        $totalPembayaran = Pembayaran::count();

        $pemesananLunas = Pemesanan::where(
            'status_pemesanan',
            'Lunas'
        )->count();

        $pemesananBelumLunas = Pemesanan::where(
            'status_pemesanan',
            'Belum Lunas'
        )->count();

        return view('dashboard', compact(
            'totalPelanggan',
            'totalBarang',
            'totalPemesanan',
            'totalPembayaran',
            'pemesananLunas',
            'pemesananBelumLunas'
        ));
    }
}