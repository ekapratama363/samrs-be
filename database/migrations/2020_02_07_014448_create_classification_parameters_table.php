<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassificationParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classification_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('classification_id')->unsigned()->nullable();
            $table->string('name');
            $table->integer('type')->nullable(); //1=Char,2=Date,3=Time,4=Numeric,5=List

            $table->integer('length')->nullable();
            $table->integer('decimal')->nullable();
            $table->string('value')->nullable();
            $table->integer('reading')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->boolean('deleted')->default(0);
            $table->timestamps();

            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('set null');
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
        Schema::dropIfExists('classification_parameters');
    }
}


/**
 * Type detail
 * Char => Length
 * Date => null
 * Time => null
 * Numeric => Length, Decimal
 * List => Values Separate use ,
*/
