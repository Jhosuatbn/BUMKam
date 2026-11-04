<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_pelanggan', function (Blueprint $table) {
            $table->string('id_retur', 30)->primary();
            $table->string('id_pemesanan', 20);
            $table->string('dicatat_oleh', 20);
            $table->string('status', 30)->default('Menunggu Persetujuan');
            $table->text('alasan');
            $table->string('diputuskan_oleh', 20)->nullable();
            $table->timestamp('diputuskan_pada')->nullable();
            $table->text('catatan_keputusan')->nullable();
            $table->timestamps();
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('pemesanan')->restrictOnDelete();
            $table->foreign('dicatat_oleh')->references('id_pengguna')->on('pengguna')->restrictOnDelete();
            $table->foreign('diputuskan_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });

        Schema::create('detail_retur', function (Blueprint $table) {
            $table->string('id_detail_retur', 30)->primary();
            $table->string('id_retur', 30);
            $table->string('id_detail_pemesanan', 20);
            $table->string('id_barang', 20);
            $table->unsignedInteger('jumlah');
            $table->string('kondisi_barang', 30);
            $table->text('keterangan')->nullable();
            $table->boolean('masuk_stok')->nullable();
            $table->timestamps();
            $table->foreign('id_retur')->references('id_retur')->on('retur_pelanggan')->cascadeOnDelete();
            $table->foreign('id_detail_pemesanan')->references('id_detail')->on('detail_pemesanan')->restrictOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->restrictOnDelete();
            $table->index(['id_detail_pemesanan', 'id_retur']);
        });

        Schema::create('stok_opname', function (Blueprint $table) {
            $table->string('id_opname', 30)->primary();
            $table->date('tanggal_opname');
            $table->string('dicatat_oleh', 20);
            $table->string('status', 30)->default('Menunggu Persetujuan');
            $table->text('alasan');
            $table->string('diputuskan_oleh', 20)->nullable();
            $table->timestamp('diputuskan_pada')->nullable();
            $table->text('catatan_keputusan')->nullable();
            $table->timestamps();
            $table->foreign('dicatat_oleh')->references('id_pengguna')->on('pengguna')->restrictOnDelete();
            $table->foreign('diputuskan_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->index(['status', 'tanggal_opname']);
        });

        Schema::create('detail_stok_opname', function (Blueprint $table) {
            $table->string('id_detail_opname', 30)->primary();
            $table->string('id_opname', 30);
            $table->string('id_barang', 20);
            $table->unsignedInteger('stok_sistem');
            $table->unsignedInteger('stok_fisik');
            $table->timestamps();
            $table->foreign('id_opname')->references('id_opname')->on('stok_opname')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->restrictOnDelete();
            $table->unique(['id_opname', 'id_barang']);
        });

        Schema::create('pengeluaran_operasional', function (Blueprint $table) {
            $table->string('id_pengeluaran', 30)->primary();
            $table->date('tanggal_pengeluaran');
            $table->string('kategori', 100);
            $table->string('keterangan', 255);
            $table->decimal('nominal', 15, 2);
            $table->string('dicatat_oleh', 20);
            $table->timestamps();
            $table->foreign('dicatat_oleh')->references('id_pengguna')->on('pengguna')->restrictOnDelete();
            $table->index(['tanggal_pengeluaran', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_operasional');
        Schema::dropIfExists('detail_stok_opname');
        Schema::dropIfExists('stok_opname');
        Schema::dropIfExists('detail_retur');
        Schema::dropIfExists('retur_pelanggan');
    }
};
