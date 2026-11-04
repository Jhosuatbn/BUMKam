<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPelanggan extends Model
{
    protected $table = 'retur_pelanggan';
    protected $primaryKey = 'id_retur';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_retur', 'id_pemesanan', 'dicatat_oleh', 'status', 'alasan', 'diputuskan_oleh', 'diputuskan_pada', 'catatan_keputusan'];
    protected $casts = ['diputuskan_pada' => 'datetime'];
    public function pemesanan() { return $this->belongsTo(Pemesanan::class, 'id_pemesanan', 'id_pemesanan'); }
    public function details() { return $this->hasMany(DetailRetur::class, 'id_retur', 'id_retur'); }
    public function pencatat() { return $this->belongsTo(Pengguna::class, 'dicatat_oleh', 'id_pengguna'); }
    public function pemutus() { return $this->belongsTo(Pengguna::class, 'diputuskan_oleh', 'id_pengguna'); }
}
