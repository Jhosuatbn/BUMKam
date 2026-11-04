<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->boolean('wa_reminder_opt_in')->default(false)->after('no_telepon');
            $table->timestamp('wa_reminder_opted_in_at')->nullable()->after('wa_reminder_opt_in');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['wa_reminder_opt_in', 'wa_reminder_opted_in_at']);
        });
    }
};
