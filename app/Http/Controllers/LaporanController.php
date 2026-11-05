<?php
namespace App\Http\Controllers;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\PergerakanStok;
use App\Models\PengeluaranOperasional;
use App\Services\MonitoringKreditService;
use App\Services\InventoryAvailabilityService;
use App\Services\PengembalianAlokasiPembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
class LaporanController extends Controller
{
    public function index(Request $request, MonitoringKreditService $monitoring)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validasi filter tanggal dan tab
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'tab' => ['nullable', 'in:pemesanan,kredit,penerimaan,pembelian,piutang,denda,persediaan,pengeluaran'],
            'persediaan_mode' => ['nullable', 'in:stok,penerimaan'],
            'pelanggan' => ['nullable', 'string', 'max:100'],
        ]);

        $tab = $validated['tab'] ?? 'pemesanan';
        // Keep the consolidated stock selector and preserve legacy report URLs.
        $persediaanMode = $validated['persediaan_mode'] ?? ($tab === 'pembelian' ? 'penerimaan' : 'stok');
        $validated['persediaan_mode'] = $persediaanMode;
        if (! in_array($tab, ['pemesanan', 'kredit'], true)) {
            return $this->laporanKeuangan($tab, $validated, $monitoring);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Query dasar pemesanan dan pembayaran
        |--------------------------------------------------------------------------
        */

        $pemesananQuery = Pemesanan::query();
        $pembayaranQuery = Pembayaran::query()
            ->whereIn('status_gateway', ['settlement', 'partial_refund']);

        /*
        |--------------------------------------------------------------------------
        | 3. Filter periode laporan
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['start_date'])) {
            $pemesananQuery->whereDate(
                'tanggal_pemesanan',
                '>=',
                $validated['start_date']
            );

            $pembayaranQuery->whereDate(
                'tanggal_pembayaran',
                '>=',
                $validated['start_date']
            );
        }

        if (!empty($validated['end_date'])) {
            $pemesananQuery->whereDate(
                'tanggal_pemesanan',
                '<=',
                $validated['end_date']
            );

            $pembayaranQuery->whereDate(
                'tanggal_pembayaran',
                '<=',
                $validated['end_date']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Ringkasan laporan
        |--------------------------------------------------------------------------
        */

        $totalPemesanan = (clone $pemesananQuery)->count();

        $totalPembayaran = (clone $pembayaranQuery)
            ->selectRaw('COALESCE(SUM(nominal - nominal_dikembalikan), 0) as total_net')
            ->value('total_net');

        $pemesananLunas = (clone $pemesananQuery)
            ->where('status_pemesanan', 'Lunas')
            ->count();

        $pemesananBelumLunas = (clone $pemesananQuery)
            ->where('status_pemesanan', 'Belum Lunas')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 5. Daftar pemesanan terbaru
        |--------------------------------------------------------------------------
        */

        $pemesananTerbaru = (clone $pemesananQuery)
            ->with([
                'pelanggan',
                'detailPemesanan.barang',
            ])
            ->orderByDesc('tanggal_pemesanan')
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 6. Daftar pembayaran terbaru
        |--------------------------------------------------------------------------
        */

        $pembayaranTerbaru = (clone $pembayaranQuery)
            ->with([
                'pemesanan.pelanggan',
            ])
            ->orderByDesc('tanggal_pembayaran')
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 7. Query laporan kredit
        |--------------------------------------------------------------------------
        |
        | Transaksi dengan tenor lebih dari nol dianggap kredit.
        | Filter periode mengikuti tanggal pemesanan.
        |
        */

        $relasiKredit = [
                'pelanggan',
                'detailPemesanan.barang',
                'jaminanDokumen',
                'pembayaran' => fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda')),
        ];
        if (Schema::hasTable('penyesuaian_denda')) $relasiKredit[] = 'penyesuaianDenda';
        if (Schema::hasTable('denda_angsuran')) $relasiKredit[] = 'dendaAngsuran';
        $kreditQuery = $this->queryKreditLayakDilaporkan()->with($relasiKredit);

        if (!empty($validated['pelanggan'])) {
            $term = trim($validated['pelanggan']);
            $kreditQuery->whereHas('pelanggan', function ($query) use ($term) {
                $query->where('nama_pelanggan', 'like', '%' . $term . '%')
                    ->orWhere('id_pelanggan', 'like', '%' . $term . '%');
            });
        }

        if (!empty($validated['start_date'])) {
            $kreditQuery->whereDate(
                'tanggal_pemesanan',
                '>=',
                $validated['start_date']
            );
        }

        if (!empty($validated['end_date'])) {
            $kreditQuery->whereDate(
                'tanggal_pemesanan',
                '<=',
                $validated['end_date']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Data tabel kredit dan total angsuran
        |--------------------------------------------------------------------------
        */

        $daftarKredit = (clone $kreditQuery)
            ->orderByDesc('tanggal_pemesanan')
            ->get();

        $daftarKredit->each(function (Pemesanan $order) use ($monitoring) {
            $order->setAttribute('monitoring_kredit', $monitoring->hitung($order));
        });

        $totalDendaDikenakan = $daftarKredit->sum(fn (Pemesanan $order) => $order->monitoring_kredit['total_denda_dikenakan']);
        $totalDendaDibayar = $daftarKredit->sum(fn (Pemesanan $order) => $order->monitoring_kredit['total_denda_dibayar']);
        $totalDendaBelumDibayar = $daftarKredit->sum(fn (Pemesanan $order) => $order->monitoring_kredit['total_denda_belum_dibayar']);
        $totalDendaDikurangi = $daftarKredit->sum(fn (Pemesanan $order) => $order->monitoring_kredit['total_denda_dikurangi']);
        $dendaAktif = app(\App\Services\DendaKeterlambatanService::class)->tersedia();

        $totalAngsuranBulanan = (clone $kreditQuery)
            ->sum('angsuran');

        /*
        |--------------------------------------------------------------------------
        | 9. Kirim data ke halaman laporan
        |--------------------------------------------------------------------------
        */

        return view('laporan.index', [
            'totalPemesanan' => $totalPemesanan,
            'totalPembayaran' => $totalPembayaran,
            'pemesananLunas' => $pemesananLunas,
            'pemesananBelumLunas' => $pemesananBelumLunas,
            'pemesananTerbaru' => $pemesananTerbaru,
            'pembayaranTerbaru' => $pembayaranTerbaru,
            'daftarKredit' => $daftarKredit,
            'totalAngsuranBulanan' => $totalAngsuranBulanan,
            'totalDendaDikenakan' => $totalDendaDikenakan,
            'totalDendaDibayar' => $totalDendaDibayar,
            'totalDendaBelumDibayar' => $totalDendaBelumDibayar,
            'totalDendaDikurangi' => $totalDendaDikurangi,
            'dendaAktif' => $dendaAktif,
        ]);
    }

    private function laporanKeuangan(string $tab, array $filter, MonitoringKreditService $monitoring)
    {
        $start = $filter['start_date'] ?? null;
        $end = $filter['end_date'] ?? null;
        $data = compact('tab', 'start', 'end');

        if ($tab === 'penerimaan') {
            $paymentQuery = Pembayaran::with('pemesanan.pelanggan');
            if (Schema::hasTable('alokasi_pembayaran')) $paymentQuery->with('alokasiDenda');
            $payments = $paymentQuery
                ->whereIn('status_gateway', ['settlement', 'partial_refund', 'refund'])
                ->when($start, fn ($query) => $query->whereDate('tanggal_pembayaran', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('tanggal_pembayaran', '<=', $end))
                ->orderByDesc('tanggal_pembayaran')->get();
            $netService = app(PengembalianAlokasiPembayaranService::class);
            $rows = $payments->map(function (Pembayaran $payment) use ($netService) {
                $payment->setAttribute('pokok_bersih', $netService->pokokEfektif($payment));
                $payment->setAttribute('denda_bersih', $netService->dendaEfektif($payment));
                $payment->setAttribute('diterima_bersih', max(0, (float) $payment->nominal - (float) $payment->nominal_dikembalikan));
                return $payment;
            });
            $data += ['rows' => $rows, 'total' => $rows->sum('diterima_bersih'), 'totalPokok' => $rows->sum('pokok_bersih'), 'totalDenda' => $rows->sum('denda_bersih')];
        } elseif ($tab === 'persediaan' && ($filter['persediaan_mode'] ?? 'stok') === 'penerimaan') {
            $rows = BarangMasuk::with(['supplier', 'penerima', 'details.barang'])
                ->when($start, fn ($query) => $query->whereDate('tanggal_masuk', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('tanggal_masuk', '<=', $end))
                ->orderByDesc('tanggal_masuk')->get();
            $rows->each(fn ($row) => $row->setAttribute('total_belanja', $row->details->sum('subtotal')));
            $data += ['rows' => $rows, 'total' => $rows->sum('total_belanja'), 'inventoryMode' => 'penerimaan'];
        } elseif ($tab === 'pembelian') {
            $rows = BarangMasuk::with(['supplier', 'penerima', 'details.barang'])
                ->when($start, fn ($query) => $query->whereDate('tanggal_masuk', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('tanggal_masuk', '<=', $end))
                ->orderByDesc('tanggal_masuk')->get();
            $rows->each(fn ($row) => $row->setAttribute('total_belanja', $row->details->sum('subtotal')));
            $data += ['rows' => $rows, 'total' => $rows->sum('total_belanja'), 'inventoryMode' => 'penerimaan'];
        } elseif (in_array($tab, ['piutang', 'denda'], true)) {
            $paymentRelation = fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund']);
            if (Schema::hasTable('alokasi_pembayaran')) $paymentRelation = fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->with('alokasiDenda');
            $orders = $this->queryKreditLayakDilaporkan()->with([
                'pelanggan', 'detailPemesanan.barang', 'pembayaran' => $paymentRelation,
                ...(Schema::hasTable('penyesuaian_denda') ? ['penyesuaianDenda'] : []),
                ...(Schema::hasTable('denda_angsuran') ? ['dendaAngsuran'] : []),
            ])
                ->when($start, fn ($query) => $query->whereDate('tanggal_pemesanan', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('tanggal_pemesanan', '<=', $end))
                ->when(! empty($filter['pelanggan']), fn ($query) => $query->whereHas('pelanggan', fn ($customer) => $customer->where('nama_pelanggan', 'like', '%'.$filter['pelanggan'].'%')->orWhere('id_pelanggan', 'like', '%'.$filter['pelanggan'].'%')))
                ->orderByDesc('tanggal_pemesanan')->get();
            $orders->each(fn (Pemesanan $order) => $order->setAttribute('ringkasan_monitoring', $monitoring->hitung($order)));
            if ($tab === 'piutang') {
                $data += ['rows' => $orders, 'total' => $orders->sum(fn ($order) => $order->ringkasan_monitoring['sisa_kewajiban'])];
            } else {
                $data += [
                    'rows' => $orders->filter(fn ($order) => $order->ringkasan_monitoring['total_denda_dikenakan'] > 0)->values(),
                    'totalDikenakan' => $orders->sum(fn ($order) => $order->ringkasan_monitoring['total_denda_dikenakan']),
                    'totalDibayar' => $orders->sum(fn ($order) => $order->ringkasan_monitoring['total_denda_dibayar']),
                    'totalBelumDibayar' => $orders->sum(fn ($order) => $order->ringkasan_monitoring['total_denda_belum_dibayar']),
                    'totalDikurangi' => $orders->sum(fn ($order) => $order->ringkasan_monitoring['total_denda_dikurangi']),
                ];
            }
        } elseif ($tab === 'persediaan') {
            $barang = Barang::orderBy('nama_barang')->get();
            app(InventoryAvailabilityService::class)->withAvailability($barang->whereNotNull('stok')->values());
            $movements = PergerakanStok::with(['barang', 'pengguna'])
                ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end))
                ->latest('created_at')->limit(200)->get();
            $data += [
                'rows' => $barang,
                'movements' => $movements,
                'totalFisik' => $barang->whereNotNull('stok')->sum('stok'),
                'totalTersedia' => $barang->whereNotNull('stok')->sum('stok_tersedia'),
                'belumDiatur' => $barang->whereNull('stok')->count(),
                'inventoryMode' => 'stok',
            ];
        } elseif ($tab === 'pengeluaran') {
            $rows = PengeluaranOperasional::with('pencatat')
                ->when($start, fn ($query) => $query->whereDate('tanggal_pengeluaran', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('tanggal_pengeluaran', '<=', $end))
                ->orderByDesc('tanggal_pengeluaran')->get();
            $data += ['rows' => $rows, 'total' => $rows->sum('nominal')];
        }

        return view('laporan.keuangan', $data);
    }

    private function queryKreditLayakDilaporkan()
    {
        return Pemesanan::query()
            ->where('tenor', '>', 0)
            ->where(fn ($query) => $query
                ->whereNull('status_persetujuan')
                ->orWhere('status_persetujuan', 'Disetujui'));
    }
}
