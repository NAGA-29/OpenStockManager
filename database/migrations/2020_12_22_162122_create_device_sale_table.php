<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_sale', function (Blueprint $table) {
            $table->string('device_id');
            $table->string('sale_id');
            $table->dateTime('sale_date_at');
            $table->primary(['device_id','sale_id']);

            // 外部キー制約
            $table->foreign('device_id')->references('device_id')->on('devices')->cascadeOnDelete();
            $table->foreign('sale_id')->references('sale_id')->on('sale_hists')->cascadeOnDelete();
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
            $table->dropForeign('device_sale_device_id_foreign');
            $table->dropForeign('device_sale_sale_id_foreign');
        });
        Schema::dropIfExists('device_sale');
    }
}
