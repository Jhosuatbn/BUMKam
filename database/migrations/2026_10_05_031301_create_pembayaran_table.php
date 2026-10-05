<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id_pembayaran', 20)->primary();

            $table->string('id_pemesanan', 20);

            $table->date('tanggal_pembayaran');
            $table->decimal('nominal', 15, 2);
            $table->decimal('total_pembayaran', 15, 2);
            $table->decimal('sisa_pembayaran', 15, 2);

            $table->string('status_pembayaran', 30);

            $table->foreign('id_pemesanan')
                ->references('id_pemesanan')
                ->on('pemesanan')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};