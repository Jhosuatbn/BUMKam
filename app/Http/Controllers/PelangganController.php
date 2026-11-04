<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    /**
     * Aturan validasi yang dipakai bersama oleh store() dan update().
     */
    private function rules(?Pelanggan $pelanggan = null): array
    {
        return [
            // Wajib mengandung huruf, jadi tidak boleh angka saja.
            // Hapus \p{N} jika ingin melarang angka sama sekali.
            'nama_pelanggan' => [
                'required',
                'string',
                'max:100',
                "regex:/^(?=.*\p{L})[\p{L}\p{N}\s.'\-]+$/u",
            ],

            // NIK: angka, tepat 16 digit.
            // Jika perlu NIK lama yang kurang dari 16 digit, ganti 'digits:16'
            // menjadi 'digits_between:10,16'.
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('pelanggan', 'nik')
                    ->ignore($pelanggan?->id_pelanggan, 'id_pelanggan'),
            ],

            // No. telepon: angka, maksimal 13 digit.
            'no_telepon' => ['required', 'digits_between:10,13'],

            'wa_reminder_opt_in' => ['nullable', 'boolean'],

            'alamat' => ['required', 'string', 'max:255'],

            // Foto KTP: opsional, gambar JPG/PNG/WEBP, maksimal 2 MB.
            // Ganti 'nullable' menjadi 'required' di store() jika foto wajib.
            'foto_ktp' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Centang "hapus foto" di halaman edit.
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'nama_pelanggan.regex' => 'Nama harus berupa teks dan tidak boleh hanya angka.',
            'nik.digits' => 'NIK harus berupa angka tepat 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'no_telepon.digits_between' => 'Nomor telepon harus berupa angka, 10 sampai 13 digit.',
            'foto_ktp.image' => 'File harus berupa gambar.',
            'foto_ktp.mimes' => 'Foto KTP harus berformat JPG, JPEG, PNG, atau WEBP.',
            'foto_ktp.max' => 'Ukuran foto KTP maksimal 2 MB.',
            'foto_ktp.uploaded' => 'Foto KTP gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $pelanggan = Pelanggan::cari(trim($filters['q'] ?? ''))
            ->orderByRaw('CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        unset($data['foto_ktp'], $data['hapus_foto']);

        $data['wa_reminder_opt_in'] = $request->boolean('wa_reminder_opt_in');
        $data['wa_reminder_opted_in_at'] = $data['wa_reminder_opt_in']
            ? now(config('kredit.zona_waktu', 'Asia/Jakarta'))
            : null;

        if ($request->hasFile('foto_ktp')) {
            $data['foto_ktp'] = $request->file('foto_ktp')->store('pelanggan', 'local');
        }

        try {
            $pelanggan = DB::transaction(function () use ($data) {
                return Pelanggan::create(
                    ['id_pelanggan' => Pelanggan::generateId()] + $data
                );
            });
        } catch (\Throwable $e) {
            // Jika gagal menyimpan ke database, hapus file yang terlanjur diunggah.
            if (! empty($data['foto_ktp'])) {
                Storage::disk('local')->delete($data['foto_ktp']);
            }

            throw $e;
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', "Pelanggan {$pelanggan->nama_pelanggan} berhasil ditambahkan.");
    }

    public function show(Pelanggan $pelanggan)
    {
        return view('pelanggan.show', compact('pelanggan'));
    }

    public function fotoKtp(Pelanggan $pelanggan)
    {
        abort_unless(in_array(auth()->user()?->peran, ['Staff', 'Pimpinan'], true), 403);
        abort_unless($pelanggan->foto_ktp, 404);

        $disk = Storage::disk('local')->exists($pelanggan->foto_ktp) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($pelanggan->foto_ktp), 404);

        return Storage::disk($disk)->response(
            $pelanggan->foto_ktp,
            'foto-ktp-'.$pelanggan->id_pelanggan.'.'.pathinfo($pelanggan->foto_ktp, PATHINFO_EXTENSION),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate($this->rules($pelanggan), $this->messages());

        unset($data['foto_ktp'], $data['hapus_foto']);

        $data['wa_reminder_opt_in'] = $request->boolean('wa_reminder_opt_in');
        $data['wa_reminder_opted_in_at'] = $data['wa_reminder_opt_in']
            ? ($pelanggan->wa_reminder_opt_in
                ? $pelanggan->wa_reminder_opted_in_at
                : now(config('kredit.zona_waktu', 'Asia/Jakarta')))
            : null;

        $fotoLama = $pelanggan->foto_ktp;
        $fotoBaru = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoBaru = $request->file('foto_ktp')->store('pelanggan', 'local');
            $data['foto_ktp'] = $fotoBaru;
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto_ktp'] = null;
        }

        try {
            // Simpan data terlebih dahulu; file lama dibersihkan hanya setelah DB berhasil.
            $pelanggan->update($data);
        } catch (\Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('local')->delete($fotoBaru);
            }

            throw $e;
        }

        if ($fotoLama && $fotoLama !== $pelanggan->foto_ktp) {
            Storage::disk('local')->delete($fotoLama);
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if ($pelanggan->pemesanan()->exists()) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena sudah memiliki pemesanan.');
        }

        $foto = $pelanggan->foto_ktp;

        $pelanggan->delete();

        if ($foto) {
            Storage::disk('public')->delete($foto);
            Storage::disk('local')->delete($foto);
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }

    /**
     * Endpoint pencarian (JSON) untuk dropdown pelanggan di form pemesanan.
     */
    public function cari(Request $request)
    {
        $filters = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        return response()->json(
            Pelanggan::cari(trim($filters['q']))
                ->orderBy('nama_pelanggan')
                ->limit(20)
                ->get(['id_pelanggan', 'nama_pelanggan'])
        );
    }
}
