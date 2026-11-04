<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Null keeps existing orders and their payment status unchanged.
            $table->string('status_persetujuan', 30)->nullable()->after('status_pemesanan')->index();
            $table->string('persetujuan_oleh', 20)->nullable()->after('status_persetujuan');
            $table->timestamp('persetujuan_pada')->nullable()->after('persetujuan_oleh');
            $table->text('catatan_persetujuan')->nullable()->after('persetujuan_pada');

            $table->foreign('persetujuan_oleh')
                ->references('id_pengguna')->on('pengguna')
                ->nullOnDelete();
        });

        Schema::create('riwayat_persetujuan_pemesanan', function (Blueprint $table) {
            $table->bigIncrements('id_riwayat');
            $table->string('id_pemesanan', 20)->index();
            $table->string('id_pengguna', 20)->nullable()->index();
            $table->string('status_persetujuan', 30);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_pemesanan')
                ->references('id_pemesanan')->on('pemesanan')
                ->cascadeOnDelete();
            $table->foreign('id_pengguna')
                ->references('id_pengguna')->on('pengguna')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_persetujuan_pemesanan');

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropForeign(['persetujuan_oleh']);
            $table->dropIndex(['status_persetujuan']);
            $table->dropColumn([
                'status_persetujuan',
                'persetujuan_oleh',
                'persetujuan_pada',
                'catatan_persetujuan',
            ]);
        });
    }
};
