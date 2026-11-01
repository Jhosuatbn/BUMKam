<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->bigIncrements('id_aktivitas');
            $table->string('id_pengguna', 20)->nullable()->index();
            $table->string('nama_pengguna', 100)->default('Sistem');
            $table->string('peran_pengguna', 20)->nullable();
            $table->string('jenis_aktivitas', 40)->index();
            $table->string('modul', 60)->index();
            $table->text('deskripsi');
            $table->string('ip_address', 45)->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
