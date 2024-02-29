<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateALineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('a_line_items', function (Blueprint $table) {
            $table->id();
            $table->string('aOrderNumber')->nullable();
            $table->string('aId')->nullable();
            $table->string('aLastUpdated')->nullable();
            $table->string('aLineTotal')->nullable();
            $table->string('aOrderId')->nullable();
            $table->string('aProductId')->nullable();
            $table->string('aProductName')->nullable();
            $table->string('aProductSku')->nullable();
            $table->string('aProductUnitOfMeasure')->nullable();
            $table->string('aQuantity')->nullable();
            $table->string('aUnitCost')->nullable();
            $table->string('aUnitPrice')->nullable();
            $table->string('aImageFileLarge')->nullable();
            $table->string('aVendorId')->nullable();
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
        Schema::dropIfExists('a_line_items');
    }
}
