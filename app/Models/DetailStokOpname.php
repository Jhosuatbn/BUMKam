<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailStokOpname extends Model
{
    protected $table = 'detail_stok_opname';
    protected $primaryKey = 'id_detail_opname';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_detail_opname', 'id_opname', 'id_barang', 'stok_sistem', 'stok_fisik'];
    public function opname() { return $this->belongsTo(StokOpname::class, 'id_opname', 'id_opname'); }
    public function barang() { return $this->belongsTo(Barang::class, 'id_barang', 'id_barang'); }
}
