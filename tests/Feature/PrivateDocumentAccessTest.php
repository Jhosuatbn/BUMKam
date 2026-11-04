<?php

namespace Tests\Feature;

use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PrivateDocumentAccessTest extends TestCase
{
    public function test_document_routes_require_authentication(): void
    {
        foreach (['pelanggan.foto-ktp', 'pembayaran.bukti', 'jaminan.document'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route {$name} harus terdaftar.");
            $this->assertContains('auth', $route->gatherMiddleware(), "Route {$name} harus memerlukan login.");
        }

        $this->assertNull(app('router')->getRoutes()->getByName('storage.local'), 'Private disk tidak boleh memiliki route file generik.');
    }

    public function test_staff_and_pimpinan_can_read_private_ktp_through_controller_only(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('pelanggan/ktp.png', 'not-a-real-document');
        $pelanggan = (new Pelanggan())->forceFill([
            'id_pelanggan' => 'PLG-TEST',
            'foto_ktp' => 'pelanggan/ktp.png',
        ]);

        foreach (['Staff', 'Pimpinan'] as $role) {
            $this->actingAs($this->userWithRole($role));
            $response = app(PelangganController::class)->fotoKtp($pelanggan);
            $this->assertSame(200, $response->getStatusCode());
        }

        Storage::disk('public')->assertMissing('pelanggan/ktp.png');
    }

    public function test_staff_and_pimpinan_can_read_private_payment_proof_through_controller_only(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('bukti-pembayaran/proof.jpg', 'payment-proof-content');
        $pembayaran = (new Pembayaran())->forceFill([
            'id_pembayaran' => 'BYR-TEST',
            'bukti_pembayaran' => 'bukti-pembayaran/proof.jpg',
        ]);

        foreach (['Staff', 'Pimpinan'] as $role) {
            $this->actingAs($this->userWithRole($role));
            $response = app(PembayaranController::class)->bukti($pembayaran);
            $this->assertSame(200, $response->getStatusCode());
        }

        Storage::disk('public')->assertMissing('bukti-pembayaran/proof.jpg');
    }

    public function test_unprivileged_role_and_unauthenticated_controller_access_are_denied(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('pelanggan/ktp.png', 'test-content');
        $pelanggan = (new Pelanggan())->forceFill(['id_pelanggan' => 'PLG-TEST', 'foto_ktp' => 'pelanggan/ktp.png']);

        $this->actingAs($this->userWithRole('Pengunjung'));
        try {
            app(PelangganController::class)->fotoKtp($pelanggan);
            $this->fail('Role selain Staff/Pimpinan seharusnya ditolak.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $pembayaran = (new Pembayaran())->forceFill([
            'id_pembayaran' => 'BYR-TEST',
            'bukti_pembayaran' => 'bukti-pembayaran/proof.jpg',
        ]);
        Storage::disk('local')->put('bukti-pembayaran/proof.jpg', 'payment-proof');
        try {
            app(PembayaranController::class)->bukti($pembayaran);
            $this->fail('Role selain Staff/Pimpinan seharusnya ditolak dari bukti pembayaran.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        auth()->logout();
        try {
            app(PelangganController::class)->fotoKtp($pelanggan);
            $this->fail('Akses tanpa login seharusnya ditolak oleh controller.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_missing_private_documents_return_not_found(): void
    {
        Storage::fake('local');
        $this->actingAs($this->userWithRole('Staff'));
        $pelanggan = (new Pelanggan())->forceFill(['id_pelanggan' => 'PLG-MISSING', 'foto_ktp' => 'pelanggan/no-file.png']);

        try {
            app(PelangganController::class)->fotoKtp($pelanggan);
            $this->fail('Dokumen yang tidak ada seharusnya menghasilkan 404.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $pembayaran = (new Pembayaran())->forceFill(['id_pembayaran' => 'BYR-MISSING', 'bukti_pembayaran' => 'bukti-pembayaran/missing.pdf']);
        try {
            app(PembayaranController::class)->bukti($pembayaran);
            $this->fail('Bukti pembayaran yang tidak ada seharusnya menghasilkan 404.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    private function userWithRole(string $role): Pengguna
    {
        return (new Pengguna())->forceFill([
            'id_pengguna' => 'PGN-TEST',
            'username' => 'test-user',
            'nama_lengkap' => 'Test User',
            'peran' => $role,
            'is_active' => true,
        ]);
    }
}
