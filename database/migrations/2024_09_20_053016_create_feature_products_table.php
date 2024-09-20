<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_products', function (Blueprint $table) {
            $table->id();
            $table->text('aCreationDate')->nullable('-'); // String for timestamp
            $table->text('aExtraShipFee')->nullable(); // String for extra ship fee
            $table->text('aFeatured')->nullable(); // String for boolean value
            $table->text('aGiftWrapAllowed')->nullable(); // String for boolean value
            $table->text('aGiftWrapPrice')->nullable(); // String for price
            $table->text('aId')->nullable(); // String for UUID
            $table->text('aImageFileLarge')->nullable(); // String for large image URL
            $table->text('aImageFileLargeAlternateText')->nullable(); // String for alternate text
            $table->text('aImageFileMedium')->nullable(); // String for medium image URL
            $table->text('aImageFileMediumAlternateText')->nullable(); // String for alternate text
            $table->text('aImageFileSmall')->nullable(); // String for small image URL
            $table->text('aImageFileSmallAlternateText')->nullable(); // String for alternate text
            $table->text('aKeywords')->nullable(); // String for keywords
            $table->text('aLastUpdated')->nullable(); // String for last updated timestamp
            $table->text('aListPrice')->nullable(); // String for price
            $table->text('aLongDescription')->nullable(); // String for long description
            $table->text('aManufacturerId')->nullable(); // String for manufacturer UUID
            $table->text('aMaximumQty')->nullable(); // String for maximum quantity
            $table->text('aMetaDescription')->nullable(); // String for meta description
            $table->text('aMetaKeywords')->nullable(); // String for meta keywords
            $table->text('aMetaTitle')->nullable(); // String for meta title
            $table->text('aMinimumQty')->nullable(); // String for minimum quantity
            $table->text('aNonShipping')->nullable(); // String for boolean value
            $table->text('aOutOfStockMode')->nullable(); // String for out of stock mode
            $table->text('aProductName')->nullable(); // String for product name
            $table->text('aProductTypeId')->nullable(); // String for product type UUID
            $table->text('aRankWeight')->nullable(); // String for rank weight
            $table->text('aRewriteUrl')->nullable(); // String for URL rewrite
            $table->text('aShipSeparately')->nullable(); // String for boolean value
            $table->text('aShippingHeight')->nullable(); // String for shipping height
            $table->text('aShippingLength')->nullable(); // String for shipping length
            $table->text('aShippingMode')->nullable(); // String for shipping mode
            $table->text('aShippingWeight')->nullable(); // String for shipping weight
            $table->text('aShippingWidth')->nullable(); // String for shipping width
            $table->text('aShortDescription')->nullable(); // String for short description
            $table->text('aSiteCost')->nullable(); // String for site cost
            $table->text('aSitePrice')->nullable(); // String for site price
            $table->text('aSitePriceOverrideText')->nullable(); // String for site price override
            $table->text('aSku')->nullable(); // String for SKU
            $table->text('aStatus')->nullable(); // String for status
            $table->text('aTaxClass')->nullable(); // String for tax class
            $table->text('aTaxExempt')->nullable(); // String for boolean value
            $table->text('aTemplateName')->nullable(); // String for template name
            $table->text('aTrackInventory')->nullable(); // String for boolean value
            $table->text('aUnitOfMeasure')->nullable(); // String for unit of measure
            $table->text('aUnspscCode')->nullable(); // String for UNSPSC code
            $table->text('aUpc')->nullable(); // String for UPC
            $table->text('aVendorId')->nullable(); // String for vendor UUID
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
        Schema::dropIfExists('feature_products');
    }
}
