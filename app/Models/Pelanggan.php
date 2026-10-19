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
        'alamat',
    ];

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'id_pelanggan');
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