<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    private function pastikanPimpinan(): void
    {
        abort_unless(auth()->user()?->peran === 'Pimpinan', 403);
    }

    private function pastikanStaffTarget(Pengguna $pengguna): void
    {
        abort_unless($pengguna->peran === 'Staff' && $pengguna->id_pengguna !== auth()->id(), 403, 'Akun Pimpinan tidak dapat dikelola dari halaman ini.');
    }

    public function index()
    {
        $this->pastikanPimpinan();
        $pengguna = Pengguna::orderBy('created_at')->paginate(15)->withQueryString();

        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        $this->pastikanPimpinan();
        $idPengguna = $this->idPenggunaBerikutnya();

        return view('pengguna.create', compact('idPengguna'));
    }

    public function store(Request $request)
    {
        $this->pastikanPimpinan();
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:pengguna,username'],
            'password' => ['required', 'string', 'min:10'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
        ]);
        Pengguna::create(array_merge($data, [
            'id_pengguna' => $this->idPenggunaBerikutnya(),
            'password' => Hash::make($data['password']),
            'peran' => 'Staff',
            'is_active' => true,
        ]));

        return redirect()->route('pengguna.index')->with('success', 'Akun Staff berhasil dibuat. Sampaikan password awal secara aman kepada pemilik akun.');
    }

    private function idPenggunaBerikutnya(): string
    {
        $nomorTerbesar = Pengguna::query()->pluck('id_pengguna')->reduce(function (int $terbesar, string $id): int {
            return preg_match('/^PGN-(\d+)$/', $id, $matches)
                ? max($terbesar, (int) $matches[1])
                : $terbesar;
        }, 0);

        return 'PGN-' . str_pad((string) ($nomorTerbesar + 1), 3, '0', STR_PAD_LEFT);
    }

    public function show(Pengguna $pengguna)
    {
        $this->pastikanPimpinan();
        $pengguna->makeHidden('password');

        return view('pengguna.show', compact('pengguna'));
    }

    public function edit(Pengguna $pengguna)
    {
        $this->pastikanPimpinan();
        $this->pastikanStaffTarget($pengguna);

        return view('pengguna.edit', compact('pengguna'));
    }

    public function update(Request $request, Pengguna $pengguna)
    {
        $this->pastikanPimpinan();
        $this->pastikanStaffTarget($pengguna);
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('pengguna', 'username')->ignore($pengguna->id_pengguna, 'id_pengguna')],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:10'],
            'is_active' => ['required', 'boolean'],
        ]);
        $updates = [
            'username' => $data['username'],
            'nama_lengkap' => $data['nama_lengkap'],
            'is_active' => (bool) $data['is_active'],
        ];
        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }
        $pengguna->update($updates);

        return redirect()->route('pengguna.index')->with('success', 'Akun Staff berhasil diperbarui. Password tidak pernah ditampilkan kembali.');
    }

    public function destroy(Pengguna $pengguna)
    {
        abort(403, 'Akun tidak dihapus permanen. Nonaktifkan akun melalui menu edit.');
    }
}
