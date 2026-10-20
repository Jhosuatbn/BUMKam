<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Menyimpan path file, contoh: "pelanggan/abc123.jpg" (bukan file-nya)
            $table->string('foto_ktp')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn('foto_ktp');
        });
    }
};