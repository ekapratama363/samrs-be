<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterReadingInClassificationParameter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classification_parameters', function (Blueprint $table) {
            $table->dropColumn('reading');
            $table->boolean('reading_indicator')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classification_parameters', function (Blueprint $table) {
            $table->integer('reading')->nullable();
            $table->dropColumn('reading_indicator');
        });
    }
}
