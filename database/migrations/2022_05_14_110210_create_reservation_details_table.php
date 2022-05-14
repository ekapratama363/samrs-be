<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_details', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInteger('reservation_id')->unsigned()->nullable();
            $table->bigInteger('material_id')->unsigned()->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('delivery_quantity')->default(0);
            $table->bigInteger('unit_of_measurement_id')->unsigned()->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('unit_of_measurement_id')->references('id')->on('unit_of_measurements')->onDelete('set null');
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
        Schema::dropIfExists('reservation_details');
    }
}
