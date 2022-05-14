<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInteger('room_id')->unsigned()->nullable();
            $table->bigInteger('plant_id')->unsigned()->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->date('max_delivery_date');
            $table->text('note');
            $table->tinyInteger('type')->comment('0 = request to vendor, 1 = transfer with delivery order, 2 = transfer without delivery order');
            $table->tinyInteger('status')->default(0)->comment('0 = pending, 1 = approve, 2 = not approve');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('set null');
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
        Schema::dropIfExists('reservations');
    }
}
