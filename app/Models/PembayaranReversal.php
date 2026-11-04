<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranReversal extends Model
{
    protected $table = 'pembayaran_reversal';
    protected $primaryKey = 'id_pembayaran_reversal';
    protected $fillable = ['id_pembayaran', 'nominal', 'alasan', 'id_pengguna'];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
