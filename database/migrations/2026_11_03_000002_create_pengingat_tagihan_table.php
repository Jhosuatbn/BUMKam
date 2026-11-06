<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some local databases already have the older Fonnte reminder table.
        // Preserve it and let the compatibility migration add the newer fields.
        if (Schema::hasTable('pengingat_tagihan')) {
            return;
        }

        Schema::create('pengingat_tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('id_pemesanan', 20);
            $table->unsignedSmallInteger('nomor_angsuran');
            $table->date('tanggal_jatuh_tempo');
            $table->string('jenis', 20)->default('h-3');
            $table->string('status', 20)->default('pending');
            $table->text('pesan');
            $table->text('respons_gateway')->nullable();
            $table->timestamp('terkirim_pada')->nullable();
            $table->timestamps();

            $table->unique(
                ['id_pemesanan', 'nomor_angsuran', 'tanggal_jatuh_tempo', 'jenis'],
                'pengingat_tagihan_satu_kali_unik'
            );
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        // Never drop a pre-existing legacy reminder table and its history.
        if (Schema::hasTable('pengingat_tagihan') && ! Schema::hasColumn('pengingat_tagihan', 'fonnte_request_id')) {
            Schema::dropIfExists('pengingat_tagihan');
        }
    }
};
