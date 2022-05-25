<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('material_id')->unsigned()->nullable();
            $table->bigInteger('room_id')->unsigned()->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->integer('stock')->unsigned()->default(0);
            $table->integer('minimum_stock')->unsigned()->default(0);
            $table->integer('quantity_in_transit')->unsigned()->default(0);
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
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
        Schema::dropIfExists('stocks');
    }
}
