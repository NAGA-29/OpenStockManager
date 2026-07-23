<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('device_serial');
            $table->dropColumn(['os', 'os_ver']);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('os', 30)->nullable();
            $table->string('os_ver', 10)->nullable();
            $table->dropColumn('custom_fields');
        });
    }
};
