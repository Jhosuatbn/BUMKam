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
        $totalPembayaran = Pembayaran::where('status_gateway', 'settlement')->count();

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

        // Nilai pesanan per periode (menggunakan total_harga yang tercatat).
        $nilaiPesananBulanIni = Pemesanan::where(
            'tanggal_pemesanan',
            '>=',
            $awalBulanIni->toDateString()
        )
            ->where('tanggal_pemesanan', '<', $awalBulanDepan->toDateString())
            ->sum('total_harga');

        $nilaiPesananBulanLalu = Pemesanan::where(
            'tanggal_pemesanan',
            '>=',
            $awalBulanLalu->toDateString()
        )
            ->where('tanggal_pemesanan', '<', $awalBulanIni->toDateString())
            ->sum('total_harga');

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

        // Perbandingan tahunan dan nilai pemesanan mengikuti enam bulan yang sama.
        $previousYearStart = $firstTrendMonth->subYear();
        $previousYearEnd = $today->subYear();
        $previousYearOrders = Pemesanan::query()
            ->selectRaw("DATE_FORMAT(tanggal_pemesanan, '%Y-%m') as bulan, COUNT(*) as jumlah")
            ->whereBetween('tanggal_pemesanan', [
                $previousYearStart->toDateString(),
                $previousYearEnd->toDateString(),
            ])
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $monthlyOrderComparison = collect(range(0, 5))
            ->map(function ($offset) use ($firstTrendMonth, $previousYearOrders) {
                $month = $firstTrendMonth->addMonths($offset);

                return (int) $previousYearOrders->get(
                    $month->subYear()->format('Y-m'),
                    0
                );
            });

        $monthlyOrderValues = Pemesanan::query()
            ->selectRaw("DATE_FORMAT(tanggal_pemesanan, '%Y-%m') as bulan, SUM(total_harga) as total")
            ->whereBetween('tanggal_pemesanan', [
                $firstTrendMonth->toDateString(),
                $today->toDateString(),
            ])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $monthlyRevenueTrend = collect(range(0, 5))
            ->map(function ($offset) use ($firstTrendMonth, $monthlyOrderValues) {
                $month = $firstTrendMonth->addMonths($offset);

                return (float) $monthlyOrderValues->get($month->format('Y-m'), 0);
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
        )->where('status_gateway', 'settlement')->count();

        $pembayaranBelumLunas = Pembayaran::where(
            'status_pembayaran',
            '!=',
            'Lunas'
        )->where('status_gateway', 'settlement')->count();

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

        // Data tambahan hanya dimuat untuk ringkasan Dashboard Pimpinan.
        $barangBelumTerjual = collect();
        $barangStokHabis = collect();
        if (auth()->user()?->peran === 'Pimpinan') {
            $ringkasanBarang = Barang::query()
                ->withSum('detailPemesanan as jumlah_dipesan', 'jumlah')
                ->get();
            $barangBelumTerjual = $ringkasanBarang
                ->filter(fn ($barang) => (int) $barang->jumlah_dipesan === 0)
                ->take(4)
                ->values();
            $barangStokHabis = $ringkasanBarang
                ->filter(fn ($barang) => $barang->stok !== null && (int) $barang->stok === 0)
                ->take(4)
                ->values();
        }

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
            'nilaiPesananBulanIni',
            'nilaiPesananBulanLalu',
            'persentasePerubahan',
            'pemesananTrend',
            'monthlyOrderComparison',
            'monthlyRevenueTrend',
            'pemesananTrendCoordinates',
            'pembayaranLunas',
            'pembayaranBelumLunas',
            'totalStatusPembayaran',
            'kelilingGrafikPembayaran',
            'dashGrafikPembayaran',
            'barangTerlaris',
            'barangBelumTerjual',
            'barangStokHabis',
            'transaksiTerbaru',
            'maxBarangDipesan'
        ));
    }
}
