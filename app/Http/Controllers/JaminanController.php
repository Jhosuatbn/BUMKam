<?php

namespace App\Http\Controllers;

use App\Models\Jaminan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Services\AuditTrailWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JaminanController extends Controller
{
    private function pastikanStaff(): void
    {
        abort_unless(auth()->user()?->peran === 'Staff', 403);
    }

    public function index()
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
        $jaminan = Jaminan::with('pemesanan.pelanggan')->latest()->paginate(15);
        $pemesananTersedia = auth()->user()->peran === 'Pimpinan'
            ? collect()
            : Pemesanan::with('pelanggan')->where('tenor', '>', 0)
                ->whereDoesntHave('jaminanDokumen')
                ->orderByDesc('tanggal_pemesanan')->get();

        return view('jaminan.index', compact('jaminan', 'pemesananTersedia'));
    }

    public function show(Jaminan $jaminan)
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
        $jaminan->load('pemesanan.pelanggan', 'verifier');

        return view('jaminan.show', compact('jaminan'));
    }

    public function document(Jaminan $jaminan)
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
        abort_unless(Storage::disk('local')->exists($jaminan->file_path), 404);

        return Storage::disk('local')->response($jaminan->file_path, basename($jaminan->file_path));
    }

    public function store(Request $request)
    {
        $this->pastikanStaff();
        $data = $request->validate([
            'id_pemesanan' => ['required', 'exists:pemesanan,id_pemesanan', 'unique:jaminan,id_pemesanan'],
            'jenis_dokumen' => ['required', Rule::in(['BPKB', 'Surat Tanah'])],
            'nomor_dokumen' => ['required', 'string', 'max:100'],
            'nama_pemilik' => ['required', 'string', 'max:100'],
            'file_dokumen' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'tanggal_penyerahan' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
        $pemesanan = Pemesanan::findOrFail($data['id_pemesanan']);
        abort_if((int) $pemesanan->tenor <= 0, 422, 'Jaminan hanya dapat dicatat untuk pemesanan kredit.');
        $path = $request->file('file_dokumen')->store('jaminan', 'local');

        try {
            $jaminan = Jaminan::create([
                'id_jaminan' => 'JMN-'.Str::upper(Str::random(12)),
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'jenis_dokumen' => $data['jenis_dokumen'],
                'nomor_dokumen' => $data['nomor_dokumen'],
                'nama_pemilik' => $data['nama_pemilik'],
                'file_path' => $path,
                'tanggal_penyerahan' => $data['tanggal_penyerahan'],
                'catatan' => $data['catatan'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('jaminan.show', $jaminan)->with('success', 'Dokumen jaminan berhasil disimpan secara privat.');
    }

    public function update(Request $request, Jaminan $jaminan)
    {
        $this->pastikanStaff();
        $data = $request->validate([
            'jenis_dokumen' => ['required', Rule::in(['BPKB', 'Surat Tanah'])],
            'nomor_dokumen' => ['required', 'string', 'max:100'],
            'nama_pemilik' => ['required', 'string', 'max:100'],
            'file_dokumen' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'tanggal_penyerahan' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
        $oldPath = null;
        $newPath = null;
        if ($request->hasFile('file_dokumen')) {
            $newPath = $request->file('file_dokumen')->store('jaminan', 'local');
            $oldPath = $jaminan->file_path;
            $data['file_path'] = $newPath;
        }
        $data['status_verifikasi'] = 'Belum Diverifikasi';
        $data['alasan_penolakan'] = null;
        $data['diverifikasi_oleh'] = null;
        try {
            $jaminan->update($data);
        } catch (\Throwable $exception) {
            if ($newPath) Storage::disk('local')->delete($newPath);
            throw $exception;
        }
        if ($oldPath) Storage::disk('local')->delete($oldPath);

        return back()->with('success', 'Data jaminan berhasil diperbarui.');
    }

    public function verify(Request $request, Jaminan $jaminan)
    {
        abort_unless($request->user()->peran === 'Pimpinan', 403);
        $data = $request->validate([
            'status_verifikasi' => ['required', Rule::in(['Terverifikasi', 'Ditolak'])],
            'alasan_penolakan' => ['required_if:status_verifikasi,Ditolak', 'nullable', 'string', 'max:2000'],
        ]);
        $jaminan->update([
            'status_verifikasi' => $data['status_verifikasi'],
            'alasan_penolakan' => $data['status_verifikasi'] === 'Ditolak' ? $data['alasan_penolakan'] : null,
            'diverifikasi_oleh' => $request->user()->id_pengguna,
        ]);

        return back()->with('success', 'Status verifikasi jaminan berhasil diperbarui.');
    }

    public function returnCollateral(Request $request, Jaminan $jaminan)
    {
        $this->pastikanStaff();
        $data = $request->validate(['status_pengembalian' => ['required', Rule::in(['Sudah Dikembalikan'])]]);
        $pemesanan = $jaminan->pemesanan;
        $wajibDibayar = max(0, (float) $pemesanan->total_harga + (float) $pemesanan->bunga - (float) $pemesanan->dp);
        $sudahDibayar = (float) Pembayaran::where('id_pemesanan', $pemesanan->id_pemesanan)
            ->where('status_gateway', 'settlement')->sum('nominal');
        abort_if($sudahDibayar + 0.01 < $wajibDibayar, 422, 'Jaminan hanya dapat ditandai dikembalikan setelah seluruh kewajiban kredit lunas.');
        $jaminan->update(['status_pengembalian' => $data['status_pengembalian'], 'tanggal_pengembalian' => now()]);

        return back()->with('success', 'Jaminan ditandai sudah dikembalikan.');
    }
}
