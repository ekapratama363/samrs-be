<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUomIdFromReservationDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_details', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measurement_id']);
            $table->dropColumn('unit_of_measurement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_details', function (Blueprint $table) {
            $table->bigInteger('unit_of_measurement_id')->unsigned()->nullable()->after('delivery_quantity');
            $table->foreign('unit_of_measurement_id')->references('id')->on('unit_of_measurements')->onDelete('set null');
        });
    }
}
