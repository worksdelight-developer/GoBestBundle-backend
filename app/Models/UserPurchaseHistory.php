<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Api\ProductController;
class UserPurchaseHistory extends Model
{
    use HasFactory;
    protected $guarded = [];


    function aLineItems()
    {
        return $this->hasMany(ALineItems::class, 'aOrderId', 'aOrderId');
    }

    function aShippingAddress()
    {
        return $this->belongsTo(AShippingAddress::class, 'aOrderId', 'aOrderId');
    }
    function aBillingAddress()
    {
        return $this->belongsTo(ABillingAddress::class, 'aOrderId', 'aOrderId');
    }

   
}
