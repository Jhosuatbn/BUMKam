<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_page_loads_for_authenticated_user(): void
    {
        Pengguna::create([
            'id_pengguna' => 'PGN-TEST',
            'username' => 'admin_test',
            'password' => bcrypt('admin123'),
            'nama_lengkap' => 'Test Admin',
            'peran' => 'Pimpinan',
        ]);

        $this->actingAs(Pengguna::first(), 'web');

        $response = $this->get('/laporan');

        $response->assertStatus(200);
        $response->assertSee('Laporan');
    }
}
