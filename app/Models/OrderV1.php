<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderV1 extends Model
{
    use HasFactory;
    protected $guarded = [];



    function orderProducts()
    {
        return $this->hasMany(OrderProductV1::class, 'order_id', 'order_id');
    }
}
