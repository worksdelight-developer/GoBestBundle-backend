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



    public function AddLineitem($order_id, $product_id, $quantity)
    {


        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                 <soapenv:Header/>
                 <soapenv:Body>
                <tem:AddLineItemByProductId>
                <!--Optional:-->
                <tem:token>
                <log:ApiId>' . $check->ApiId . '</log:ApiId>
                <!--Optional:-->
                <log:ExpirationDateUtc>' . $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
                <!--Optional:-->
                <log:Id>' . $check->token . '</log:Id>
                <!--Optional:-->
                <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
                <!--Optional:-->
                <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
                </tem:token>
                <!--Optional:-->
                <tem:orderId>' . $order_id . '</tem:orderId>
                <!--Optional:-->
                <tem:productId>' . $product_id . '</tem:productId>
                <!--Optional:-->
                <tem:quantity>' . $quantity . '</tem:quantity>
                </tem:AddLineItemByProductId>
                </soapenv:Body>
                </soapenv:Envelope>';

        //  dd($addItem);

        $curl1 = curl_init();

        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $addItem,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/AddLineItemByProductId'
            ),
        ));

        $response1 = curl_exec($curl1);

        if (curl_errno($curl1)) {
            $response1 = curl_error($curl1);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response1], 400);
        }
        curl_close($curl1);

        $xml1 = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response1);
        $xml1 = simplexml_load_string($xml1);
        $json1 = json_encode($xml1);
        $responseArray1 = json_decode($json1, true);
        if (isset($responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult']) && !empty($responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult'])) {
            return $responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult'];
        } else {
            return 'ee';
        }
    }


    public function orderPlacedV1(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
            'products' => 'required',
            'products_quantity' => 'required',
            'ApiId' => 'required',
            'user_email' =>  'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $AddLineitem = [];
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
            if (count($products) !== 1  && $key !== 0) {
                $AddLineitem[] = $this->AddLineitem($request->order_id, $value, $quantity);
                DB::table('order_and_product')->where(['order_id' => $request->order_id, 'UID' => $request->ApiId])->update(['status' => 'order']);
                DB::table('cart')->where(['product_id' => $value, 'user_email' => $request->user_email])->delete();
                $checkOrder = DB::table('order_history')->where(['order_id' => $request->order_id, 'user_id' => $request->ApiId])->first();
                if (empty($checkOrder)) {
                    DB::table('order_history')->insert(['order_id' => $request->order_id, 'user_id' => $request->ApiId]);
                }
            }
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

        return response()->json(['status' => 1, 'message' => 'order Placed ', 'result' => $request->all(), 'AddLineitem' => $AddLineitem]);
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
        $orders = OrderV1::where('user_id', $request->user_id)->withCount('orderProducts')->orderby('id', 'desc')->get();
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
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' => $orders]);
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
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' => $order]);
    }
}
