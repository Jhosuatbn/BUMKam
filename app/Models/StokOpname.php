<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    protected $table = 'stok_opname';
    protected $primaryKey = 'id_opname';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_opname', 'tanggal_opname', 'dicatat_oleh', 'status', 'alasan', 'diputuskan_oleh', 'diputuskan_pada', 'catatan_keputusan'];
    protected $casts = ['tanggal_opname' => 'date', 'diputuskan_pada' => 'datetime'];
    public function details() { return $this->hasMany(DetailStokOpname::class, 'id_opname', 'id_opname'); }
    public function pencatat() { return $this->belongsTo(Pengguna::class, 'dicatat_oleh', 'id_pengguna'); }
    public function pemutus() { return $this->belongsTo(Pengguna::class, 'diputuskan_oleh', 'id_pengguna'); }
}
