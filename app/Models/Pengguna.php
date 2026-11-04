<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Pengguna extends Authenticatable
{
    protected $table = 'pengguna';

    protected $primaryKey = 'id_pengguna';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pengguna',
        'username',
        'password',
        'nama_lengkap',
        'peran',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getAuthIdentifierName()
    {
        return 'id_pengguna';
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}
