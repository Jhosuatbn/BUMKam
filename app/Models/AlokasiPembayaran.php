<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlokasiPembayaran extends Model
{
    protected $table = 'alokasi_pembayaran';
    protected $primaryKey = 'id_alokasi_pembayaran';
    protected $fillable = ['id_pembayaran', 'nomor_angsuran', 'alokasi_angsuran', 'alokasi_denda'];

    public function pembayaran() { return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran'); }
    public function pengembalian() { return $this->hasMany(AlokasiPembayaranPengembalian::class, 'id_alokasi_pembayaran', 'id_alokasi_pembayaran'); }
}
