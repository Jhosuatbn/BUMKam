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
        'status_persetujuan',
        'persetujuan_oleh',
        'persetujuan_pada',
        'catatan_persetujuan',
    ];

    protected $casts = [
        'persetujuan_pada' => 'datetime',
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

    public function penyesuaianDenda()
    {
        return $this->hasMany(PenyesuaianDenda::class, 'id_pemesanan', 'id_pemesanan');
    }

    public function dendaAngsuran()
    {
        return $this->hasMany(DendaAngsuran::class, 'id_pemesanan', 'id_pemesanan');
    }

    public function jaminanDokumen()
    {
        return $this->hasOne(Jaminan::class, 'id_pemesanan', 'id_pemesanan');
    }

    public function persetujuanOleh()
    {
        return $this->belongsTo(Pengguna::class, 'persetujuan_oleh', 'id_pengguna');
    }

    public function riwayatPersetujuan()
    {
        return $this->hasMany(RiwayatPersetujuanPemesanan::class, 'id_pemesanan', 'id_pemesanan')
            ->orderByDesc('created_at');
    }
}
