<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 入出庫トランザクションテーブル
     *
     * tracking_type='individual' の場合: inventory_unit_id を参照（個体管理）
     * tracking_type='quantity'   の場合: inventory_stock_id と quantity_change を使用（数量管理）
     */
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();

            // 管理方式 ('individual' or 'quantity')
            $table->string('tracking_type', 16);

            // 入庫 / 出庫 / 調整
            $table->string('transaction_type', 16); // 'in', 'out', 'adjust'

            // 品目マスタへの参照（共通）
            $table->unsignedBigInteger('item_id')->nullable();

            // 個別管理: devices.device_id を参照
            $table->string('inventory_unit_id')->nullable();

            // 数量管理: inventory_stocks.id を参照
            $table->unsignedBigInteger('inventory_stock_id')->nullable();

            // 数量管理: 変動数（入庫=正, 出庫=負, 調整=任意）
            $table->integer('quantity_change')->nullable();

            $table->string('reason', 256)->nullable(); // 理由
            $table->text('note')->nullable();           // 備考
            $table->unsignedBigInteger('staff_id');     // 処理担当者

            $table->timestamp('transacted_at');         // 実際の入出庫日時
            $table->timestamps();

            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('set null');

            $table->foreign('inventory_unit_id')
                  ->references('device_id')
                  ->on('devices')
                  ->onDelete('set null');

            $table->foreign('inventory_stock_id')
                  ->references('id')
                  ->on('inventory_stocks')
                  ->onDelete('set null');

            $table->foreign('staff_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
