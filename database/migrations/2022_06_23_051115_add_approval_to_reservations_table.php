<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalToReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->bigInteger('approved_or_rejected_by')->unsigned()->nullable()->after('updated_by');
            $table->foreign('approved_or_rejected_by')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamp('approved_or_rejected_at')->nullable()->after('updated_at');
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
            $table->dropForeign(['approved_or_rejected_by']);
            $table->dropColumn('approved_or_rejected_by');
            $table->dropColumn('approved_or_rejected_at');
        });
    }
}
