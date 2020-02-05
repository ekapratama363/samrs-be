<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('user_group_id')->unsigned()->nullable();
            // $table->integer('location_id')->unsigned()->nullable();
            // $table->integer('company_id')->unsigned()->nullable();
            // $table->integer('supervisor')->unsigned()->nullable();
            $table->integer('cost_center')->unsigned()->nullable();
            $table->string('photo')->nullable();
            $table->string('departement')->nullable();
            $table->integer('status')->nullable();
            $table->dateTime('join_date');
            $table->dateTime('retired_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_group_id')->references('id')->on('user_groups')->onDelete('cascade');
            // $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            // $table->foreign('company_id')->references('id')->on('suppliers')->onDelete('cascade');
            // $table->foreign('supervisor')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
