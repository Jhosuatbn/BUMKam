<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pembayaran',
        'id_pemesanan',
        'tanggal_pembayaran',
        'nominal',
        'total_pembayaran',
        'sisa_pembayaran',
        'status_pembayaran',
        'metode_pembayaran',
        'status_gateway',
        'snap_token',
        'gateway_redirect_url',
        'bukti_pembayaran',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(
            Pemesanan::class,
            'id_pemesanan',
            'id_pemesanan'
        );
    }
}
