<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('filename');
                $table->string('extension'); // jpg, png, gif or mp4, mov
                // $table->string('alias_name')->nullable(); //別名
                $table->string('hash');
                $table->string('path');
                $table->integer('height');
                $table->integer('width');
                $table->integer('size');
                // $table->string('thumbnail');
                $table->string('device_id');
                $table->timestamps();

                $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
};
