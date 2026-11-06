<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengingat_tagihan')) {
            return;
        }

        $legacyTable = Schema::hasColumn('pengingat_tagihan', 'fonnte_request_id');

        Schema::table('pengingat_tagihan', function (Blueprint $table) use ($legacyTable) {
            if (! Schema::hasColumn('pengingat_tagihan', 'jenis')) {
                // Legacy rows did not record the reminder window. Keep them
                // distinct from new H-3/H-7 reminders.
                $table->string('jenis', 20)->default($legacyTable ? 'legacy' : 'h-3');
            }
            if (! Schema::hasColumn('pengingat_tagihan', 'pesan')) {
                $table->text('pesan')->nullable();
            }
            if (! Schema::hasColumn('pengingat_tagihan', 'respons_gateway')) {
                $table->text('respons_gateway')->nullable();
            }
            if (! Schema::hasColumn('pengingat_tagihan', 'terkirim_pada')) {
                $table->timestamp('terkirim_pada')->nullable();
            }
        });

        if ($legacyTable && Schema::hasColumn('pengingat_tagihan', 'dikirim_pada')) {
            DB::table('pengingat_tagihan')
                ->whereNull('terkirim_pada')
                ->whereNotNull('dikirim_pada')
                ->update(['terkirim_pada' => DB::raw('dikirim_pada')]);
        }

        $indexes = collect(Schema::getIndexes('pengingat_tagihan'));
        $hasNewUnique = $indexes->contains(fn (array $index) =>
            ($index['unique'] ?? false)
            && ($index['columns'] ?? []) === ['id_pemesanan', 'nomor_angsuran', 'tanggal_jatuh_tempo', 'jenis']
        );

        if (! $hasNewUnique) {
            Schema::table('pengingat_tagihan', function (Blueprint $table) {
                $table->unique(
                    ['id_pemesanan', 'nomor_angsuran', 'tanggal_jatuh_tempo', 'jenis'],
                    'pengingat_tagihan_satu_kali_unik'
                );
            });
        }

        // Keep an index beginning with id_pemesanan in place for the existing
        // foreign key before removing the old, narrower unique index.
        $indexes = collect(Schema::getIndexes('pengingat_tagihan'));
        if ($indexes->contains(fn (array $index) => $index['name'] === 'pengingat_tagihan_unik')) {
            Schema::table('pengingat_tagihan', function (Blueprint $table) {
                $table->dropUnique('pengingat_tagihan_unik');
            });
        }
    }

    public function down(): void
    {
        // This is a compatibility migration. Keep the reminder history and
        // compatible columns in place when rolling back application code.
    }
};
