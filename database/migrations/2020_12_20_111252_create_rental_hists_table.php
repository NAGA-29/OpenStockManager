<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_hists', function (Blueprint $table) {
            $table->string('lend_id')->primary();
            // $table->string('desc_code');
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
            // $table->string('in_use_id');
            $table->boolean('all_returned')->default(false);
            $table->dateTime('checkout_at');
            $table->dateTime('schedule_return_at')->nullable();
            $table->dateTime('return_at')->nullable();
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
        // Schema::table('rental_hists', function (Blueprint $table) {
        //     $table->dropForeign('rental_hists_client_foreign');
        //     $table->dropForeign('rental_hists_personnel_foreign');
        //     $table->dropForeign('rental_hists_staff_foreign');
        // });
        Schema::dropIfExists('rental_hists');
        Schema::enableForeignKeyConstraints();
    }
}
