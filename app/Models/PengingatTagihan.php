<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengingatTagihan extends Model
{
    protected $table = 'pengingat_tagihan';

    protected $fillable = [
        'id_pemesanan',
        'nomor_angsuran',
        'tanggal_jatuh_tempo',
        'jenis',
        'status',
        'pesan',
        'respons_gateway',
        'terkirim_pada',
    ];

    protected function casts(): array
    {
        return ['tanggal_jatuh_tempo' => 'date', 'terkirim_pada' => 'datetime'];
    }
}
