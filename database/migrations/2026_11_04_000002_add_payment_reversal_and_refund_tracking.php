<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->decimal('nominal_dikembalikan', 15, 2)->default(0)->after('nominal');
        });

        Schema::create('pembayaran_reversal', function (Blueprint $table) {
            $table->bigIncrements('id_pembayaran_reversal');
            $table->string('id_pembayaran', 20)->unique();
            $table->decimal('nominal', 15, 2);
            $table->text('alasan');
            $table->string('id_pengguna', 20);
            $table->timestamps();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->restrictOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->restrictOnDelete();
        });

        Schema::create('midtrans_refund_events', function (Blueprint $table) {
            $table->bigIncrements('id_midtrans_refund_event');
            $table->string('id_pembayaran', 20);
            $table->char('event_key', 64)->unique();
            $table->string('refund_provider_id', 100)->nullable();
            $table->decimal('nominal_refund', 15, 2);
            $table->decimal('nominal_refund_kumulatif', 15, 2);
            $table->timestamp('diterima_pada')->useCurrent();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->restrictOnDelete();
            $table->index(['id_pembayaran', 'diterima_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_refund_events');
        Schema::dropIfExists('pembayaran_reversal');
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('nominal_dikembalikan');
        });
    }
};
