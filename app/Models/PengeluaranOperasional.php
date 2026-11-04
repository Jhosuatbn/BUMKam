<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranOperasional extends Model
{
    protected $table = 'pengeluaran_operasional';
    protected $primaryKey = 'id_pengeluaran';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_pengeluaran', 'tanggal_pengeluaran', 'kategori', 'keterangan', 'nominal', 'dicatat_oleh'];
    protected $casts = ['tanggal_pengeluaran' => 'date'];
    public function pencatat() { return $this->belongsTo(Pengguna::class, 'dicatat_oleh', 'id_pengguna'); }
}
