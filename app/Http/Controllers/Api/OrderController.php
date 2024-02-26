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

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.
        datacontract.org/2004/07/Logicblock.Commerce.Domain">
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
            // $OrderProductV1 = new OrderProductV1;
            // $save = [
            //     'user_id' => $request->user_id,
            //     'order_id' => $request->order_id,
            //     'product_id' => $value,
            //     'quantity' => $quantity
            // ];
            // // $exists = OrderProductV1::where('order_id', $request->order_id)->where('user_id', $request->user_id)->first();
            // // if (isset($exists->id)) {
            // //     $OrderProductV1 =  $exists;
            // // }
            // $OrderProductV1->fill($save);
            // $OrderProductV1->save();
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

        // $OrderV1 = new OrderV1;
        // $exists = OrderV1::where([
        //     'user_id' => $request->user_id,
        //     'order_id' => $request->order_id
        // ])->first();
        // if (isset($exists->id)) {
        //     $OrderV1 =  $exists;
        // }

        // $save = [
        //     'user_id' => $request->user_id,
        //     'order_id' => $request->order_id
        // ];
        // $OrderV1->fill($save);
        // $OrderV1->save();

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

        $orders =  $this->GetOrdersByCriteria($request->user_id, []);

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



    public function GetOrdersByCriteria($user_id, $filters)
    {
        // dd($filters);
        $startIndex = 0;
        $pageSize = 50;
        $dateFilter = '';
        if (isset($filters['StartDate']) && isset($filters['EndDate'])) {
            $startDate = Carbon::parse($filters['StartDate']);
            $endDate = Carbon::parse($filters['EndDate']);
            $formattedFirstDay = $startDate->format('Y-m-d\TH:i:s.uP');
            $formattedLastDay = $endDate->format('Y-m-d\TH:i:s.uP');
            $dateFilter = '<log:EndDate>' . $formattedLastDay . '</log:EndDate><log:StartDate>' . $formattedFirstDay . '</log:StartDate>';
        }

        if (isset($filters->startIndex) && isset($filters->pageSize)) {
            $startIndex =   $filters->startIndex;
            $pageSize =   $filters->pageSize;
        }


        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">

        <soapenv:Header/>
     
        <soapenv:Body>
     
           <tem:GetOrdersByCriteria>
     
              <!--Optional:-->
     
              <tem:token>
     
                 <!--Optional:-->
     
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
     
              <!--Optional:-->
     
              <tem:criteria><log:AffiliateId xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/><log:CategoryId xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>' . $dateFilter . '<log:UserId>' . $user_id . '</log:UserId></tem:criteria><tem:startIndex>' . $startIndex . '</tem:startIndex>
     
              <!--Optional:-->
     
              <tem:pageSize>' . $pageSize . '</tem:pageSize>
     
           </tem:GetOrdersByCriteria>
     
        </soapenv:Body>
     
     </soapenv:Envelope>';





        // Initialize cURL for the SOAP request
        $curl = curl_init();

        // Configure cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc', // Update with the actual URL
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $soapRequest,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/GetOrdersByCriteria'
            ),
        ));
        // Execute the cURL request
        $response = curl_exec($curl);

        // Handle cURL errors
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            echo 'cURL error: ' . $response;
        }
        // Close the cURL session
        curl_close($curl);

        // Process the response as needed
        $xmlResponse = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xmlResponse = simplexml_load_string($xmlResponse);
        $jsonResponse = json_encode($xmlResponse);
        $responseArray = json_decode($jsonResponse, true);
        $orders = [];
        if (isset($responseArray['sBody']['GetOrdersByCriteriaResponse']['GetOrdersByCriteriaResult']['aList']['aOrder'])) {
            $ordersFromApi = $responseArray['sBody']['GetOrdersByCriteriaResponse']['GetOrdersByCriteriaResult']['aList']['aOrder'];
            // dd(gettype($ordersFromApi[0]));
            if (isset($ordersFromApi[0]) && gettype($ordersFromApi[0]) == 'array') {
                foreach ($ordersFromApi as $key => $value) {
                    // dd($value);
                    $orders[] = $this->filterOrderResponce($value);
                }
            } else {
                $orders[] = $this->filterOrderResponce($ordersFromApi);
            }


            // $orders =  $ordersFromApi;
        }

        return $orders;
    }



    public function filterOrderResponce($orderInfoFromApi)
    {

        $aLineItems = @$orderInfoFromApi['aLineItems']['aLineItem'];
        if (!isset($orderInfoFromApi['aLineItems']['aLineItem'][0]) &&  isset($orderInfoFromApi['aLineItems']['aLineItem'])) {
            $aLineItem = array();
            array_push($aLineItem, $orderInfoFromApi['aLineItems']['aLineItem']);
            $aLineItems =  $aLineItem;
        }
        $orderInfo = [
            'aIsPlaced' =>  @$orderInfoFromApi['aIsPlaced'],
            'aGrandTotal' =>     @$orderInfoFromApi['aGrandTotal'],
            'aCartId' =>    @$orderInfoFromApi['aCartId'],
            'aLastUpdated' =>    @$orderInfoFromApi['aLastUpdated'],
            'aOrderNumber' =>    @$orderInfoFromApi['aOrderNumber'],
            'aOrderSource' =>    @$orderInfoFromApi['aOrderSource'],
            'aOrderSourceValue' =>   @$orderInfoFromApi['aOrderSourceValue'],
            'aOrderStatusId' =>    @$orderInfoFromApi['aOrderStatusId'],
            'aOrderStatusName' =>    @$orderInfoFromApi['aOrderStatusName'],
            'aPaymentStatus' =>    @$orderInfoFromApi['aPaymentStatus'],
            'aBillingAddress' =>   @$orderInfoFromApi['aBillingAddress'],
            'aShippingAddress' =>   @$orderInfoFromApi['aShippingAddress'],
            'aLineItems' =>   $aLineItems,
        ];

        return  $orderInfo;
    }


    function suggestNextOrderInfo($orderData, $userInfo)
    {
        $reorderInterval = 24;
        $suggestedNextOrderInfo = [];
        $addedProducts = [];
        $callClass = new ProductController;
        foreach ($orderData as $order) {
            foreach ($order['aLineItems'] as $aLineItems) {
                $deliveryDate = Carbon::parse($order['aLastUpdated']);
                $nextOrderDate = $deliveryDate->copy()->addDays($reorderInterval);
                $productId = $aLineItems['aProductId'];
                $quantity = $aLineItems['aQuantity'];
                $orderId = $aLineItems['aOrderId'];
                if (!isset($addedProducts[$productId])) {
                    $nextOrderInfo = [
                        'next_order_date' => $nextOrderDate->format('Y-m-d'),
                        'product_id' => $productId,
                        'product_info' => $callClass->productInfo($productId, $userInfo),
                        'order_id' => $orderId,
                        'quantity' => $quantity,
                    ];
                    $suggestedNextOrderInfo[] = $nextOrderInfo;
                    $addedProducts[$productId] = true;
                }
            }
        }

        return $suggestedNextOrderInfo;
    }


    public function predictNextOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $user_id = $request->user_id;
        $filters = [
            'StartDate' => now()->firstOfMonth(),
            'EndDate' =>  now()->endOfMonth(),
        ];
        $orders =  $this->GetOrdersByCriteria($user_id, $filters);
        $suggestedNextOrderInfo = $this->suggestNextOrderInfo($orders, []);

        return response()->json(['status' => 1, 'message' => 'predictNextOrder fetched', 'response' => $suggestedNextOrderInfo]);
    }
}
