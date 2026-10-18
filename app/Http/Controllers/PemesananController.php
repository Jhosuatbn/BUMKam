<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PemesananController extends Controller
{
    public function index()
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pemesanan.index', compact('pemesanan'));
    }

    /**
     * Menghitung perkiraan ID untuk ditampilkan di halaman form.
     * ID akan diverifikasi dan dibuat ulang saat penyimpanan.
     */
    private function idPemesananBerikutnya(): string
    {
        $prefix = 'PSN-' . now()->format('dmy') . '-';

        $ids = Pemesanan::where('id_pemesanan', 'like', $prefix . '%')
            ->pluck('id_pemesanan');

        $nomorTerakhir = 0;

        foreach ($ids as $id) {
            $bagianNomor = substr($id, strlen($prefix));

            if (ctype_digit($bagianNomor)) {
                $nomorTerakhir = max(
                    $nomorTerakhir,
                    (int) $bagianNomor
                );
            }
        }

        return $prefix . str_pad(
            $nomorTerakhir + 1,
            2,
            '0',
            STR_PAD_LEFT
        );
    }

    public function create()
    {
        $pelanggan = Pelanggan::all();
        $barang = Barang::all();

        $idPemesananPreview = $this->idPemesananBerikutnya();

        return view('pemesanan.create', compact(
            'pelanggan',
            'barang',
            'idPemesananPreview'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tanggal_pemesanan' => 'required|date',
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
            'skema_pembayaran' => 'required|in:Tanpa DP,Pakai DP',
            'dp' => 'required_if:skema_pembayaran,Pakai DP|nullable|numeric|min:0',
            'tenor' => 'required|integer|min:1',
            'jaminan' => 'nullable|string',
        ]);

        $barang = Barang::findOrFail($request->id_barang);

        $totalHarga = $barang->harga * (int) $request->jumlah;
        $bunga = $totalHarga * 0.15;

        $dp = $request->skema_pembayaran === 'Pakai DP'
            ? (float) $request->dp
            : 0;

        if ($dp > $totalHarga) {
            return back()
                ->withInput()
                ->withErrors([
                    'dp' => 'Nominal DP tidak boleh melebihi total harga barang.',
                ]);
        }

        $angsuran = (
            $totalHarga - $dp + $bunga
        ) / (int) $request->tenor;

        /*
         * MySQL advisory lock mencegah dua proses penyimpanan
         * menghitung nomor urut yang sama secara bersamaan.
         */
        $tanggalId = now()->format('dmy');
        $namaLock = 'pemesanan_' . $tanggalId;

        $lock = DB::selectOne(
            'SELECT GET_LOCK(?, 10) AS lock_status',
            [$namaLock]
        );

        if (!$lock || (int) $lock->lock_status !== 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_pemesanan' => 'Sistem sedang memproses pemesanan lain. Silakan coba lagi.',
                ]);
        }

        try {
            DB::transaction(function () use (
                $request,
                $barang,
                $totalHarga,
                $bunga,
                $dp,
                $angsuran,
                $tanggalId
            ) {
                $prefix = 'PSN-' . $tanggalId . '-';

                $ids = Pemesanan::where(
                    'id_pemesanan',
                    'like',
                    $prefix . '%'
                )->pluck('id_pemesanan');

                $nomorTerakhir = 0;

                foreach ($ids as $id) {
                    $bagianNomor = substr($id, strlen($prefix));

                    if (ctype_digit($bagianNomor)) {
                        $nomorTerakhir = max(
                            $nomorTerakhir,
                            (int) $bagianNomor
                        );
                    }
                }

                $idPemesanan = $prefix . str_pad(
                    $nomorTerakhir + 1,
                    2,
                    '0',
                    STR_PAD_LEFT
                );

                $pemesanan = Pemesanan::create([
                    'id_pemesanan' => $idPemesanan,
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

                DetailPemesanan::create([
                    'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'id_barang' => $barang->id_barang,
                    'jumlah' => $request->jumlah,
                    'harga' => $barang->harga,
                    'subtotal' => $totalHarga,
                ]);
            });
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            DB::selectOne(
                'SELECT RELEASE_LOCK(?)',
                [$namaLock]
            );
        }

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pemesanan berhasil ditambahkan.');
    }

    public function show(Pemesanan $pemesanan)
    {
        $pemesanan->load([
            'pelanggan',
            'detailPemesanan.barang',
            'pembayaran',
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
            'skema_pembayaran' => 'required|in:Tanpa DP,Pakai DP',
            'dp' => 'required_if:skema_pembayaran,Pakai DP|nullable|numeric|min:0',
            'tenor' => 'required|integer|min:1',
            'jaminan' => 'nullable|string',
        ]);

        $barang = Barang::findOrFail($request->id_barang);

        $totalHarga = $barang->harga * (int) $request->jumlah;
        $bunga = $totalHarga * 0.15;

        $dp = $request->skema_pembayaran === 'Pakai DP'
            ? (float) $request->dp
            : 0;

        if ($dp > $totalHarga) {
            return back()
                ->withInput()
                ->withErrors([
                    'dp' => 'Nominal DP tidak boleh melebihi total harga barang.',
                ]);
        }

        $angsuran = (
            $totalHarga - $dp + $bunga
        ) / (int) $request->tenor;

        DB::transaction(function () use (
            $request,
            $pemesanan,
            $barang,
            $totalHarga,
            $bunga,
            $dp,
            $angsuran
        ) {
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

            $detail = $pemesanan->detailPemesanan()->first();

            if ($detail) {
                $detail->update([
                    'id_barang' => $barang->id_barang,
                    'jumlah' => $request->jumlah,
                    'harga' => $barang->harga,
                    'subtotal' => $totalHarga,
                ]);
            } else {
                DetailPemesanan::create([
                    'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'id_barang' => $barang->id_barang,
                    'jumlah' => $request->jumlah,
                    'harga' => $barang->harga,
                    'subtotal' => $totalHarga,
                ]);
            }
        });

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pemesanan berhasil diperbarui.');
    }

    public function destroy(Pemesanan $pemesanan)
    {
        DB::transaction(function () use ($pemesanan) {
            $pemesanan->detailPemesanan()->delete();
            $pemesanan->delete();
        });

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pemesanan berhasil dihapus.');
    }
}
