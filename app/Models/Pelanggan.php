<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pelanggan',
        'nama_pelanggan',
        'nik',
        'no_telepon',
        'wa_reminder_opt_in',
        'wa_reminder_opted_in_at',
        'alamat',
        'foto_ktp',
    ];

    protected function casts(): array
    {
        return [
            'wa_reminder_opt_in' => 'boolean',
            'wa_reminder_opted_in_at' => 'datetime',
        ];
    }

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'id_pelanggan');
    }

    /** Authenticated endpoint for viewing the customer's KTP image. */
    public function getFotoKtpUrlAttribute(): ?string
    {
        return $this->foto_ktp ? route('pelanggan.foto-ktp', $this) : null;
    }

    /**
     * Membuat ID pelanggan berikutnya, contoh: PLG-01, PLG-02, ... PLG-99, PLG-100.
     *
     * Dipanggil di dalam DB::transaction() saat menyimpan data supaya
     * tidak terjadi ID kembar jika dua staff menyimpan bersamaan.
     */
    public static function generateId(): string
    {
        // Ambil angka terbesar dari bagian setelah "PLG-" (bukan urut string,
        // karena secara alfabet "PLG-100" lebih kecil dari "PLG-99").
        $max = static::lockForUpdate()
            ->max(DB::raw('CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)'));

        return 'PLG-' . str_pad(((int) $max) + 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Pencarian pelanggan berdasarkan ID, nama, NIK, atau nomor telepon.
     *
     * Pemakaian: Pelanggan::cari($request->q)->get();
     */
    public function scopeCari($query, ?string $kata)
    {
        return $query->when($kata, function ($q) use ($kata) {
            $q->where(function ($sub) use ($kata) {
                $sub->where('nama_pelanggan', 'like', "%{$kata}%")
                    ->orWhere('id_pelanggan', 'like', "%{$kata}%")
                    ->orWhere('nik', 'like', "%{$kata}%")
                    ->orWhere('no_telepon', 'like', "%{$kata}%");
            });
        });
    }
}
