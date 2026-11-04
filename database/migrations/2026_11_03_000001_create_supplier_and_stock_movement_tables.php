<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->string('id_supplier', 30)->primary();
            $table->string('nama_supplier', 150);
            $table->string('kontak_person', 100)->nullable();
            $table->string('no_telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->string('id_barang_masuk', 30)->primary();
            $table->string('id_supplier', 30);
            $table->string('nomor_faktur', 100)->nullable();
            $table->date('tanggal_masuk');
            $table->string('diterima_oleh', 20)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->foreign('id_supplier')->references('id_supplier')->on('supplier')->restrictOnDelete();
            $table->foreign('diterima_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        Schema::create('detail_barang_masuk', function (Blueprint $table) {
            $table->string('id_detail_masuk', 30)->primary();
            $table->string('id_barang_masuk', 30);
            $table->string('id_barang', 20);
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
            $table->foreign('id_barang_masuk')->references('id_barang_masuk')->on('barang_masuk')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->restrictOnDelete();
            $table->index('id_barang');
        });

        Schema::create('pergerakan_stok', function (Blueprint $table) {
            $table->string('id_pergerakan', 30)->primary();
            $table->string('id_barang', 20);
            $table->string('id_pengguna', 20)->nullable();
            $table->string('jenis', 30);
            $table->integer('jumlah_perubahan');
            $table->unsignedInteger('stok_sebelum');
            $table->unsignedInteger('stok_sesudah');
            $table->string('referensi_tipe', 40)->nullable();
            $table->string('referensi_id', 30)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->restrictOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->index(['id_barang', 'created_at']);
            $table->index(['referensi_tipe', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stok');
        Schema::dropIfExists('detail_barang_masuk');
        Schema::dropIfExists('barang_masuk');
        Schema::dropIfExists('supplier');
    }
};
