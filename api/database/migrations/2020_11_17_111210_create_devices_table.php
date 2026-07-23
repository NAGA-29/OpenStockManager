<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            // $table->id();
            $table->string('device_id')->unique()->primary();
            $table->string('device_type',10);
            $table->string('device_name',30);
            $table->string('device_serial')->unique();
            $table->string('os',30)->nullable();
            $table->string('os_ver',10)->nullable();
            $table->dateTime('first_work_date_at')->nullable();
            $table->dateTime('purchase_date_at')->nullable();
            $table->string('client')->nullable();
            $table->string('sale_id')->default('');
            $table->string('option')->nullable();
            $table->boolean('defective')->default(false);
            $table->boolean('not_for_sale')->default(false);
            $table->text('note')->nullable();
            // $table->boolean('lending_now')->default(false);
            $table->string('lending_now')->default('');
            $table->string('using_user_id')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('modified_at');
            $table->dateTime('soft_deleted_at')->nullable();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_rental');
        Schema::dropIfExists('devices');
    }
}
