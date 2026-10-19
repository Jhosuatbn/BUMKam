<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun Staff
        Pengguna::firstOrCreate(
            ['username' => 'admin'],
            [
                'id_pengguna' => 'PGN-001',
                'password' => Hash::make('admin123'),
                'nama_lengkap' => 'Staff BUMKam',
                'peran' => 'Staff',
            ]
        );

        // Akun Pimpinan
        Pengguna::firstOrCreate(
            ['username' => 'pimpinan'],
            [
                'id_pengguna' => 'PGN-002',
                'password' => Hash::make('pimpinan123'),
                'nama_lengkap' => 'Pimpinan BUMKam',
                'peran' => 'Pimpinan',
            ]
        );
    }
}
