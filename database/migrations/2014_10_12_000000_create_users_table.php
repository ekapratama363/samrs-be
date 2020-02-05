<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
<<<<<<< HEAD
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile')->nullable();
            $table->string('api_token', 100)->unique();
            $table->boolean('deleted')->default(0);
            $table->integer('worng_pass')->nullable();
            $table->integer('status')->nullable();
            $table->string('confirmation_code', 50)->nullable();
            $table->string('last_request_time')->nullable();
=======
            $table->bigIncrements('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('wrong_pass')->nullable();
            $table->integer('status')->default(0);
            $table->string('api_token', 100)->unique();
            $table->string('mobile')->nullable();
            $table->string('confirmation_code', 50)->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            $table->string('last_request_time')->nullable();
            $table->boolean('deleted')->default(false);
>>>>>>> ruben_dev
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
