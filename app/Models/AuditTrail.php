<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $table = 'audit_trails';
    protected $primaryKey = 'id_aktivitas';
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna', 'nama_pengguna', 'peran_pengguna', 'jenis_aktivitas',
        'modul', 'deskripsi', 'ip_address', 'old_value', 'new_value', 'created_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
