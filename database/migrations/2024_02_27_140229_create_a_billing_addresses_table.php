<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateABillingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('a_billing_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('aOrderId')->nullable();
            $table->string('aOrderNumber')->nullable();
            $table->string('aBackendUserId')->nullable();
            $table->string('aBranchCode')->nullable();
            $table->string('aCity')->nullable();
            $table->string('aCompany')->nullable();
            $table->string('aCountryCode')->nullable();
            $table->string('aDepartmentId')->nullable();
            $table->string('aDepartmentName')->nullable();
            $table->string('aEnabled')->nullable();
            $table->string('aFax')->nullable();
            $table->string('aFirstName')->nullable();
            $table->string('aId')->nullable();
            $table->string('aLastName')->nullable();
            $table->string('aLine1')->nullable();
            $table->string('aLine2')->nullable();
            $table->string('aLine3')->nullable();
            $table->string('aMiddleInitial')->nullable();
            $table->string('aNickName')->nullable();
            $table->string('aPhone')->nullable();
            $table->string('aPostalCode')->nullable();
            $table->string('aRegionCode')->nullable();
            $table->string('aRouteCode')->nullable();
            $table->string('aThirdPartyId')->nullable();
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
        Schema::dropIfExists('a_billing_addresses');
    }
}
