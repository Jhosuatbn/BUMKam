<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jaminan', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
            $table->string('nama_pemilik', 100)->nullable()->change();
            $table->string('diterima_oleh', 20)->nullable()->after('tanggal_penyerahan');
            $table->foreign('diterima_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jaminan', function (Blueprint $table) {
            $table->dropForeign(['diterima_oleh']);
            $table->dropColumn('diterima_oleh');
            // Legacy rows may have no document after this change, so do not make the field required again.
        });
    }
};
