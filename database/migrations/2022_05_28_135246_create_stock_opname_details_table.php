<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnameDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('stock_opname_id')->unsigned()->nullable();
            $table->bigInteger('stock_id')->unsigned()->nullable();
            $table->integer('system_stock')->unsigned()->nullable();
            $table->integer('actual_stock')->unsigned()->nullable();
            $table->integer('total_scanned')->unsigned()->nullable();
            $table->text('serial_numbers')->nullable();
            $table->string('note')->nullable();
            $table->string('remark')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stock_opname_id')->references('id')->on('stock_opnames')->onDelete('set null');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null');
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
        Schema::dropIfExists('stock_opname_details');
    }
}
