<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Carbon\CarbonImmutable;

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

        $today = CarbonImmutable::today();
        $firstTrendMonth = $today->startOfMonth()->subMonths(5);

        $ordersByMonth = Pemesanan::query()
            ->selectRaw('tanggal_pemesanan, COUNT(*) as jumlah')
            ->whereBetween('tanggal_pemesanan', [
                $firstTrendMonth->toDateString(),
                $today->toDateString(),
            ])
            ->groupBy('tanggal_pemesanan')
            ->get()
            ->groupBy(fn ($order) => CarbonImmutable::parse($order->tanggal_pemesanan)->format('Y-m'))
            ->map(fn ($orders) => (int) $orders->sum('jumlah'));

        $monthlyOrderTotals = collect(range(0, 5))
            ->map(function ($offset) use ($firstTrendMonth, $ordersByMonth) {
                $month = $firstTrendMonth->addMonths($offset);
                $key = $month->format('Y-m');

                return [
                    'label' => $month->locale('id')->translatedFormat('M'),
                    'jumlah' => $ordersByMonth->get($key, 0),
                ];
            });

        $maxMonthlyOrders = max(1, (int) $monthlyOrderTotals->max('jumlah'));
        $pemesananTrend = $monthlyOrderTotals
            ->values()
            ->map(fn ($month, $index) => [
                ...$month,
                'x' => 28 + ($index * 116),
                'y' => round(180 - (($month['jumlah'] / $maxMonthlyOrders) * 136), 1),
            ]);
        $pemesananTrendCoordinates = $pemesananTrend
            ->map(fn ($point) => $point['x'].','.$point['y'])
            ->implode(' ');

        $pembayaranLunas = Pembayaran::where('status_pembayaran', 'Lunas')->count();
        $pembayaranBelumLunas = Pembayaran::where('status_pembayaran', '!=', 'Lunas')->count();
        $totalStatusPembayaran = $pembayaranLunas + $pembayaranBelumLunas;
        $kelilingGrafikPembayaran = 2 * M_PI * 72;
        $dashGrafikPembayaran = $totalStatusPembayaran > 0
            ? $kelilingGrafikPembayaran * ($pembayaranLunas / $totalStatusPembayaran)
            : 0;

        $barangTerlaris = DetailPemesanan::query()
            ->select('id_barang')
            ->selectRaw('SUM(jumlah) as jumlah_dipesan')
            ->groupBy('id_barang')
            ->orderByDesc('jumlah_dipesan')
            ->with('barang:id_barang,nama_barang')
            ->limit(4)
            ->get()
            ->map(fn ($detail) => [
                'nama' => $detail->barang?->nama_barang ?? '-',
                'jumlah' => (int) $detail->jumlah_dipesan,
            ])
            ->values();
        $maxBarangDipesan = max(1, (int) $barangTerlaris->max('jumlah'));

        return view('dashboard', compact(
            'totalPelanggan',
            'totalBarang',
            'totalPemesanan',
            'totalPembayaran',
            'pemesananLunas',
            'pemesananBelumLunas',
            'pemesananTrend',
            'pemesananTrendCoordinates',
            'pembayaranLunas',
            'pembayaranBelumLunas',
            'totalStatusPembayaran',
            'kelilingGrafikPembayaran',
            'dashGrafikPembayaran',
            'barangTerlaris',
            'maxBarangDipesan'
        ));
    }
}