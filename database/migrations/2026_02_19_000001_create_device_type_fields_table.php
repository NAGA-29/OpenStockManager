<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_type_fields', function (Blueprint $table) {
            $table->id();
            $table->string('device_category_code', 16);
            $table->string('field_key', 64);
            $table->string('label', 128);
            $table->string('field_type', 16); // text, number, select, boolean
            $table->json('options')->nullable(); // [{"label":"...", "value":"..."}, ...]
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['device_category_code', 'field_key']);
            $table->foreign('device_category_code')
                  ->references('code')
                  ->on('device_categories')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_type_fields');
    }
};
