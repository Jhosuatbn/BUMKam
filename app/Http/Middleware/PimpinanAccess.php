<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PimpinanAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        // Pengguna selain Pimpinan tetap menggunakan akses sebelumnya.
        if (!$pengguna || $pengguna->peran !== 'Pimpinan') {
            return $next($request);
        }

        // Halaman yang boleh diakses oleh Pimpinan.
        $halamanDiizinkan = [
            'dashboard',
            'laporan.index',
            'pelanggan.index',
            'pelanggan.show',
            'barang.index',
            'barang.show',
            'pemesanan.index',
            'pemesanan.show',
            'pemesanan.approve',
            'monitoring-kredit.index',
            'monitoring-kredit.jadwal',
            'monitoring-kredit.denda',
            'monitoring-kredit.denda.reduce',
            'monitoring-kredit.show',
            'jaminan.index',
            'jaminan.show',
            'jaminan.document',
            'jaminan.verify',
            'audit-trail.index',
            'pengguna.index',
            'pengguna.create',
            'pengguna.store',
            'pengguna.show',
            'pengguna.edit',
            'pengguna.update',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $halamanDiizinkan, true)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
