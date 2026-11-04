<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengingat_tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('id_pemesanan', 20);
            $table->unsignedSmallInteger('nomor_angsuran');
            $table->date('tanggal_jatuh_tempo');
            $table->string('status', 20)->default('terkirim');
            $table->string('fonnte_request_id', 40)->nullable();
            $table->text('pesan_error')->nullable();
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();

            $table->unique(['id_pemesanan', 'nomor_angsuran', 'tanggal_jatuh_tempo'], 'pengingat_tagihan_unik');
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_tagihan');
    }
};
