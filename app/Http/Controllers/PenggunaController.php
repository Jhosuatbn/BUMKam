<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = Pengguna::all();

        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        return view('pengguna.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'id_pengguna' => 'required|string|max:20|unique:pengguna,id_pengguna',
        'username' => 'required|string|max:50|unique:pengguna,username',
        'password' => 'required|string|min:6',
        'nama_lengkap' => 'required|string|max:100',
        'peran' => 'required|in:Staff,Pimpinan',
    ]);

    Pengguna::create([
        'id_pengguna' => $request->id_pengguna,
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'nama_lengkap' => $request->nama_lengkap,
        'peran' => $request->peran,
    ]);

    return redirect()
        ->route('pengguna.index')
        ->with('success', 'Pengguna berhasil ditambahkan.');
}

    public function show(Pengguna $pengguna)
{
    return view('pengguna.show', compact('pengguna'));
}

    public function edit(Pengguna $pengguna)
{
    return view('pengguna.edit', compact('pengguna'));
}

public function update(Request $request, Pengguna $pengguna)
{
    $request->validate([
        'username' => 'required|string|max:50|unique:pengguna,username,' . $pengguna->id_pengguna . ',id_pengguna',
        'password' => 'nullable|string|min:6',
        'nama_lengkap' => 'required|string|max:100',
        'peran' => 'required|in:Staff,Pimpinan',
    ]);

    $data = [
        'username' => $request->username,
        'nama_lengkap' => $request->nama_lengkap,
        'peran' => $request->peran,
    ];

    // Password hanya diubah jika diisi
    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $pengguna->update($data);

    return redirect()
        ->route('pengguna.index')
        ->with('success', 'Pengguna berhasil diperbarui.');
}

    public function destroy(Pengguna $pengguna)
{
    $pengguna->delete();

    return redirect()
        ->route('pengguna.index')
        ->with('success', 'Pengguna berhasil dihapus.');
}
}