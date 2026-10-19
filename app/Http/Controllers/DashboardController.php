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
        // Ringkasan data
        $totalPelanggan = Pelanggan::count();
        $totalBarang = Barang::count();
        $totalPemesanan = Pemesanan::count();
        $totalPembayaran = Pembayaran::count();

        $totalKredit = Pemesanan::where(
            'skema_pembayaran',
            'Kredit'
        )->distinct('id_pelanggan')
            ->count('id_pelanggan');

        // Status pemesanan
        $pemesananLunas = Pemesanan::where(
            'status_pemesanan',
            'Lunas'
        )->count();

        $pemesananBelumLunas = Pemesanan::where(
            'status_pemesanan',
            'Belum Lunas'
        )->count();

        // Perbandingan transaksi bulan ini dan bulan sebelumnya
        $awalBulanIni = CarbonImmutable::now()->startOfMonth();
        $awalBulanDepan = $awalBulanIni->addMonth();
        $awalBulanLalu = $awalBulanIni->subMonth();

        $transaksiBulanIni = Pemesanan::where(
            'tanggal_pemesanan',
            '>=',
            $awalBulanIni->toDateString()
        )
            ->where(
                'tanggal_pemesanan',
                '<',
                $awalBulanDepan->toDateString()
            )
            ->count();

        $transaksiBulanLalu = Pemesanan::where(
            'tanggal_pemesanan',
            '>=',
            $awalBulanLalu->toDateString()
        )
            ->where(
                'tanggal_pemesanan',
                '<',
                $awalBulanIni->toDateString()
            )
            ->count();

        if ($transaksiBulanLalu > 0) {
            $persentasePerubahan = (
                ($transaksiBulanIni - $transaksiBulanLalu)
                / $transaksiBulanLalu
            ) * 100;
        } elseif ($transaksiBulanIni > 0) {
            $persentasePerubahan = 100;
        } else {
            $persentasePerubahan = 0;
        }

        $persentasePerubahan = round($persentasePerubahan, 1);

        // Grafik transaksi enam bulan terakhir
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
            ->groupBy(
                fn ($order) => CarbonImmutable::parse(
                    $order->tanggal_pemesanan
                )->format('Y-m')
            )
            ->map(
                fn ($orders) => (int) $orders->sum('jumlah')
            );

        $monthlyOrderTotals = collect(range(0, 5))
            ->map(function ($offset) use (
                $firstTrendMonth,
                $ordersByMonth
            ) {
                $month = $firstTrendMonth->addMonths($offset);
                $key = $month->format('Y-m');

                return [
                    'label' => $month->locale('id')->translatedFormat('M'),
                    'jumlah' => $ordersByMonth->get($key, 0),
                ];
            });

        $maxMonthlyOrders = max(
            1,
            (int) $monthlyOrderTotals->max('jumlah')
        );

        $pemesananTrend = $monthlyOrderTotals
            ->values()
            ->map(fn ($month, $index) => [
                ...$month,
                'x' => 28 + ($index * 116),
                'y' => round(
                    180 - (
                        ($month['jumlah'] / $maxMonthlyOrders) * 136
                    ),
                    1
                ),
            ]);

        $pemesananTrendCoordinates = $pemesananTrend
            ->map(fn ($point) => $point['x'] . ',' . $point['y'])
            ->implode(' ');

        // Status pembayaran
        $pembayaranLunas = Pembayaran::where(
            'status_pembayaran',
            'Lunas'
        )->count();

        $pembayaranBelumLunas = Pembayaran::where(
            'status_pembayaran',
            '!=',
            'Lunas'
        )->count();

        $totalStatusPembayaran = $pembayaranLunas
            + $pembayaranBelumLunas;

        $kelilingGrafikPembayaran = 2 * M_PI * 72;

        $dashGrafikPembayaran = $totalStatusPembayaran > 0
            ? $kelilingGrafikPembayaran
                * ($pembayaranLunas / $totalStatusPembayaran)
            : 0;

        // Barang paling banyak dipesan
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

        $maxBarangDipesan = max(
            1,
            (int) $barangTerlaris->max('jumlah')
        );

        // Lima transaksi terbaru
        $transaksiTerbaru = Pemesanan::with('pelanggan')
            ->orderByDesc('tanggal_pemesanan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Kirim data ke halaman dashboard
        return view('dashboard', compact(
            'totalPelanggan',
            'totalBarang',
            'totalPemesanan',
            'totalPembayaran',
            'totalKredit',
            'pemesananLunas',
            'pemesananBelumLunas',
            'transaksiBulanIni',
            'transaksiBulanLalu',
            'persentasePerubahan',
            'pemesananTrend',
            'pemesananTrendCoordinates',
            'pembayaranLunas',
            'pembayaranBelumLunas',
            'totalStatusPembayaran',
            'kelilingGrafikPembayaran',
            'dashGrafikPembayaran',
            'barangTerlaris',
            'transaksiTerbaru',
            'maxBarangDipesan'
        ));
    }
}
