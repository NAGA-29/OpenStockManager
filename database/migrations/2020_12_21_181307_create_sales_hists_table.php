<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_hists', function (Blueprint $table) {
            $table->string('sale_id')->primary();
            $table->string('client');
            $table->foreign('client')
            ->references('client_id')
            ->on('clients')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->unsignedBigInteger('personnel');
            $table->foreign('personnel')
            ->references('id')
            ->on('contacts')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->bigInteger('staff')->unsigned();
            $table->foreign('staff')
            ->references('id')
            ->on('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->dateTime('sale_date_at');
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
        Schema::dropIfExists('sales_hists');
    }
}
