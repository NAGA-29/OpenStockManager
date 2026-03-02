<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceRentalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_rental', function (Blueprint $table) {
            // $table->id()->primary;
            $table->string('device_id');
            $table->string('lend_id');
            $table->primary(['device_id','lend_id']);
            $table->dateTime('checkout_at');
            $table->dateTime('return_at')->nullable();
            // $table->dateTime('soft_deleted_at')->nullable();

            // 外部キー制約
            $table->foreign('device_id')->references('device_id')->on('devices')->cascadeOnDelete();
            $table->foreign('lend_id')->references('lend_id')->on('rental_hists')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_rental', function (Blueprint $table) {
            $table->dropForeign('device_rental_device_id_foreign');
            $table->dropForeign('device_rental_lend_id_foreign');
        });
        Schema::dropIfExists('device_rental');
    }
}
