<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBarangMasuk extends Model
{
    protected $table = 'detail_barang_masuk';
    protected $primaryKey = 'id_detail_masuk';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_detail_masuk', 'id_barang_masuk', 'id_barang', 'jumlah', 'harga_satuan', 'subtotal'];
    public function barang() { return $this->belongsTo(Barang::class, 'id_barang', 'id_barang'); }
    public function barangMasuk() { return $this->belongsTo(BarangMasuk::class, 'id_barang_masuk', 'id_barang_masuk'); }
}
