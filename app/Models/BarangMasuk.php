<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id_barang_masuk';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_barang_masuk', 'id_supplier', 'nomor_faktur', 'tanggal_masuk', 'diterima_oleh', 'catatan'];
    protected $casts = ['tanggal_masuk' => 'date'];

    public function supplier() { return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier'); }
    public function details() { return $this->hasMany(DetailBarangMasuk::class, 'id_barang_masuk', 'id_barang_masuk'); }
    public function penerima() { return $this->belongsTo(Pengguna::class, 'diterima_oleh', 'id_pengguna'); }
}
