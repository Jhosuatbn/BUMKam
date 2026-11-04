<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailRetur extends Model
{
    protected $table = 'detail_retur';
    protected $primaryKey = 'id_detail_retur';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_detail_retur', 'id_retur', 'id_detail_pemesanan', 'id_barang', 'jumlah', 'kondisi_barang', 'keterangan', 'masuk_stok'];
    protected $casts = ['masuk_stok' => 'boolean'];
    public function retur() { return $this->belongsTo(ReturPelanggan::class, 'id_retur', 'id_retur'); }
    public function detailPemesanan() { return $this->belongsTo(DetailPemesanan::class, 'id_detail_pemesanan', 'id_detail'); }
    public function barang() { return $this->belongsTo(Barang::class, 'id_barang', 'id_barang'); }
}
