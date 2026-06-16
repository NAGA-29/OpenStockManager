<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->string('category_code', 16)->nullable();
            $table->string('tracking_type', 16); // 'individual' or 'quantity'
            $table->string('unit', 16)->default('台'); // 単位 (台, 個, 本, etc.)
            $table->json('custom_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_code')
                  ->references('code')
                  ->on('device_categories')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
