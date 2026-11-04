<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailFilterValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_date_filter_returns_validation_error_instead_of_server_error(): void
    {
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-AUDIT-FILTER', 'username' => 'audit_filter_leader', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Audit', 'peran' => 'Pimpinan',
        ]);

        $this->actingAs($leader)
            ->from(route('dashboard'))
            ->get(route('audit-trail.index', ['dari' => 'bukan-tanggal']))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('dari');

        $this->actingAs($leader)
            ->from(route('dashboard'))
            ->get(route('audit-trail.index', ['dari' => '2026-11-10', 'sampai' => '2026-11-01']))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('sampai');
    }

    public function test_audit_filter_shows_all_existing_specific_activity_types(): void
    {
        $leader = Pengguna::create([
            'id_pengguna' => 'PGN-AUDIT-TYPES', 'username' => 'audit_types_leader', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Jenis Audit', 'peran' => 'Pimpinan',
        ]);

        $this->actingAs($leader)->get(route('audit-trail.index'))
            ->assertOk()
            ->assertSee('value="approve"', false)
            ->assertSee('value="reject"', false)
            ->assertSee('value="handover"', false)
            ->assertSee('value="cancel"', false)
            ->assertSee('value="reverse"', false)
            ->assertSee('value="penyesuaian_denda"', false);
    }
}
