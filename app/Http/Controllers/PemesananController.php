<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Jaminan;
use App\Services\AuditTrailWriter;
use App\Services\StockMovementService;
use App\Services\InventoryAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemesananController extends Controller
{
    private const PERSENTASE_BUNGA = 0.15;

    public function index()
    {
        $pemesanan = Pemesanan::with('pelanggan')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pemesanan.index', compact('pemesanan'));
    }

    public function persetujuan(Request $request)
    {
        abort_unless(auth()->user()?->peran === 'Pimpinan', 403);
        $status = $request->query('status', 'Menunggu Persetujuan');
        abort_unless(in_array($status, ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'], true), 404);
        $pemesanan = Pemesanan::with('pelanggan')
            ->where('status_persetujuan', $status)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
        return view('pemesanan.index', compact('pemesanan', 'status'))->with('halamanPersetujuan', true);
    }

    public function cariPelanggan(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 3) return response()->json(['data' => [], 'message' => 'Ketik minimal 3 karakter untuk mencari.']);
        $data = Pelanggan::query()->where('nama_pelanggan', 'like', '%' . $term . '%')->orderBy('nama_pelanggan')->limit(12)->get(['id_pelanggan', 'nama_pelanggan']);
        return response()->json(['data' => $data]);
    }

    public function cariBarang(Request $request, InventoryAvailabilityService $availability)
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 3) return response()->json(['data' => [], 'message' => 'Ketik minimal 3 karakter untuk mencari.']);
        $data = $availability->withAvailability(Barang::query()->where('nama_barang', 'like', '%' . $term . '%')->orderBy('nama_barang')->limit(12)->get(['id_barang', 'nama_barang', 'harga', 'stok']));
        return response()->json(['data' => $data]);
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

    public function create(InventoryAvailabilityService $availability)
    {
        $pelanggan = Pelanggan::whereKey(old('id_pelanggan'))->get();
        $barang = $availability->withAvailability(Barang::whereKey(old('id_barang'))->get());
        $idPemesananPreview = $this->idPemesananBerikutnya();

        return view('pemesanan.create', compact(
            'pelanggan',
            'barang',
            'idPemesananPreview'
        ));
    }

    public function store(Request $request, AuditTrailWriter $auditTrail, InventoryAvailabilityService $availability)
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
            'catatan_jaminan' => 'nullable|string|max:2000',
            'jenis_jaminan' => ['required', Rule::in(['BPKB', 'Surat Tanah'])],
            'nomor_jaminan' => 'required|string|max:100',
            'tanggal_penyerahan' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $request, $availability) {
            $barang = Barang::whereKey($validated['id_barang'])->lockForUpdate()->firstOrFail();
            $jumlah = (int) $validated['jumlah'];

            if ($barang->stok === null) {
                throw ValidationException::withMessages([
                    'id_barang' => 'Stok barang ini belum diatur. Minta Staff memasukkan stok terlebih dahulu.',
                ]);
            }

            $stokTersedia = $availability->availableQuantity($barang);
            if ($stokTersedia < $jumlah) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Stok tersedia tidak mencukupi. Sisa setelah reservasi: ' . $stokTersedia . ' unit.',
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
                'bunga' => $bunga,
                'total_harga' => $totalHarga,
                'angsuran' => $angsuran,
                'status_pemesanan' => 'Belum Lunas',
                'status_persetujuan' => 'Menunggu Persetujuan',
            ]);

            $pemesanan->riwayatPersetujuan()->create([
                'id_pengguna' => $request->user()->id_pengguna,
                'status_persetujuan' => 'Menunggu Persetujuan',
                'catatan' => 'Pengajuan pemesanan dikirim untuk diperiksa Pimpinan.',
                'created_at' => now(),
            ]);

            if (! empty($validated['jenis_jaminan'])) {
                Jaminan::create([
                    'id_jaminan' => 'JMN-'.Str::upper(Str::random(12)),
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'jenis_dokumen' => $validated['jenis_jaminan'],
                    'nomor_dokumen' => $validated['nomor_jaminan'],
                    'nama_pemilik' => null,
                    'file_path' => null,
                    'tanggal_penyerahan' => $validated['tanggal_penyerahan'],
                    'diterima_oleh' => $request->user()->id_pengguna,
                    'catatan' => $validated['catatan_jaminan'] ?? null,
                    'status_verifikasi' => 'Menunggu Verifikasi',
                ]);
            }

            DetailPemesanan::create([
                'id_detail' => 'DTL-' . Str::upper(Str::random(10)),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'id_barang' => $barang->id_barang,
                'jumlah' => $jumlah,
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
                'stok_dikurangi' => false,
            ]);
        });
        $auditTrail->record($request->user(), $request, 'create', 'Jaminan', 'Informasi jaminan dicatat tanpa mengunggah salinan dokumen.');
        $auditTrail->record($request->user(), $request, 'create', 'Persetujuan Pemesanan', 'Pengajuan pemesanan ' . $validated['id_pemesanan'] . ' menunggu pemeriksaan Pimpinan.');

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
            'jaminanDokumen',
            'persetujuanOleh',
            'diserahkanOleh',
            'riwayatPersetujuan.pengguna',
        ]);

        return view('pemesanan.show', compact('pemesanan'));
    }

    public function edit(Pemesanan $pemesanan)
    {
        abort_if($pemesanan->status_penyerahan === 'Riwayat Lama', 403, 'Pemesanan lama dengan pencatatan stok terdahulu tidak dapat diubah.');
        abort_if(
            $pemesanan->status_persetujuan !== null
                && $pemesanan->status_persetujuan !== 'Menunggu Persetujuan',
            403,
            'Pemesanan yang sudah disetujui atau ditolak tidak dapat diubah.'
        );

        $pelanggan = Pelanggan::whereKey($pemesanan->id_pelanggan)->get();

        $pemesanan->load('detailPemesanan.barang');

        $barang = $pemesanan->detailPemesanan->first()?->barang ? collect([$pemesanan->detailPemesanan->first()->barang]) : collect();

        return view('pemesanan.edit', compact(
            'pemesanan',
            'pelanggan',
            'barang'
        ));
    }

    public function update(Request $request, Pemesanan $pemesanan, InventoryAvailabilityService $availability)
    {
        abort_if($pemesanan->status_penyerahan === 'Riwayat Lama', 403, 'Pemesanan lama dengan pencatatan stok terdahulu tidak dapat diubah.');
        abort_if(
            $pemesanan->status_persetujuan !== null
                && $pemesanan->status_persetujuan !== 'Menunggu Persetujuan',
            403,
            'Pemesanan yang sudah disetujui atau ditolak tidak dapat diubah.'
        );

        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tanggal_pemesanan' => 'required|date',
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
            'skema_pembayaran' => 'required|in:DP,Sebagian,Tanpa DP',
            'dp' => 'required_if:skema_pembayaran,DP,Sebagian|nullable|numeric|min:0',
            'tenor' => 'required|integer|in:12,24,36',
        ]);
        DB::transaction(function () use ($validated, $pemesanan, $availability) {
            $detail = DetailPemesanan::where('id_pemesanan', $pemesanan->id_pemesanan)
                ->lockForUpdate()
                ->first();
            $barang = Barang::whereKey($validated['id_barang'])->lockForUpdate()->firstOrFail();
            $jumlahBaru = (int) $validated['jumlah'];

            if ($barang->stok === null) {
                throw ValidationException::withMessages([
                    'id_barang' => 'Stok barang ini belum diatur. Minta Staff memasukkan stok terlebih dahulu.',
                ]);
            }

            $stokBisaDipakai = $availability->availableQuantity($barang);

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
                'bunga' => $bunga,
                'total_harga' => $totalHarga,
                'angsuran' => $angsuran,
            ]);

            $dataDetail = [
                'id_barang' => $barang->id_barang,
                'jumlah' => $jumlahBaru,
                'harga' => $barang->harga,
                'subtotal' => $totalHarga,
                'stok_dikurangi' => false,
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

    public function approve(Request $request, Pemesanan $pemesanan, AuditTrailWriter $auditTrail, StockMovementService $movements, InventoryAvailabilityService $availability)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);

        $validated = $request->validate([
            'status_persetujuan' => ['required', Rule::in(['Disetujui', 'Ditolak'])],
            'catatan_persetujuan' => ['required_if:status_persetujuan,Ditolak', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $pemesanan, $validated, $movements, $availability) {
            $terkunci = Pemesanan::whereKey($pemesanan->id_pemesanan)
                ->lockForUpdate()
                ->firstOrFail();

            if ($terkunci->status_persetujuan !== 'Menunggu Persetujuan') {
                throw ValidationException::withMessages([
                    'status_persetujuan' => 'Pengajuan ini sudah diputuskan atau bukan pengajuan yang menunggu persetujuan.',
                ]);
            }

            $details = DetailPemesanan::where('id_pemesanan', $terkunci->id_pemesanan)->lockForUpdate()->get();
            if ($validated['status_persetujuan'] === 'Disetujui') {
                foreach ($details as $detail) {
                    if ($detail->stok_dikurangi) {
                        continue; // legacy row already deducted physical stock
                    }
                    $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->firstOrFail();
                    $tersedia = $availability->availableQuantity($barang);
                    if ($tersedia < (int) $detail->jumlah) {
                        throw ValidationException::withMessages([
                            'status_persetujuan' => "Stok tersedia untuk {$barang->nama_barang} tinggal {$tersedia} unit; pengajuan tidak dapat disetujui.",
                        ]);
                    }
                }
            }

            $catatan = $validated['catatan_persetujuan'] ?? null;
            $terkunci->update([
                'status_persetujuan' => $validated['status_persetujuan'],
                'persetujuan_oleh' => $request->user()->id_pengguna,
                'persetujuan_pada' => now(),
                'catatan_persetujuan' => $catatan,
            ]);

            $terkunci->riwayatPersetujuan()->create([
                'id_pengguna' => $request->user()->id_pengguna,
                'status_persetujuan' => $validated['status_persetujuan'],
                'catatan' => $catatan,
                'created_at' => now(),
            ]);

            if ($validated['status_persetujuan'] === 'Ditolak') {
                foreach ($details as $detail) {
                    if (! $detail->stok_dikurangi) {
                        continue;
                    }

                    $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->first();
                    if ($barang && $barang->stok !== null) {
                        $movements->apply($barang, (int) $detail->jumlah, $request->user()->id_pengguna, 'Pembatalan Barang Keluar', 'Pemesanan', $terkunci->id_pemesanan, 'Stok dipulihkan setelah pengajuan ditolak Pimpinan.');
                    }
                    $detail->update(['stok_dikurangi' => false]);
                }
            }
        });

        $status = $validated['status_persetujuan'];
        $catatan = $validated['catatan_persetujuan'] ?? null;
        $auditTrail->record(
            $request->user(),
            $request,
            $status === 'Disetujui' ? 'approve' : 'reject',
            'Persetujuan Pemesanan',
            'Pemesanan ' . $pemesanan->id_pemesanan . ' ' . strtolower($status) . ($catatan ? ': ' . $catatan : '.')
        );

        return redirect()->route('pemesanan.show', $pemesanan)
            ->with('success', 'Status pemesanan berhasil diperbarui menjadi ' . $status . '.');
    }

    public function handover(Request $request, Pemesanan $pemesanan, StockMovementService $movements, AuditTrailWriter $auditTrail)
    {
        abort_unless($request->user()?->peran === 'Staff', 403);
        DB::transaction(function () use ($request, $pemesanan, $movements) {
            $order = Pemesanan::whereKey($pemesanan->id_pemesanan)->lockForUpdate()->firstOrFail();
            if ($order->status_persetujuan !== 'Disetujui' || $order->status_penyerahan !== 'Belum Diserahkan') {
                throw ValidationException::withMessages(['status_penyerahan' => 'Hanya pesanan disetujui yang belum diserahkan dapat diproses.']);
            }
            $details = DetailPemesanan::where('id_pemesanan', $order->id_pemesanan)->lockForUpdate()->get();
            foreach ($details as $detail) {
                $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->firstOrFail();
                if (! $detail->stok_dikurangi) {
                    $movements->apply($barang, -(int) $detail->jumlah, $request->user()->id_pengguna, 'Barang Keluar', 'Pemesanan', $order->id_pemesanan, 'Barang diserahkan kepada pelanggan.');
                    $detail->update(['stok_dikurangi' => true]);
                }
            }
            $order->update(['status_penyerahan' => 'Diserahkan', 'diserahkan_oleh' => $request->user()->id_pengguna, 'diserahkan_pada' => now()]);
            $order->riwayatPersetujuan()->create(['id_pengguna' => $request->user()->id_pengguna, 'status_persetujuan' => 'Diserahkan', 'catatan' => 'Barang diserahkan dan stok fisik diperbarui.', 'created_at' => now()]);
        });
        $auditTrail->record($request->user(), $request, 'handover', 'Pemesanan', 'Barang pemesanan ' . $pemesanan->id_pemesanan . ' diserahkan.');
        return redirect()->route('pemesanan.show', $pemesanan)->with('success', 'Penyerahan barang berhasil dicatat.');
    }

    public function cancel(Request $request, Pemesanan $pemesanan, AuditTrailWriter $auditTrail)
    {
        abort_unless($request->user()?->peran === 'Staff', 403);
        $validated = $request->validate(['alasan_pembatalan' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $pemesanan, $validated) {
            $order = Pemesanan::whereKey($pemesanan->id_pemesanan)->lockForUpdate()->firstOrFail();
            if (! in_array($order->status_persetujuan, ['Menunggu Persetujuan', 'Disetujui'], true)
                || $order->status_penyerahan !== 'Belum Diserahkan'
                || $order->pembayaran()->exists()) {
                throw ValidationException::withMessages(['status_persetujuan' => 'Pemesanan tidak dapat dibatalkan setelah diserahkan, memiliki pembayaran, atau sudah diputuskan.']);
            }
            $order->update(['status_persetujuan' => 'Dibatalkan', 'dibatalkan_oleh' => $request->user()->id_pengguna, 'dibatalkan_pada' => now(), 'alasan_pembatalan' => $validated['alasan_pembatalan']]);
            $order->riwayatPersetujuan()->create(['id_pengguna' => $request->user()->id_pengguna, 'status_persetujuan' => 'Dibatalkan', 'catatan' => $validated['alasan_pembatalan'], 'created_at' => now()]);
        });
        $auditTrail->record($request->user(), $request, 'cancel', 'Pemesanan', 'Pemesanan ' . $pemesanan->id_pemesanan . ' dibatalkan sebelum penyerahan. Alasan: ' . $validated['alasan_pembatalan']);
        return redirect()->route('pemesanan.show', $pemesanan)->with('success', 'Pemesanan berhasil dibatalkan.');
    }

    public function destroy(Request $request, Pemesanan $pemesanan, StockMovementService $movements)
    {
        abort_if($pemesanan->status_penyerahan === 'Riwayat Lama', 403, 'Pemesanan lama dengan riwayat stok terdahulu tidak dapat dihapus.');
        abort_if(
            $pemesanan->status_persetujuan !== null
                && $pemesanan->status_persetujuan !== 'Menunggu Persetujuan',
            403,
            'Pemesanan yang sudah disetujui atau ditolak tidak dapat dihapus.'
        );

        abort_if(
            $pemesanan->pembayaran()->exists(),
            403,
            'Pemesanan yang sudah memiliki riwayat pembayaran tidak dapat dihapus.'
        );

        if ($pemesanan->jaminanDokumen()->exists()) {
            return back()->with('error', 'Pemesanan memiliki dokumen jaminan. Kelola jaminan terlebih dahulu sebelum menghapus pemesanan.');
        }
        DB::transaction(function () use ($pemesanan, $request, $movements) {
            $details = $pemesanan->detailPemesanan()->get();
            foreach ($details as $detail) {
                $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->first();
                if ($detail->stok_dikurangi && $barang && $barang->stok !== null) {
                        $movements->apply($barang, (int) $detail->jumlah, $request->user()->id_pengguna, 'Pembatalan Barang Keluar', 'Pemesanan', $pemesanan->id_pemesanan, 'Stok dipulihkan saat pesanan dihapus.');
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
