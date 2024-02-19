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
use App\Models\OrderV1;
use App\Models\OrderProductV1;



use Illuminate\Support\Facades\DB;
use App\Models\FavouriteProduct;
use Carbon\Carbon;

class OrderController extends Controller
{
    //

    public function orderPlacedV1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
            'products' => 'required',
            'products_quantity' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }

        $products = $request->products;
        $products_quantity = $request->products_quantity;

        if (gettype($products) !== 'array') {
            return response()->json(['status' => 0, 'message' =>  'products is not an array']);
        }
        if (gettype($products_quantity) !== 'array') {
            return response()->json(['status' => 0, 'message' =>  'products_quantity is not an array']);
        }

        if (count($products_quantity) !== count($products)) {
            return response()->json(['status' => 0, 'message' =>  ' products and products_quantity s lenght not matched']);
        }

        foreach ($products as $key => $value) {
            $quantity =  $products_quantity[$key];
            $OrderProductV1 = new OrderProductV1;
            $exists = OrderProductV1::where([
                'user_id' => $request->user_id,
                'order_id' => $request->order_id,
                'product_id' => $value,
                'quantity' => $quantity
            ])->first();

            if (isset($exists->id)) {
                $OrderProductV1 =  $exists;
            }
            $save = [
                'user_id' => $request->user_id,
                'order_id' => $request->order_id,
                'product_id' => $value
            ];
            $OrderProductV1->fill($save);
            $OrderProductV1->save();
        }

        $OrderV1 = new OrderV1;
        $exists = OrderV1::where([
            'user_id' => $request->user_id,
            'order_id' => $request->order_id
        ])->first();
        if (isset($exists->id)) {
            $OrderV1 =  $exists;
        }

        $save = [
            'user_id' => $request->user_id,
            'order_id' => $request->order_id
        ];
        $OrderV1->fill($save);
        $OrderV1->save();

        return response()->json(['status' => 1, 'message' => 'order Placed ', 'result' => $request->all()]);
    }


    public  function  getPurchaseHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $orders = OrderV1::where('user_id', $request->user_id)->withCount('orderProducts')->get();
        $callClass = new ProductController;
        foreach ($orders as $key => $value) {
            $orderInfoFromApi = $callClass->orderInfo($value->order_id);
            $aIsPlaced = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aIsPlaced'];
            $aGrandTotal = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aGrandTotal'];
            $aCartId = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aCartId'];
            $aLastUpdated = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLastUpdated'];
            $aOrderNumber =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderNumber'];
            $aOrderSource =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSource'];
            $aOrderSourceValue =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSourceValue'];
            $aOrderStatusId =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusId'];
            $aOrderStatusName = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusName'];
            $aPaymentStatus =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aPaymentStatus'];

            $orderInfo = [
                'aIsPlaced' =>  $aIsPlaced,
                'aGrandTotal' =>    $aGrandTotal,
                'aCartId' =>   $aCartId,
                'aLastUpdated' =>   $aLastUpdated,
                'aOrderNumber' =>   $aOrderNumber,
                'aOrderSource' =>   $aOrderSource,
                'aOrderSourceValue' =>   $aOrderSourceValue,
                'aOrderStatusId' =>   $aOrderStatusId,
                'aOrderStatusName' =>   $aOrderStatusName,
                'aPaymentStatus' =>   $aPaymentStatus,
            ];
            $value['orderInfo'] = $orderInfo;
        }
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'result' => $orders]);
    }


    public  function  getOrderDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $order = OrderV1::where('id', $request->id)->withCount('orderProducts')->first();
        // dd($order);
        $callClass = new ProductController;
        // foreach ($orders as $key => $value) {
        $orderInfoFromApi = $callClass->orderInfo($order->order_id);

        $aIsPlaced = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aIsPlaced'];
        $aGrandTotal = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aGrandTotal'];
        $aCartId = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aCartId'];
        $aLastUpdated = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLastUpdated'];
        $aOrderNumber =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderNumber'];
        $aOrderSource =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSource'];
        $aOrderSourceValue =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderSourceValue'];
        $aOrderStatusId =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusId'];
        $aOrderStatusName = @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aOrderStatusName'];
        $aPaymentStatus =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aPaymentStatus'];
        $aShippingCost =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingCost'];
        $aShippingDiscounts =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingDiscounts'];
        $aShippingMethodId =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingMethodId'];
        $aShippingProviderId =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingProviderId'];
        $aShippingProviderServiceCode =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingProviderServiceCode'];
        $aShippingStatus  =  @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingStatus'];
        $aShippingTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aShippingTotal'];
        $aSubTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aSubTotal'];
        $aTaxTotal =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aTaxTotal'];
        $aThirdPartyOrderId =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aThirdPartyOrderId'];
        $aTimeOfOrder =   @$orderInfoFromApi['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aTimeOfOrder'];

        $orderInfo = [
            'aIsPlaced' =>  $aIsPlaced,
            'aGrandTotal' =>    $aGrandTotal,
            'aCartId' =>   $aCartId,
            'aLastUpdated' =>   $aLastUpdated,
            'aOrderNumber' =>   $aOrderNumber,
            'aOrderSource' =>   $aOrderSource,
            'aOrderSourceValue' =>   $aOrderSourceValue,
            'aOrderStatusId' =>   $aOrderStatusId,
            'aOrderStatusName' =>   $aOrderStatusName,
            'aPaymentStatus' =>   $aPaymentStatus,
            'aShippingCost' =>   $aShippingCost,
            'aShippingDiscounts' =>   $aShippingDiscounts,
            'aShippingMethodId' =>   $aShippingMethodId,
            'aShippingProviderId' =>   $aShippingProviderId,
            'aShippingProviderServiceCode' =>   $aShippingProviderServiceCode,
            'aShippingStatus' =>   $aShippingStatus,
            'aShippingTotal' =>   $aShippingTotal,
            'aSubTotal' =>   $aSubTotal,
            'aTaxTotal' =>   $aTaxTotal,
            'aThirdPartyOrderId' =>   $aThirdPartyOrderId,
            'aTimeOfOrder' =>   $aTimeOfOrder
        ];
        $order['orderInfo'] = $orderInfo;

        foreach ($order->orderProducts as $product) {
            $product['product_info'] = $callClass->productInfo($product->product_id, []);
        }
        // $order['orderInfo2'] = $orderInfoFromApi;
        // }
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'result' => $order]);
    }
}
