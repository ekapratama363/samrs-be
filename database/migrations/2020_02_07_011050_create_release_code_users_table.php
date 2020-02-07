<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReleaseCodeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('release_code_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('release_code_id')->unsigned()->nullable();
            $table->string('release_code_desc')->nullable();
            $table->bigInteger('release_group_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->boolean('deleted')->default(0);
            $table->timestamps();

            $table->foreign('release_code_id')->references('id')->on('release_codes')->onDelete('set null');
            $table->foreign('release_group_id')->references('id')->on('release_groups')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('release_code_users');
    }
}
