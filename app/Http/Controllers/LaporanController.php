<?php
namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ], [
            'start_date.date' => 'Tanggal mulai tidak valid.',
            'end_date.date' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' =>
                'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $pemesananQuery = Pemesanan::query();
        $pembayaranQuery = Pembayaran::query();

        if ($request->filled('start_date')) {
            $pemesananQuery->whereDate(
                'tanggal_pemesanan',
                '>=',
                $request->start_date
            );

            $pembayaranQuery->whereDate(
                'tanggal_pembayaran',
                '>=',
                $request->start_date
            );
        }

        if ($request->filled('end_date')) {
            $pemesananQuery->whereDate(
                'tanggal_pemesanan',
                '<=',
                $request->end_date
            );

            $pembayaranQuery->whereDate(
                'tanggal_pembayaran',
                '<=',
                $request->end_date
            );
        }

        $totalPemesanan = (clone $pemesananQuery)->count();

        $totalPembayaran = (clone $pembayaranQuery)->sum('nominal');

        $pemesananLunas = (clone $pemesananQuery)
            ->where('status_pemesanan', 'Lunas')
            ->count();

        $pemesananBelumLunas = (clone $pemesananQuery)
            ->where('status_pemesanan', 'Belum Lunas')
            ->count();

        $pemesananTerbaru = (clone $pemesananQuery)
            ->with('pelanggan')
            ->latest('tanggal_pemesanan')
            ->take(10)
            ->get();

        $pembayaranTerbaru = (clone $pembayaranQuery)
            ->with('pemesanan.pelanggan')
            ->latest('tanggal_pembayaran')
            ->take(10)
            ->get();

        return view('laporan.index', compact(
            'totalPemesanan',
            'totalPembayaran',
            'pemesananLunas',
            'pemesananBelumLunas',
            'pemesananTerbaru',
            'pembayaranTerbaru',
            'request'
        ));
    }
}
