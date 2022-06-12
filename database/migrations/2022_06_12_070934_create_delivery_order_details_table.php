<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_order_details', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInteger('delivery_order_id')->unsigned()->nullable();
            $table->bigInteger('reservation_detail_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = sudah dikirim, 1 = belum dikirim');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('set null');
            $table->foreign('reservation_detail_id')->references('id')->on('reservation_details')->onDelete('set null');
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
        Schema::dropIfExists('delivery_order_details');
    }
}
