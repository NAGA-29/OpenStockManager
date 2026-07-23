<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('location', 128)->default(''); // ロケーション（テキスト管理）
            $table->integer('quantity')->default(0);       // 現在数量
            $table->integer('min_stock')->nullable();      // 最低在庫数（アラート用）
            $table->timestamps();

            $table->unique(['item_id', 'location']); // 品目×ロケーションで一意

            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
