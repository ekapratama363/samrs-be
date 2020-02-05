<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key');
            $table->text('value');
            $table->integer('updated_by')->unsigned()->nullable();
<<<<<<< HEAD:database/migrations/2020_02_05_024637_create_settings_table.php
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('sort')->nullable();
=======
>>>>>>> master:database/migrations/2020_02_05_060449_create_settings_table.php
            $table->timestamps();

            $table->primary('key');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
