<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    protected $table = 'detail_pemesanan';

    protected $primaryKey = 'id_detail';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_detail',
        'id_pemesanan',
        'id_barang',
        'jumlah',
        'harga',
        'subtotal',
        'stok_dikurangi',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
