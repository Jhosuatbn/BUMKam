<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffTambahanSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya ubah label bawaan yang belum disesuaikan oleh pemilik sistem.
        Pengguna::where('username', 'admin')
            ->where('nama_lengkap', 'Staff BUMKam')
            ->update(['nama_lengkap' => 'Staff 1']);

        // Password acak tidak ditampilkan. Pimpinan menetapkan password yang diketahui Staff 2
        // lewat menu Pengguna sebelum akun dipakai.
        Pengguna::firstOrCreate(
            ['username' => 'staff2'],
            [
                'id_pengguna' => 'PGN-' . Str::upper(Str::random(12)),
                'password' => Hash::make(Str::random(48)),
                'nama_lengkap' => 'Staff 2',
                'peran' => 'Staff',
                'is_active' => true,
            ]
        );
    }
}
