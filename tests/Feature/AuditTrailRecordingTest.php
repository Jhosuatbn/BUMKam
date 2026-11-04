<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_audited_supplier_action_is_not_duplicated_by_middleware(): void
    {
        $staff = $this->user('Staff', 'audit_supplier_staff');

        $this->actingAs($staff)->post(route('supplier.store'), [
            'nama_supplier' => 'Supplier Audit Uji',
            'kontak_person' => 'Kontak Uji',
            'no_telepon' => '081234567890',
        ])->assertRedirect(route('supplier.index'));

        $this->assertDatabaseCount('audit_trails', 1);
        $this->assertDatabaseHas('audit_trails', [
            'id_pengguna' => $staff->id_pengguna,
            'modul' => 'Supplier',
            'jenis_aktivitas' => 'create',
        ]);
    }

    public function test_unhandled_financial_module_route_is_recorded_under_its_module_name(): void
    {
        $staff = $this->user('Staff', 'audit_expense_staff');

        $this->actingAs($staff)->post(route('pengeluaran.store'), [
            'tanggal_pengeluaran' => now()->toDateString(),
            'kategori' => 'Operasional',
            'keterangan' => 'Biaya uji audit',
            'nominal' => 125000,
        ])->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseHas('audit_trails', [
            'id_pengguna' => $staff->id_pengguna,
            'modul' => 'Pengeluaran Operasional',
            'jenis_aktivitas' => 'create',
        ]);
        $this->assertSame('Pengeluaran Operasional', AuditTrail::latest('created_at')->value('modul'));
    }

    private function user(string $role, string $username): Pengguna
    {
        return Pengguna::create([
            'id_pengguna' => 'PGN-'.strtoupper(substr(md5($username), 0, 10)),
            'username' => $username,
            'password' => bcrypt('secret'),
            'nama_lengkap' => $username,
            'peran' => $role,
        ]);
    }
}
