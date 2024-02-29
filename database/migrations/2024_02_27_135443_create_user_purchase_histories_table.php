<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPurchaseHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_purchase_histories', function (Blueprint $table) {
            $table->id();
            $table->string('aOrderId')->nullable();
            $table->string('aIsPlaced')->nullable();
            $table->string('aGrandTotal')->nullable();
            $table->string('aCartId')->nullable();
            $table->string('aLastUpdated')->nullable();
            $table->string('aOrderNumber')->nullable();
            $table->string('aOrderSource')->nullable();
            $table->string('aOrderSourceValue')->nullable();
            $table->string('aOrderStatusId')->nullable();
            $table->string('aOrderStatusName')->nullable();
            $table->string('aPaymentStatus')->nullable();
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
        Schema::dropIfExists('user_purchase_histories');
    }
}
