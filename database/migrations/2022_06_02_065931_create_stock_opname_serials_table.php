<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnameSerialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname_serials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('stock_opname_detail_id')->unsigned()->nullable()->index('idx_stock_opname_detail');
            $table->string('serial_number');
            $table->bigInteger('created_by')->unsigned()->nullable()->index('idx_created_by');
            $table->bigInteger('updated_by')->unsigned()->nullable()->index('idx_updated_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stock_opname_detail_id')->references('id')->on('stock_opname_details')->onDelete('set null');
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
        Schema::dropIfExists('stock_opname_serials');
    }
}
