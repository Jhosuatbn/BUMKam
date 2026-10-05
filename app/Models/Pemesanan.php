<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $primaryKey = 'id_pemesanan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pemesanan',
        'id_pelanggan',
        'tanggal_pemesanan',
        'skema_pembayaran',
        'dp',
        'tenor',
        'jaminan',
        'bunga',
        'total_harga',
        'angsuran',
        'status_pemesanan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pemesanan');
    }
}