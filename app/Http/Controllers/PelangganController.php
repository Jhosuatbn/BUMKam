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
        $pelanggan = Pelanggan::cari($request->q)
            ->orderByRaw('CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        // Hanya pratinjau; ID final dibuat ulang saat data disimpan.
        $nextId = Pelanggan::generateId();

        return view('pelanggan.create', compact('nextId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        unset($data['foto_ktp'], $data['hapus_foto']);

        if ($request->hasFile('foto_ktp')) {
            // Disimpan di storage/app/public/pelanggan
            $data['foto_ktp'] = $request->file('foto_ktp')->store('pelanggan', 'public');
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
                Storage::disk('public')->delete($data['foto_ktp']);
            }

            throw $e;
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', "Pelanggan {$pelanggan->id_pelanggan} berhasil ditambahkan.");
    }

    public function show(Pelanggan $pelanggan)
    {
        return view('pelanggan.show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate($this->rules($pelanggan), $this->messages());

        unset($data['foto_ktp'], $data['hapus_foto']);

        if ($request->hasFile('foto_ktp')) {
            // Ganti foto: hapus file lama, simpan yang baru.
            if ($pelanggan->foto_ktp) {
                Storage::disk('public')->delete($pelanggan->foto_ktp);
            }

            $data['foto_ktp'] = $request->file('foto_ktp')->store('pelanggan', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            // Hanya hapus foto, tanpa menggantinya.
            if ($pelanggan->foto_ktp) {
                Storage::disk('public')->delete($pelanggan->foto_ktp);
            }

            $data['foto_ktp'] = null;
        }

        // id_pelanggan tidak ikut diubah.
        $pelanggan->update($data);

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
        return response()->json(
            Pelanggan::cari($request->q)
                ->orderBy('nama_pelanggan')
                ->limit(20)
                ->get(['id_pelanggan', 'nama_pelanggan', 'nik', 'no_telepon', 'alamat'])
        );
    }
}