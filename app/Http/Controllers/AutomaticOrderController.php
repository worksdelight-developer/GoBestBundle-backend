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

        $users = User::select('UID')->get();
        foreach ($users  as $user) {
            $userCart = DB::table('cart')->where('user_email', $user->email)->get();
            if (count($userCart) != 0) {
                dd($userCart);
            }
        }
        // dd($user, 'inventory');
    }
}
