<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denda_angsuran', function (Blueprint $table) {
            $table->bigIncrements('id_denda_angsuran');
            $table->string('id_pemesanan', 20);
            $table->unsignedInteger('nomor_angsuran');
            $table->decimal('denda_awal', 15, 2)->default(0);
            $table->timestamp('ditetapkan_pada');
            $table->timestamps();
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanan')->onDelete('restrict');
            $table->unique(['id_pemesanan', 'nomor_angsuran']);
        });

        Schema::create('penyesuaian_denda', function (Blueprint $table) {
            $table->bigIncrements('id_penyesuaian_denda');
            $table->string('id_pemesanan', 20);
            $table->unsignedInteger('nomor_angsuran');
            $table->decimal('denda_awal', 15, 2);
            $table->decimal('nominal_pengurangan', 15, 2);
            $table->text('alasan');
            $table->string('id_pimpinan', 20);
            $table->timestamp('diputuskan_pada');
            $table->timestamps();
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanan')->onDelete('restrict');
            $table->foreign('id_pimpinan')->references('id_pengguna')->on('pengguna')->onDelete('restrict');
            $table->index(['id_pemesanan', 'nomor_angsuran']);
        });

        Schema::create('alokasi_pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id_alokasi_pembayaran');
            $table->string('id_pembayaran', 20);
            $table->unsignedInteger('nomor_angsuran');
            $table->decimal('alokasi_angsuran', 15, 2)->default(0);
            $table->decimal('alokasi_denda', 15, 2)->default(0);
            $table->timestamps();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->onDelete('restrict');
            $table->unique(['id_pembayaran', 'nomor_angsuran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_pembayaran');
        Schema::dropIfExists('penyesuaian_denda');
        Schema::dropIfExists('denda_angsuran');
    }
};
