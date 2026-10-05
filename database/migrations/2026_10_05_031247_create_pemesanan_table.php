<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->string('id_pemesanan', 20)->primary();

            $table->string('id_pelanggan', 20);

            $table->date('tanggal_pemesanan');
            $table->string('skema_pembayaran', 20);

            $table->decimal('dp', 15, 2)->default(0);
            $table->integer('tenor')->default(0);
            $table->string('jaminan', 255)->nullable();
            $table->decimal('bunga', 15, 2)->default(0);

            $table->decimal('total_harga', 15, 2);
            $table->decimal('angsuran', 15, 2)->default(0);

            $table->string('status_pemesanan', 30);

            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};