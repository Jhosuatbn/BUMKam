<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jaminan extends Model
{
    protected $table = 'jaminan';
    protected $primaryKey = 'id_jaminan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jaminan', 'id_pemesanan', 'jenis_dokumen', 'nomor_dokumen',
        'nama_pemilik', 'file_path', 'tanggal_penyerahan', 'status_verifikasi',
        'alasan_penolakan', 'catatan', 'status_pengembalian', 'tanggal_pengembalian',
        'diverifikasi_oleh',
        'diterima_oleh',
    ];

    protected $casts = [
        'tanggal_penyerahan' => 'date',
        'tanggal_pengembalian' => 'datetime',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan', 'id_pemesanan');
    }

    public function verifier()
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh', 'id_pengguna');
    }

    public function penerima()
    {
        return $this->belongsTo(Pengguna::class, 'diterima_oleh', 'id_pengguna');
    }

    /**
     * Normalize labels for records created before the current status wording.
     * Stored values remain untouched so existing databases need no migration.
     */
    public function getStatusVerifikasiLabelAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'Belum Diverifikasi' => 'Menunggu Verifikasi',
            'Terverifikasi' => 'Disetujui',
            default => $this->status_verifikasi ?: 'Menunggu Verifikasi',
        };
    }

    public function getStatusPengembalianLabelAttribute(): string
    {
        return $this->status_pengembalian === 'Sudah Dikembalikan'
            ? 'Dikembalikan'
            : ($this->status_pengembalian ?: 'Masih Disimpan');
    }
}
