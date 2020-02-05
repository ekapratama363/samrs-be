<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->longText('address')->nullable();
            $table->string('phone')->nullable();
            $table->longText('photo')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();

<<<<<<< HEAD:database/migrations/2020_02_04_144757_create_user_profiles_table.php
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

=======
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
>>>>>>> master:database/migrations/2020_02_05_061526_create_user_profiles_table.php
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
