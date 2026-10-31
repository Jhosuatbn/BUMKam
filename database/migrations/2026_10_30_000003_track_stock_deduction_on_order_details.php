<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            // Data historis tidak dianggap pernah memotong stok baru.
            $table->boolean('stok_dikurangi')->default(false)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table) {
            $table->dropColumn('stok_dikurangi');
        });
    }
};
