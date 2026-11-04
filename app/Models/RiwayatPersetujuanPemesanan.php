<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPersetujuanPemesanan extends Model
{
    protected $table = 'riwayat_persetujuan_pemesanan';
    protected $primaryKey = 'id_riwayat';
    public $timestamps = false;

    protected $fillable = [
        'id_pemesanan',
        'id_pengguna',
        'status_persetujuan',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan', 'id_pemesanan');
    }
}
