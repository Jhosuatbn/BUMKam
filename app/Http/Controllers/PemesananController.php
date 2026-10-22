<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PemesananController extends Controller
{
    private const PERSENTASE_BUNGA = 0.15;

    public function index()
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pemesanan.index', compact('pemesanan'));
    }

    /**
     * Membuat ID pratinjau untuk ditampilkan pada formulir.
     * ID tetap divalidasi saat formulir disimpan.
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
                $nomorTerakhir = max($nomorTerakhir, (int) $bagianNomor);
            }
        }

        return $prefix . str_pad(
            (string) ($nomorTerakhir + 1),
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
        $validated = $request->validate([
            'id_pemesanan' => 'required|string|max:20|unique:pemesanan,id_pemesanan',
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tanggal_pemesanan' => 'required|date',
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
            'skema_pembayaran' => 'required|in:DP,Sebagian,Tanpa DP',
            'dp' => 'required_if:skema_pembayaran,DP,Sebagian|nullable|numeric|min:0',
            'tenor' => 'required|integer|min:1',
            'jaminan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = Barang::findOrFail($validated['id_barang']);

            $totalHarga = (float) $barang->harga * (int) $validated['jumlah'];
            $pakaiDP = in_array(
                $validated['skema_pembayaran'],
                ['DP', 'Sebagian'],
                true
            );
            $dp = $pakaiDP ? (float) $validated['dp'] : 0;

            if ($dp > $totalHarga) {
                throw ValidationException::withMessages([
                    'dp' => 'Nominal DP tidak boleh melebihi total harga barang.',
                ]);
            }

            // Bunga dihitung di server, bukan dari nilai kiriman browser.
            $bunga = $totalHarga * self::PERSENTASE_BUNGA;
            $angsuran = ($totalHarga - $dp + $bunga) / (int) $validated['tenor'];

            $pemesanan = Pemesanan::create([
                'id_pemesanan' => $validated['id_pemesanan'],
                'id_pelanggan' => $validated['id_pelanggan'],
                'tanggal_pemesanan' => $validated['tanggal_pemesanan'],
                'skema_pembayaran' => $validated['skema_pembayaran'],
                'dp' => $dp,
                'tenor' => $validated['tenor'],
                'jaminan' => $validated['jaminan'] ?? null,
                'bunga' => $bunga,
                'total_harga' => $totalHarga,
                'angsuran' => $angsuran,
                'status_pemesanan' => 'Belum Lunas',
            ]);

            DetailPemesanan::create([
                'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'id_barang' => $barang->id_barang,
                'jumlah' => (int) $validated['jumlah'],
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
            ]);
        });

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
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tanggal_pemesanan' => 'required|date',
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
            'skema_pembayaran' => 'required|in:DP,Sebagian,Tanpa DP',
            'dp' => 'required_if:skema_pembayaran,DP,Sebagian|nullable|numeric|min:0',
            'tenor' => 'required|integer|min:1',
            'jaminan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $pemesanan) {
            $barang = Barang::findOrFail($validated['id_barang']);

            $totalHarga = (float) $barang->harga * (int) $validated['jumlah'];
            $pakaiDP = in_array(
                $validated['skema_pembayaran'],
                ['DP', 'Sebagian'],
                true
            );
            $dp = $pakaiDP ? (float) $validated['dp'] : 0;

            if ($dp > $totalHarga) {
                throw ValidationException::withMessages([
                    'dp' => 'Nominal DP tidak boleh melebihi total harga barang.',
                ]);
            }

            $bunga = $totalHarga * self::PERSENTASE_BUNGA;
            $angsuran = ($totalHarga - $dp + $bunga) / (int) $validated['tenor'];

            $pemesanan->update([
                'id_pelanggan' => $validated['id_pelanggan'],
                'tanggal_pemesanan' => $validated['tanggal_pemesanan'],
                'skema_pembayaran' => $validated['skema_pembayaran'],
                'dp' => $dp,
                'tenor' => $validated['tenor'],
                'jaminan' => $validated['jaminan'] ?? null,
                'bunga' => $bunga,
                'total_harga' => $totalHarga,
                'angsuran' => $angsuran,
            ]);

            $detail = $pemesanan->detailPemesanan()->first();

            $dataDetail = [
                'id_barang' => $barang->id_barang,
                'jumlah' => (int) $validated['jumlah'],
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
            ];

            if ($detail) {
                $detail->update($dataDetail);
            } else {
                DetailPemesanan::create(array_merge($dataDetail, [
                    'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                ]));
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
