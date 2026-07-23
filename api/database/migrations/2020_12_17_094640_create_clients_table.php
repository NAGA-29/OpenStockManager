<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->string('client_id')->primary();
            $table->string('company');
            $table->string('url');
            $table->string('tel');
            $table->string('street_address');
            $table->text('note')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('modified_at');
            $table->dateTime('soft_deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('clients');
        Schema::enableForeignKeyConstraints();
    }
}
