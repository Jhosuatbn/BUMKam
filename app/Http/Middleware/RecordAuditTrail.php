<?php

namespace App\Http\Middleware;

use App\Services\AuditTrailWriter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditTrail
{
    /** Routes whose controllers already write a descriptive audit event. */
    private const EXPLICITLY_AUDITED_ROUTES = [
        'barang.store',
        'barang.update',
        'barang-masuk.store',
        'supplier.store',
        'supplier.update',
        'supplier.destroy',
        'pemesanan.approve',
        'pemesanan.handover',
        'pemesanan.cancel',
        'pembayaran.reverse',
        'jaminan.update',
        'monitoring-kredit.denda.reduce',
    ];

    public function __construct(private readonly AuditTrailWriter $writer)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();
        $response = $next($request);
        $actor ??= $request->user();
        $route = $request->route();
        $routeName = $route?->getName();

        if (! $routeName || $response->getStatusCode() >= 400) {
            return $response;
        }

        $method = $request->method();
        if ($routeName === 'login.process' && $response->getStatusCode() < 400 && $request->user()) {
            $this->writer->record($request->user(), $request, 'login', 'Autentikasi', 'Pengguna berhasil login.');
            return $response;
        }
        if ($routeName === 'logout') {
            $this->writer->record($actor, $request, 'logout', 'Autentikasi', 'Pengguna logout.');
            return $response;
        }
        $successFlashWasAdded = in_array(
            'success',
            (array) $request->session()->get('_flash.new', []),
            true
        );
        if (! $response->isSuccessful() && ! ($response->isRedirect() && $successFlashWasAdded)) {
            return $response;
        }
        $action = match ($method) {
            'POST' => 'Menambahkan / memproses',
            'PUT', 'PATCH' => 'Memperbarui',
            'DELETE' => 'Menghapus',
            default => null,
        };
        if ($action === null || str_starts_with($routeName, 'audit-trail.')) {
            return $response;
        }

        if (in_array($routeName, self::EXPLICITLY_AUDITED_ROUTES, true)) {
            return $response;
        }

        $module = match (true) {
            str_starts_with($routeName, 'pelanggan.') => 'Pelanggan',
            str_starts_with($routeName, 'barang.') => 'Barang',
            str_starts_with($routeName, 'barang-masuk.') => 'Barang Masuk',
            str_starts_with($routeName, 'supplier.') => 'Supplier',
            str_starts_with($routeName, 'retur.') => 'Retur Pelanggan',
            str_starts_with($routeName, 'stok-opname.') => 'Stok Opname',
            str_starts_with($routeName, 'pengeluaran.') => 'Pengeluaran Operasional',
            str_starts_with($routeName, 'pemesanan.') => 'Pemesanan',
            str_starts_with($routeName, 'pembayaran.') => 'Pembayaran',
            str_starts_with($routeName, 'pengguna.') => 'Pengguna',
            str_starts_with($routeName, 'jaminan.') => 'Jaminan',
            str_starts_with($routeName, 'monitoring-kredit.') => 'Monitoring Kredit',
            default => 'Sistem',
        };
        $activity = match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
        };
        if (str_contains($routeName, 'verifikasi') || str_contains($routeName, '.verify')) $activity = 'verify';
        if (str_contains($routeName, '.decide')) $activity = 'verify';
        if ($routeName === 'pengguna.update' && $request->filled('is_active')) $activity = 'account_status';
        if ($routeName === 'pengguna.update' && $request->filled('password')) $activity = 'password_reset';

        $description = $action.' pada modul '.$module.' ('.$routeName.').';
        if ($routeName === 'jaminan.verify') {
            $jaminan = $route->parameter('jaminan');
            $keputusan = $request->input('status_verifikasi');
            $keteranganKeputusan = $keputusan === 'Ditolak' ? 'ditolak' : 'disetujui';
            $description = 'Jaminan '.($jaminan?->id_jaminan ?? '').' '.$keteranganKeputusan.' oleh Pimpinan.';
        }

        $this->writer->record(
            $actor,
            $request,
            $activity,
            $module,
            $description
        );

        return $response;
    }
}
