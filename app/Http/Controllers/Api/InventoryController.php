<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Models\Inventory;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use App\Models\FavouriteProduct;
use Carbon\Carbon;

class InventoryController extends Controller
{
    //


    public function inventoryList(Request $request)
    {

        $ProductClass = new ProductController;

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $result =  Inventory::where('user_id', $request->user_id)->orderby('id', 'desc')->get();
        // foreach ($result as $key => $value) {
        //     $value['productInfo'] = $ProductClass->productInfo($value->product_id, $userInfo);
        //     $orderInfoFromApi = $ProductClass->orderInfo($value->order_id);

        //     $aIsPlaced = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aIsPlaced'];
        //     $aGrandTotal = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aGrandTotal'];
        //     $aCartId = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aCartId'];
        //     $aLastUpdated = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLastUpdated'];
        //     $aOrderNumber =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderNumber'];
        //     $aOrderSource =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSource'];
        //     $aOrderSourceValue =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSourceValue'];
        //     $aOrderStatusId =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusId'];
        //     $aOrderStatusName = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusName'];
        //     $aPaymentStatus =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aPaymentStatus'];
        //     $aShippingCost =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingCost'];
        //     $aShippingDiscounts =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingDiscounts'];
        //     $aShippingMethodId =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingMethodId'];
        //     $aShippingProviderId =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingProviderId'];
        //     $aShippingProviderServiceCode =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingProviderServiceCode'];
        //     $aShippingStatus  =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingStatus'];
        //     $aShippingTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingTotal'];
        //     $aSubTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aSubTotal'];
        //     $aTaxTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aTaxTotal'];
        //     $aThirdPartyOrderId =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aThirdPartyOrderId'];
        //     $aTimeOfOrder =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aTimeOfOrder'];

        //     $orderInfo = [
        //         'aIsPlaced' =>  $aIsPlaced,
        //         'aGrandTotal' =>    $aGrandTotal,
        //         'aCartId' =>   $aCartId,
        //         'aLastUpdated' =>   $aLastUpdated,
        //         'aOrderNumber' =>   $aOrderNumber,
        //         'aOrderSource' =>   $aOrderSource,
        //         'aOrderSourceValue' =>   $aOrderSourceValue,
        //         'aOrderStatusId' =>   $aOrderStatusId,
        //         'aOrderStatusName' =>   $aOrderStatusName,
        //         'aPaymentStatus' =>   $aPaymentStatus,
        //         'aShippingCost' =>   $aShippingCost,
        //         'aShippingDiscounts' =>   $aShippingDiscounts,
        //         'aShippingMethodId' =>   $aShippingMethodId,
        //         'aShippingProviderId' =>   $aShippingProviderId,
        //         'aShippingProviderServiceCode' =>   $aShippingProviderServiceCode,
        //         'aShippingStatus' =>   $aShippingStatus,
        //         'aShippingTotal' =>   $aShippingTotal,
        //         'aSubTotal' =>   $aSubTotal,
        //         'aTaxTotal' =>   $aTaxTotal,
        //         'aThirdPartyOrderId' =>   $aThirdPartyOrderId,
        //         'aTimeOfOrder' =>   $aTimeOfOrder
        //     ];
        //     $value['orderInfo'] = $orderInfo;
        // }
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' =>  $result]);
    }

    public function addToInventory(Request $request)
    {
        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required',
            'productImage' => 'required',
            'productName' => 'required',
            'price' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $save = [
            'user_id' => $request->user_id,
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'productImage' => $request->productImage,
            'productName' => $request->productName,
            'price' => $request->price,
        ];
        $Inventory = new Inventory;
        $existsRecord = Inventory::where([
            'user_id' => $request->user_id,
            // 'order_id' => $request->order_id,
            'product_id' => $request->product_id,

        ])->first();

        if (isset($existsRecord->user_id)) {
            $Inventory =  $existsRecord;
            $save['quantity'] = intval($request->quantity) +  intval($existsRecord->quantity);
        }
        $Inventory->fill($save);
        $Inventory->save();
        return response()->json(['status' => 1, 'message' => 'addToInventory', 'result' => $request->all()]);
    }

    public function scanOut(Request $request)
    {
        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
            'min_quantity'  => 'required',
            'type'  => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }


        $Inventory = new Inventory;
        $existsRecord = Inventory::where('id', $request->id)->first();

        if (isset($existsRecord->user_id)) {
            if ((int)$request->quantity <= 0) {
                Inventory::where('id', $request->id)->delete();
            } else {
                $save = [
                    'user_id' => $existsRecord->user_id,
                    'order_id' => $existsRecord->order_id,
                    'product_id' => $existsRecord->product_id,
                    'quantity' => $request->quantity,
                    'min_quantity'  => $request->min_quantity,
                    'type' => $request->type,
                ];
                $Inventory =  $existsRecord;
                $Inventory->fill($save);
                $Inventory->update();
            }
        }

        return response()->json(['status' => 1, 'message' => 'Inventory quantity Updated', 'result' => $request->all()]);
    }
}
