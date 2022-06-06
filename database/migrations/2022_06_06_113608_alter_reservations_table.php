<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->bigInteger('room_sender')->unsigned()->nullable()->after('vendor_id');
            $table->foreign('room_sender')->references('id')->on('rooms')->onDelete('set null');
            $table->renameColumn('max_delivery_date', 'delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['room_sender']);
            $table->dropColumn('room_sender');
            $table->renameColumn('delivery_date', 'max_delivery_date');
        });
    }
}
