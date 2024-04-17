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

        $users = User::get();
        foreach ($users  as $user) {
            $products = DB::table('cart')->where('user_email', $user->name)->get();
            if (count($products) != 0) {
                dd($products);
            }
        }
        // dd($user, 'inventory');
    }
}
