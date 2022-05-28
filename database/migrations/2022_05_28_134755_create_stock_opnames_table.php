<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->index('idx_code');
            $table->bigInteger('room_id')->unsigned()->nullable()->index('idx_room_id');
            $table->tinyInteger('status')->comment('0 = waiting approve, 1 = approved, 2 = rejected');
            $table->bigInteger('created_by')->unsigned()->nullable()->index('idx_created_by');
            $table->bigInteger('updated_by')->unsigned()->nullable()->index('idx_updated_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
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
        Schema::dropIfExists('stock_opnames');
    }
}
