<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryOrderDetailIdInStockDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_details', function (Blueprint $table) {
            $table->bigInteger('delivery_order_detail_id')->unsigned()->nullable()->after('stock_id');
            $table->foreign('delivery_order_detail_id')->references('id')->on('delivery_order_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_details', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_detail_id']);
            $table->dropColumn('delivery_order_detail_id');
        });
    }
}
