<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 既存の devices テーブルに item_id を追加。
     * devices は個別管理（tracking_type='individual'）の inventory_units として扱う。
     * item_id を通じて品目マスタ（items）と紐づける。
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->after('device_type');

            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
        });
    }
};
