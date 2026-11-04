<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlokasiPembayaranPengembalian extends Model
{
    public $timestamps = false;
    protected $table = 'alokasi_pembayaran_pengembalian';
    protected $primaryKey = 'id_alokasi_pembayaran_pengembalian';
    protected $fillable = [
        'id_midtrans_refund_event', 'id_alokasi_pembayaran', 'id_pembayaran',
        'nominal_pokok_dibalik', 'nominal_denda_dibalik', 'created_at',
    ];

    public function alokasi() { return $this->belongsTo(AlokasiPembayaran::class, 'id_alokasi_pembayaran', 'id_alokasi_pembayaran'); }
    public function event() { return $this->belongsTo(MidtransRefundEvent::class, 'id_midtrans_refund_event', 'id_midtrans_refund_event'); }
}
