<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnToStorages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storages', function (Blueprint $table) {
            $table->string('name', 21)->change();
            $table->string('code')->nullable();
            $table->bigInteger('plant_id')->unsigned()->nullable();
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');

            // Set coloumn to nullable
            $table->string('name')->nullable()->change();
            $table->integer('type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storages', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');

            $table->string('name')->change();
            $table->integer('type')->change();
        });
    }
}
