<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('metode_pembayaran', 20)->default('tunai')->after('status_pembayaran');
            $table->string('status_gateway', 30)->default('settlement')->after('metode_pembayaran');
            $table->string('snap_token', 255)->nullable()->after('status_gateway');
            $table->text('gateway_redirect_url')->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'metode_pembayaran',
                'status_gateway',
                'snap_token',
                'gateway_redirect_url',
            ]);
        });
    }
};
