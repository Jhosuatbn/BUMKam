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
            'pelanggan.foto-ktp',
            'barang.index',
            'barang.show',
            'supplier.index',
            'supplier.show',
            'barang-masuk.index',
            'barang-masuk.show',
            'monitoring-stok.index',
            'retur.index',
            'retur.show',
            'retur.decide',
            'stok-opname.index',
            'stok-opname.show',
            'stok-opname.decide',
            'pengeluaran.index',
            'pemesanan.index',
            'pemesanan.persetujuan',
            'pemesanan.show',
            'pemesanan.approve',
            'pembayaran.index',
            'pembayaran.show',
            'pembayaran.bukti',
            'pembayaran.receipt',
            'pembayaran.reverse',
            'pembayaran.sync-midtrans',
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
