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
            'tenor' => 'required|integer|in:12,24,36',
            'jaminan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = Barang::whereKey($validated['id_barang'])->lockForUpdate()->firstOrFail();
            $jumlah = (int) $validated['jumlah'];

            if ($barang->stok === null) {
                throw ValidationException::withMessages([
                    'id_barang' => 'Stok barang ini belum diatur. Minta Staff memasukkan stok terlebih dahulu.',
                ]);
            }

            if ($barang->stok < $jumlah) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok . ' unit.',
                ]);
            }

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
                'jumlah' => $jumlah,
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
                'stok_dikurangi' => true,
            ]);

            $barang->decrement('stok', $jumlah);
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
            'tenor' => 'required|integer|in:12,24,36',
            'jaminan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $pemesanan) {
            $detail = DetailPemesanan::where('id_pemesanan', $pemesanan->id_pemesanan)
                ->lockForUpdate()
                ->first();
            $barangLama = $detail
                ? Barang::whereKey($detail->id_barang)->lockForUpdate()->first()
                : null;
            $barang = Barang::whereKey($validated['id_barang'])->lockForUpdate()->firstOrFail();
            $jumlahBaru = (int) $validated['jumlah'];
            $jumlahLama = (int) ($detail?->jumlah ?? 0);

            if ($barang->stok === null) {
                throw ValidationException::withMessages([
                    'id_barang' => 'Stok barang ini belum diatur. Minta Staff memasukkan stok terlebih dahulu.',
                ]);
            }

            $stokBisaDipakai = (int) $barang->stok;
            $stokLamaSudahDikurangi = (bool) ($detail?->stok_dikurangi ?? false);
            if ($stokLamaSudahDikurangi && $barangLama?->id_barang === $barang->id_barang && $barangLama->stok !== null) {
                $stokBisaDipakai += $jumlahLama;
            }

            if ($stokBisaDipakai < $jumlahBaru) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $stokBisaDipakai . ' unit.',
                ]);
            }

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

            $dataDetail = [
                'id_barang' => $barang->id_barang,
                'jumlah' => $jumlahBaru,
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
                'stok_dikurangi' => true,
            ];

            if ($detail) {
                $detail->update($dataDetail);
            } else {
                DetailPemesanan::create(array_merge($dataDetail, [
                    'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                ]));
            }

            if ($stokLamaSudahDikurangi && $barangLama && $barangLama->stok !== null) {
                $barangLama->increment('stok', $jumlahLama);
            }
            $barang->decrement('stok', $jumlahBaru);
        });

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pemesanan berhasil diperbarui.');
    }

    public function destroy(Pemesanan $pemesanan)
    {
        DB::transaction(function () use ($pemesanan) {
            $details = $pemesanan->detailPemesanan()->get();
            foreach ($details as $detail) {
                $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->first();
                if ($detail->stok_dikurangi && $barang && $barang->stok !== null) {
                    $barang->increment('stok', (int) $detail->jumlah);
                }
            }
            $pemesanan->detailPemesanan()->delete();
            $pemesanan->delete();
        });

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pemesanan berhasil dihapus.');
    }
}
