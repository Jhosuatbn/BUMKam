<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayaran = Pembayaran::with('pemesanan.pelanggan')->get();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create()
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();
        $idPembayaranPreview = $this->idPembayaranBerikutnya();

        return view('pembayaran.create', compact('pemesanan', 'idPembayaranPreview'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
            'tanggal_pembayaran' => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                ->lockForUpdate()
                ->firstOrFail();
            $nominal = round((float) $pemesanan->angsuran, 2);

            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'id_pemesanan' => 'Pemesanan ini belum memiliki nominal angsuran yang valid.',
                ]);
            }

            $ringkasan = $this->ringkasanPembayaran($pemesanan, $nominal);

            Pembayaran::create([
                'id_pembayaran' => $this->idPembayaranBerikutnya(),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'nominal' => $nominal,
                'total_pembayaran' => $ringkasan['total'],
                'sisa_pembayaran' => $ringkasan['sisa'],
                'status_pembayaran' => $ringkasan['status'],
            ]);

            $pemesanan->update([
                'status_pemesanan' => $ringkasan['status'],
            ]);
        });

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load('pemesanan.pelanggan');

        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(Pembayaran $pembayaran)
    {
        $pemesanan = Pemesanan::with('pelanggan')->get();

        return view('pembayaran.edit', compact('pembayaran', 'pemesanan'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id_pemesanan',
            'tanggal_pembayaran' => 'required|date',
        ]);
        $idPemesananSebelumnya = $pembayaran->id_pemesanan;

        DB::transaction(function () use ($validated, $pembayaran, $idPemesananSebelumnya) {
            $pemesanan = Pemesanan::whereKey($validated['id_pemesanan'])
                ->lockForUpdate()
                ->firstOrFail();
            $nominal = round((float) $pemesanan->angsuran, 2);

            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'id_pemesanan' => 'Pemesanan ini belum memiliki nominal angsuran yang valid.',
                ]);
            }

            $ringkasan = $this->ringkasanPembayaran(
                $pemesanan,
                $nominal,
                $pembayaran->id_pembayaran
            );

            $pembayaran->update([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'nominal' => $nominal,
                'total_pembayaran' => $ringkasan['total'],
                'sisa_pembayaran' => $ringkasan['sisa'],
                'status_pembayaran' => $ringkasan['status'],
            ]);

            $pemesanan->update([
                'status_pemesanan' => $ringkasan['status'],
            ]);

            if ($idPemesananSebelumnya !== $pemesanan->id_pemesanan) {
                $pemesananLama = Pemesanan::whereKey($idPemesananSebelumnya)
                    ->lockForUpdate()
                    ->firstOrFail();
                $this->perbaruiStatusPemesanan($pemesananLama);
            }
        });

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        DB::transaction(function () use ($pembayaran) {
            $pemesanan = Pemesanan::whereKey($pembayaran->id_pemesanan)
                ->lockForUpdate()
                ->firstOrFail();

            $pembayaran->delete();
            $this->perbaruiStatusPemesanan($pemesanan);
        });

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    private function idPembayaranBerikutnya(): string
    {
        $prefix = 'BYR-'.now()->format('dmy').'-';
        $ids = Pembayaran::where('id_pembayaran', 'like', $prefix.'%')
            ->pluck('id_pembayaran');
        $nomorTerakhir = 0;

        foreach ($ids as $id) {
            $bagianNomor = substr($id, strlen($prefix));

            if (ctype_digit($bagianNomor)) {
                $nomorTerakhir = max($nomorTerakhir, (int) $bagianNomor);
            }
        }

        do {
            $nomorTerakhir++;
            $idPembayaran = $prefix.str_pad(
                (string) $nomorTerakhir,
                2,
                '0',
                STR_PAD_LEFT
            );
        } while (Pembayaran::whereKey($idPembayaran)->exists());

        return $idPembayaran;
    }

    private function ringkasanPembayaran(
        Pemesanan $pemesanan,
        float $nominalTambahan,
        ?string $idPembayaranDikecualikan = null
    ): array {
        $query = Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan);

        if ($idPembayaranDikecualikan !== null) {
            $query->where('id_pembayaran', '!=', $idPembayaranDikecualikan);
        }

        $total = round((float) $query->sum('nominal') + $nominalTambahan, 2);
        $tagihanAngsuran = max(
            0,
            round(
                (float) $pemesanan->total_harga
                + (float) $pemesanan->bunga
                - (float) $pemesanan->dp,
                2
            )
        );
        $sisa = round(max(0, $tagihanAngsuran - $total), 2);

        return [
            'total' => $total,
            'sisa' => $sisa,
            'status' => $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
        ];
    }

    private function perbaruiStatusPemesanan(Pemesanan $pemesanan): void
    {
        $ringkasan = $this->ringkasanPembayaran($pemesanan, 0);
        $pemesanan->update([
            'status_pemesanan' => $ringkasan['status'],
        ]);
    }
}
