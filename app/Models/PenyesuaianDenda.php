<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyesuaianDenda extends Model
{
    protected $table = 'penyesuaian_denda';
    protected $primaryKey = 'id_penyesuaian_denda';
    protected $fillable = ['id_pemesanan', 'nomor_angsuran', 'denda_awal', 'nominal_pengurangan', 'alasan', 'id_pimpinan', 'diputuskan_pada'];
    protected $casts = ['diputuskan_pada' => 'datetime'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class, 'id_pemesanan', 'id_pemesanan'); }
    public function pimpinan() { return $this->belongsTo(Pengguna::class, 'id_pimpinan', 'id_pengguna'); }
}
