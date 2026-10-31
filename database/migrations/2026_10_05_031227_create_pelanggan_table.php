<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 20)->primary();
            $table->string('nama_pelanggan', 100);

            // NIK dan no. telepon disimpan sebagai string (bukan integer):
            // - NIK 16 digit melebihi batas integer (maks 2.147.483.647)
            // - angka 0 di depan nomor telepon (08xx) akan hilang jika integer
            $table->string('nik', 16)->unique();
            $table->string('no_telepon', 13);

            $table->text('alamat');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};