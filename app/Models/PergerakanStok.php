<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PergerakanStok extends Model
{
    public $timestamps = false;
    protected $table = 'pergerakan_stok';
    protected $primaryKey = 'id_pergerakan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_pergerakan', 'id_barang', 'id_pengguna', 'jenis', 'jumlah_perubahan', 'stok_sebelum', 'stok_sesudah', 'referensi_tipe', 'referensi_id', 'catatan', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];
    public function barang() { return $this->belongsTo(Barang::class, 'id_barang', 'id_barang'); }
    public function pengguna() { return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna'); }
}
