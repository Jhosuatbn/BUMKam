<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaminan', function (Blueprint $table) {
            $table->string('id_jaminan', 30)->primary();
            $table->string('id_pemesanan', 20)->unique();
            $table->string('jenis_dokumen', 30);
            $table->string('nomor_dokumen', 100);
            $table->string('nama_pemilik', 100);
            $table->string('file_path');
            $table->date('tanggal_penyerahan');
            $table->string('status_verifikasi', 30)->default('Belum Diverifikasi');
            $table->text('alasan_penolakan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status_pengembalian', 30)->default('Masih Disimpan');
            $table->timestamp('tanggal_pengembalian')->nullable();
            $table->string('diverifikasi_oleh', 20)->nullable();
            $table->timestamps();

            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanan')->cascadeOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaminan');
    }
};
