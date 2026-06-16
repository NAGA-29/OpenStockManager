<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('condition')->comment('商品状態');
        });

        DB::table('conditions')->insert([
            ['id' => '1', 'condition' => '新品'],
            ['id' => '2', 'condition' => '新古品'],
            ['id' => '3', 'condition' => '中古品'],
            ['id' => '4', 'condition' => 'ジャンク品'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conditions');
    }
};
