<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id("id");
            $table->string("client_id");
            $table->foreign('client_id')
            ->references('client_id')
            ->on('clients')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

            $table->string('name');
            $table->string('tel');
            $table->string('email');
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
        // Schema::table('contacts', function (Blueprint $table) {
        //     $table->dropForeign('contacts_client_id_foreign');
        // });
        Schema::dropIfExists('contacts');
        Schema::enableForeignKeyConstraints();
    }
}
