<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // テーブルが存在しない場合のみ作成
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->bigIncrements('id');
                // $table->morphs('tokenable');
                $table->string('tokenable_id'); // 文字列型のIDを想定　CHANGE:2023/06/26
                $table->string('tokenable_type'); // CHANGE:2023/06/26
                $table->index(['tokenable_type', 'tokenable_id']); //CHANGE:2023/06/26
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        };
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};