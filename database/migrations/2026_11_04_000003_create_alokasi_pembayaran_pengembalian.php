<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_pembayaran_pengembalian', function (Blueprint $table) {
            $table->bigIncrements('id_alokasi_pembayaran_pengembalian');
            $table->unsignedBigInteger('id_midtrans_refund_event');
            $table->unsignedBigInteger('id_alokasi_pembayaran');
            $table->string('id_pembayaran', 20);
            $table->decimal('nominal_pokok_dibalik', 15, 2)->default(0);
            $table->decimal('nominal_denda_dibalik', 15, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_midtrans_refund_event')->references('id_midtrans_refund_event')->on('midtrans_refund_events')->restrictOnDelete();
            $table->foreign('id_alokasi_pembayaran')->references('id_alokasi_pembayaran')->on('alokasi_pembayaran')->restrictOnDelete();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->restrictOnDelete();
            $table->unique(['id_midtrans_refund_event', 'id_alokasi_pembayaran'], 'refund_allocation_unique');
            $table->index('id_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_pembayaran_pengembalian');
    }
};
