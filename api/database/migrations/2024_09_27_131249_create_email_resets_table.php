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
        if (!Schema::hasTable('email_resets')) {
            Schema::create('email_resets', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('new_email');
                $table->string('token');
                $table->timestamp('created_at')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('email_resets');
    }
};
