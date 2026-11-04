<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('status_penyerahan', 30)->default('Belum Diserahkan')->after('status_pemesanan')->index();
            $table->string('diserahkan_oleh', 20)->nullable()->after('status_penyerahan');
            $table->timestamp('diserahkan_pada')->nullable()->after('diserahkan_oleh');
            $table->foreign('diserahkan_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->string('dibatalkan_oleh', 20)->nullable()->after('diserahkan_pada');
            $table->timestamp('dibatalkan_pada')->nullable()->after('dibatalkan_oleh');
            $table->text('alasan_pembatalan')->nullable()->after('dibatalkan_pada');
            $table->foreign('dibatalkan_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        // Existing rows with stok_dikurangi=true already changed physical stock.
        // Mark them as legacy so availability is not reduced a second time.
        DB::table('pemesanan')
            ->whereIn('id_pemesanan', DB::table('detail_pemesanan')->where('stok_dikurangi', true)->select('id_pemesanan'))
            ->update(['status_penyerahan' => 'Riwayat Lama']);
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropForeign(['dibatalkan_oleh']);
            $table->dropForeign(['diserahkan_oleh']);
            $table->dropIndex(['status_penyerahan']);
            $table->dropColumn(['status_penyerahan', 'diserahkan_oleh', 'diserahkan_pada', 'dibatalkan_oleh', 'dibatalkan_pada', 'alasan_pembatalan']);
        });
    }
};
