<?php

namespace App\Http\Controllers;

use App\Models\Jaminan;
use App\Models\Pemesanan;
use App\Services\MonitoringKreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JaminanController extends Controller
{
    private function pastikanRoleDiizinkan(): void
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
    }

    private function pastikanStaff(): void
    {
        abort_unless(auth()->user()?->peran === 'Staff', 403);
    }

    private function pastikanTerhubungKePemesanan(Jaminan $jaminan): void
    {
        abort_unless($jaminan->pemesanan()->exists(), 404);
    }

    public function index()
    {
        $this->pastikanRoleDiizinkan();
        $jaminan = Jaminan::with('pemesanan.pelanggan', 'verifier', 'penerima')
            ->latest('created_at')
            ->paginate(15);

        return view('jaminan.index', compact('jaminan'));
    }

    public function show(Jaminan $jaminan, MonitoringKreditService $monitoring)
    {
        $this->pastikanRoleDiizinkan();
        $jaminan->load('pemesanan.pelanggan', 'verifier', 'penerima');
        abort_unless($jaminan->pemesanan, 404);
        $ringkasan = $this->ringkasanKewajiban($jaminan->pemesanan, $monitoring);
        $bolehDikembalikan = $ringkasan['sisa_kewajiban'] <= 0.01;

        return view('jaminan.show', compact('jaminan', 'bolehDikembalikan'));
    }

    public function document(Jaminan $jaminan)
    {
        $this->pastikanRoleDiizinkan();
        $this->pastikanTerhubungKePemesanan($jaminan);
        abort_unless($jaminan->file_path && Storage::disk('local')->exists($jaminan->file_path), 404);

        $extension = pathinfo($jaminan->file_path, PATHINFO_EXTENSION);
        $namaUnduhan = 'dokumen-jaminan-'.$jaminan->id_jaminan.($extension ? '.'.$extension : '');

        return Storage::disk('local')->response($jaminan->file_path, $namaUnduhan, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function update(Request $request, Jaminan $jaminan, \App\Services\AuditTrailWriter $audit)
    {
        $this->pastikanStaff();
        $this->pastikanTerhubungKePemesanan($jaminan);
        abort_if($jaminan->status_pengembalian === 'Sudah Dikembalikan', 422, 'Jaminan yang sudah dikembalikan tidak dapat diubah.');

        $data = $request->validate([
            'jenis_dokumen' => ['required', Rule::in(['BPKB', 'Surat Tanah'])],
            'nomor_dokumen' => ['required', 'string', 'max:100'],
            'tanggal_penyerahan' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        // Every staff correction returns the same linked document to the verification queue.
        $data['status_verifikasi'] = 'Menunggu Verifikasi';
        $data['alasan_penolakan'] = null;
        $data['diverifikasi_oleh'] = null;

        $data['diterima_oleh'] ??= $request->user()->id_pengguna;
        $jaminan->update($data);
        $audit->record($request->user(), $request, 'update', 'Jaminan', 'Data identitas jaminan diperbarui; tidak ada salinan dokumen yang diunggah.');
        return back()->with('success', 'Data jaminan berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }

    public function verify(Request $request, Jaminan $jaminan)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        $this->pastikanTerhubungKePemesanan($jaminan);
        abort_if($jaminan->status_pengembalian === 'Sudah Dikembalikan', 422, 'Jaminan yang sudah dikembalikan tidak dapat diverifikasi ulang.');

        $data = $request->validate([
            'status_verifikasi' => ['required', Rule::in(['Disetujui', 'Ditolak'])],
            'alasan_penolakan' => ['required_if:status_verifikasi,Ditolak', 'nullable', 'string', 'max:2000'],
        ]);

        $jaminan->update([
            'status_verifikasi' => $data['status_verifikasi'],
            'alasan_penolakan' => $data['status_verifikasi'] === 'Ditolak' ? $data['alasan_penolakan'] : null,
            'diverifikasi_oleh' => $request->user()->id_pengguna,
        ]);

        return back()->with('success', 'Keputusan verifikasi jaminan berhasil dicatat.');
    }

    public function returnCollateral(Request $request, Jaminan $jaminan, MonitoringKreditService $monitoring)
    {
        $this->pastikanStaff();
        $request->validate(['status_pengembalian' => ['required', Rule::in(['Sudah Dikembalikan'])]]);

        DB::transaction(function () use ($jaminan, $monitoring) {
            $lockedJaminan = Jaminan::whereKey($jaminan->getKey())->lockForUpdate()->firstOrFail();
            $this->pastikanTerhubungKePemesanan($lockedJaminan);
            abort_if($lockedJaminan->status_pengembalian === 'Sudah Dikembalikan', 422, 'Jaminan sudah tercatat dikembalikan.');

            // Lock the order row to serialize this decision with payment/refund processing.
            $pemesanan = Pemesanan::whereKey($lockedJaminan->id_pemesanan)->lockForUpdate()->firstOrFail();
            $ringkasan = $this->ringkasanKewajiban($pemesanan, $monitoring);
            abort_if($ringkasan['sisa_kewajiban'] > 0.01, 422, 'Jaminan hanya dapat ditandai dikembalikan setelah seluruh kewajiban kredit dan denda lunas.');

            $lockedJaminan->update([
                'status_pengembalian' => 'Sudah Dikembalikan',
                'tanggal_pengembalian' => now(),
            ]);
        });

        return back()->with('success', 'Jaminan ditandai sudah dikembalikan.');
    }

    /** Use the same net-payment, refund, and penalty calculation as credit monitoring. */
    private function ringkasanKewajiban(Pemesanan $pemesanan, MonitoringKreditService $monitoring): array
    {
        $relasi = [
            'pembayaran' => fn ($query) => $query
                ->whereIn('status_gateway', ['settlement', 'partial_refund'])
                ->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ];
        if (Schema::hasTable('penyesuaian_denda')) {
            $relasi[] = 'penyesuaianDenda';
        }
        if (Schema::hasTable('denda_angsuran')) {
            $relasi[] = 'dendaAngsuran';
        }

        $pemesanan->load($relasi);

        return $monitoring->hitung($pemesanan);
    }
}
