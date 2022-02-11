<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWarehouseTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('quantity');
            $table->integer('destination_wh_id');
            $table->integer('from_wh_id')->nullable();
            $table->integer('status');
            $table->string('transaction_id')->nullable();
            $table->string("notes")->nullable();
            $table->integer('from_id')->nullable();
            $table->integer('to_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses_transactions', function (Blueprint $table) {
            //
        });
    }
}
