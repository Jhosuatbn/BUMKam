<?php
namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Carbon\CarbonImmutable;
use App\Services\MonitoringKreditService;
use App\Services\InventoryAvailabilityService;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan data
        $totalPelanggan = Pelanggan::count();
        $totalBarang = Barang::count();
        $totalPemesanan = Pemesanan::count();
        $statusPembayaranDiterima = ['settlement', 'partial_refund'];
        $totalPembayaran = Pembayaran::whereIn('status_gateway', $statusPembayaranDiterima)->count();

        // Legacy orders with a null approval status remain included; new pending,
        // rejected, and cancelled applications are not active credit customers.
        $kreditAktifQuery = fn () => Pemesanan::where('tenor', '>', 0)
            ->where(fn ($query) => $query
                ->whereNull('status_persetujuan')
                ->orWhere('status_persetujuan', 'Disetujui'));
        $totalKredit = $kreditAktifQuery()->distinct('id_pelanggan')
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
        $zonaWaktu = config('kredit.zona_waktu', 'Asia/Jakarta');
        $awalBulanIni = CarbonImmutable::now($zonaWaktu)->startOfMonth();
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
        $today = CarbonImmutable::today($zonaWaktu);
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
            ->whereBetween('tanggal_pemesanan', [$firstTrendMonth->toDateString(), $today->toDateString()])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $monthlyOrderValueTrend = collect(range(0, 5))
            ->map(fn ($offset) => (float) $monthlyOrderValues->get($firstTrendMonth->addMonths($offset)->format('Y-m'), 0));

        $paymentAmountExpression = Schema::hasColumn('pembayaran', 'nominal_dikembalikan')
            ? 'SUM(nominal - COALESCE(nominal_dikembalikan, 0))'
            : 'SUM(nominal)';
        $monthlyPaymentValues = Pembayaran::query()
            ->whereIn('status_gateway', $statusPembayaranDiterima)
            ->selectRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan, {$paymentAmountExpression} as total")
            ->whereBetween('tanggal_pembayaran', [$firstTrendMonth->toDateString(), $today->toDateString()])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $monthlyRevenueTrend = collect(range(0, 5))
            ->map(function ($offset) use ($firstTrendMonth, $monthlyPaymentValues) {
                $month = $firstTrendMonth->addMonths($offset);

                return (float) $monthlyPaymentValues->get($month->format('Y-m'), 0);
            });

        $jumlahBarangStokKritis = 0;
        $piutangBerjalan = 0.0;
        $jumlahAngsuranTerlambat = 0;
        $dendaDikenakan = 0.0;
        $dendaDiterima = 0.0;

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
        )->whereIn('status_gateway', $statusPembayaranDiterima)->count();

        $pembayaranBelumLunas = Pembayaran::where(
            'status_pembayaran',
            '!=',
            'Lunas'
        )->whereIn('status_gateway', $statusPembayaranDiterima)->count();

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
        $jumlahBarangStokHabis = 0;
        $pengajuanMenungguPersetujuan = collect();
        $jumlahPersetujuanMenunggu = 0;
        if (auth()->user()?->peran === 'Pimpinan') {
            $ringkasanBarang = Barang::query()
                ->withSum('detailPemesanan as jumlah_dipesan', 'jumlah')
                ->get();
            $criticalStock = (int) config('inventory.critical_stock', 5);
            $barangTersedia = app(InventoryAvailabilityService::class)->withAvailability($ringkasanBarang->filter(fn ($barang) => $barang->stok !== null)->values());
            $barangBelumTerjual = $ringkasanBarang
                ->filter(fn ($barang) => (int) $barang->jumlah_dipesan === 0)
                ->take(4)
                ->values();
            $jumlahBarangStokKritis = $barangTersedia->filter(fn ($barang) => $barang->stok_tersedia <= $criticalStock)->count();
            $barangStokHabisPenuh = $ringkasanBarang
                ->filter(fn ($barang) => $barang->stok !== null && (int) $barang->stok === 0)
                ->values();
            $jumlahBarangStokHabis = $barangStokHabisPenuh->count();
            $barangStokHabis = $barangStokHabisPenuh->take(4)->values();

            $pengajuanMenungguPersetujuan = Pemesanan::with('pelanggan')
                ->where('status_persetujuan', 'Menunggu Persetujuan')
                ->latest('created_at')
                ->limit(5)
                ->get();
            $jumlahPersetujuanMenunggu = Pemesanan::where('status_persetujuan', 'Menunggu Persetujuan')->count();

            $relasiMonitoring = [
                'pembayaran' => fn ($query) => $query->whereIn('status_gateway', $statusPembayaranDiterima)
                    ->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda')),
            ];
            if (Schema::hasTable('penyesuaian_denda')) $relasiMonitoring[] = 'penyesuaianDenda';
            if (Schema::hasTable('denda_angsuran')) $relasiMonitoring[] = 'dendaAngsuran';
            $kreditAktif = $kreditAktifQuery()->with($relasiMonitoring)->get();
            $ringkasanKreditDashboard = $kreditAktif->map(fn (Pemesanan $order) => app(MonitoringKreditService::class)->hitung($order));
            $piutangBerjalan = (float) $ringkasanKreditDashboard->sum('sisa_kewajiban');
            $jumlahAngsuranTerlambat = (int) $ringkasanKreditDashboard->sum('angsuran_terlambat');
            $dendaDikenakan = (float) $ringkasanKreditDashboard->sum('total_denda_dikenakan');
            $dendaDiterima = (float) $ringkasanKreditDashboard->sum('total_denda_dibayar');
        }

        $nilaiPembayaranBulanIni = $this->totalPembayaranDiterima($awalBulanIni, $awalBulanDepan, $statusPembayaranDiterima);
        $nilaiPembayaranBulanLalu = $this->totalPembayaranDiterima($awalBulanLalu, $awalBulanIni, $statusPembayaranDiterima);

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
            'monthlyOrderValueTrend',
            'nilaiPembayaranBulanIni',
            'nilaiPembayaranBulanLalu',
            'pemesananTrendCoordinates',
            'pembayaranLunas',
            'pembayaranBelumLunas',
            'totalStatusPembayaran',
            'kelilingGrafikPembayaran',
            'dashGrafikPembayaran',
            'barangTerlaris',
            'barangBelumTerjual',
            'barangStokHabis',
            'pengajuanMenungguPersetujuan',
            'jumlahPersetujuanMenunggu',
            'transaksiTerbaru',
            'maxBarangDipesan',
            'piutangBerjalan',
            'jumlahAngsuranTerlambat',
            'dendaDikenakan',
            'dendaDiterima',
            'jumlahBarangStokKritis',
            'jumlahBarangStokHabis'
        ));
    }

    private function totalPembayaranDiterima(CarbonImmutable $mulai, CarbonImmutable $akhir, array $status): float
    {
        $query = Pembayaran::query()->whereIn('status_gateway', $status)
            ->where('tanggal_pembayaran', '>=', $mulai->toDateString())
            ->where('tanggal_pembayaran', '<', $akhir->toDateString());

        return (float) (Schema::hasColumn('pembayaran', 'nominal_dikembalikan')
            ? $query->selectRaw('COALESCE(SUM(nominal - COALESCE(nominal_dikembalikan, 0)), 0) as total')->value('total')
            : $query->sum('nominal'));
    }
}
