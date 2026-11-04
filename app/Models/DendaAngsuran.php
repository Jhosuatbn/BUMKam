<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DendaAngsuran extends Model
{
    protected $table = 'denda_angsuran';
    protected $primaryKey = 'id_denda_angsuran';
    protected $fillable = ['id_pemesanan', 'nomor_angsuran', 'denda_awal', 'ditetapkan_pada'];
    protected $casts = ['ditetapkan_pada' => 'datetime'];
}
