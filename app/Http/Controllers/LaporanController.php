<?php
namespace App\Http\Controllers;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validasi filter tanggal dan tab
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'tab' => ['nullable', 'in:pemesanan,kredit'],
            'pelanggan' => ['nullable', 'string', 'max:100'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Query dasar pemesanan dan pembayaran
        |--------------------------------------------------------------------------
        */

        $pemesananQuery = Pemesanan::query();
        $pembayaranQuery = Pembayaran::query()
            ->where('status_gateway', 'settlement');

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
            ->sum('nominal');

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

        $kreditQuery = Pemesanan::query()
            ->with([
                'pelanggan',
                'detailPemesanan.barang',
                'pembayaran' => fn ($query) => $query->where('status_gateway', 'settlement'),
            ])
            ->whereNotNull('tenor')
            ->where('tenor', '>', 0);

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
        ]);
    }
}
