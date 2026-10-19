<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ];
    }

    private function messages(): array
    {
        return [
            'nama_pelanggan.regex' => 'Nama harus berupa teks dan tidak boleh hanya angka.',
            'nik.digits' => 'NIK harus berupa angka tepat 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'no_telepon.digits_between' => 'Nomor telepon harus berupa angka, 10 sampai 13 digit.',
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

        $pelanggan = DB::transaction(function () use ($data) {
            return Pelanggan::create(
                ['id_pelanggan' => Pelanggan::generateId()] + $data
            );
        });

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

        $pelanggan->delete();

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