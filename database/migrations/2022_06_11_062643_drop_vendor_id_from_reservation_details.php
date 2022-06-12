<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVendorIdFromReservationDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_details', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
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
            $table->bigInteger('vendor_id')->unsigned()->nullable()->after('delivery_quantity');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }
}
