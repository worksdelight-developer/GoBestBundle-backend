<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutomaticOrderController extends Controller
{
    //


    public function orderPlace250(Request $request)
    {
        $ProductCont = new ProductController;
        $OrderCont = new OrderController;
        $userCartproducts = User::has('cart')->with('cart')->get();
        // dd($userCartproducts);
        $return  = [];
        $orders = [];
        $orderInfo = [];
        foreach ($userCartproducts as $userCart) {
            $amount =   $userCart->cart->pluck('product_price')->toarray();
            $amount = array_map('floatval', $amount);
            $total = array_sum($amount);
            if ($total >= 250) {
                // dd($total, $amount);
                $return = $ProductCont->CreateNewUnplacedOrderV1($userCart, $userCart->cart[0]->product_id, $userCart->cart[0]->product_quantity);
                $OrderCont->orderPlacedV2(
                    $userCart->UID,
                    $return['order_id'],
                    $userCart->cart->pluck('product_id')->toarray(),
                    $userCart->cart->pluck('product_quantity')->toarray(),
                    $userCart->ApiId,
                    $userCart->email
                );
                $orderInfo[] = $ProductCont->OrderPlaceV1($userCart, $return['order_id'], $userCart->cart[0]->product_id, $userCart->cart[0]->product_quantity, $total);
            }
        }

        return response()->json(['status' => 1, 'message' => 'Order Automatic Success ', 'orderInfo' => $orderInfo]);
    }
    // public function placeOrder($produts, $user)
    // {
    //     $ProductCont = new ProductController;
    //     $OrderApiCont = new OrderController;
    //     $products = [];
    //     foreach ($produts as $product) {
    //         // $productInfo =  $ProductCont->getProductDetailsByID(['productIds' => $product->product_id]);
    //         $products['product_id'][] = $product->product_id;
    //         $products['product_quantity'][] = $product->product_quantity;
    //     }
    //     return $products;
    // }
}
