<?php

namespace App\Http\Controllers;

use App\Models\Jaminan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
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

    public function show(Jaminan $jaminan)
    {
        $this->pastikanRoleDiizinkan();
        $jaminan->load('pemesanan.pelanggan', 'verifier', 'penerima');
        abort_unless($jaminan->pemesanan, 404);
        $jaminan->pemesanan->loadMissing('pembayaran');

        $pemesanan = $jaminan->pemesanan;
        $wajibDibayar = max(0, (float) $pemesanan->total_harga + (float) $pemesanan->bunga - (float) $pemesanan->dp);
        $sudahDibayar = (float) $pemesanan->pembayaran->where('status_gateway', 'settlement')->sum('nominal');
        $bolehDikembalikan = $pemesanan->status_pemesanan === 'Lunas' || $sudahDibayar + 0.01 >= $wajibDibayar;

        return view('jaminan.show', compact('jaminan', 'bolehDikembalikan'));
    }

    public function document(Jaminan $jaminan)
    {
        $this->pastikanRoleDiizinkan();
        $this->pastikanTerhubungKePemesanan($jaminan);
        abort_unless($jaminan->file_path && Storage::disk('local')->exists($jaminan->file_path), 404);

        $extension = pathinfo($jaminan->file_path, PATHINFO_EXTENSION);
        $namaUnduhan = 'dokumen-jaminan-'.$jaminan->id_jaminan.($extension ? '.'.$extension : '');

        return Storage::disk('local')->response($jaminan->file_path, $namaUnduhan);
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

    public function returnCollateral(Request $request, Jaminan $jaminan)
    {
        $this->pastikanStaff();
        $this->pastikanTerhubungKePemesanan($jaminan);
        abort_if($jaminan->status_pengembalian === 'Sudah Dikembalikan', 422, 'Jaminan sudah tercatat dikembalikan.');
        $request->validate(['status_pengembalian' => ['required', Rule::in(['Sudah Dikembalikan'])]]);

        $pemesanan = $jaminan->pemesanan;
        abort_unless($pemesanan, 404);
        $wajibDibayar = max(0, (float) $pemesanan->total_harga + (float) $pemesanan->bunga - (float) $pemesanan->dp);
        $sudahDibayar = (float) Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->where('status_gateway', 'settlement')
            ->sum('nominal');
        abort_if($sudahDibayar + 0.01 < $wajibDibayar, 422, 'Jaminan hanya dapat ditandai dikembalikan setelah seluruh kewajiban kredit lunas.');

        $jaminan->update([
            'status_pengembalian' => 'Sudah Dikembalikan',
            'tanggal_pengembalian' => now(),
        ]);

        return back()->with('success', 'Jaminan ditandai sudah dikembalikan.');
    }
}
