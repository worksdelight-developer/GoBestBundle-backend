<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutomaticOrderController extends Controller
{
    //


    public function orderPlace250(Request $request)
    {

        $userCartproducts = User::has('cart')->with('cart')->get();

        foreach ($userCartproducts as $userCart) {
            $this->placeOrder($userCart->cart, $userCart);
        }
    }
    public function placeOrder($produts, $user)
    {
        // dd($produts, $user);
        foreach ($produts as $product) {
            // dd($product);

            $
        }
    }
}
