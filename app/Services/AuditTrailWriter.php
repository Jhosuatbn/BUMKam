<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class AuditTrailWriter
{
    public function record(?Pengguna $pengguna, Request $request, string $activity, string $module, string $description): void
    {
        AuditTrail::create([
            'id_pengguna' => $pengguna?->id_pengguna,
            'nama_pengguna' => $pengguna?->nama_lengkap ?? $pengguna?->username ?? 'Sistem',
            'peran_pengguna' => $pengguna?->peran,
            'jenis_aktivitas' => $activity,
            'modul' => $module,
            'deskripsi' => $description,
            'ip_address' => $request->ip(),
            // Payload tidak dicatat; ini mencegah NIK, password, dan isi dokumen ikut tersimpan.
            'old_value' => null,
            'new_value' => null,
            'created_at' => now(),
        ]);
    }
}
