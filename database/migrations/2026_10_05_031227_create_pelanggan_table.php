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
            $table->integer('nik', 16)->unique();
            $table->integer('no_telepon', 13)->unique();
            $table->text('alamat');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};