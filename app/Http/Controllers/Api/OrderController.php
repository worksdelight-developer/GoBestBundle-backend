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
use App\Models\UserPurchaseHistory;
use App\Models\ABillingAddress;
use App\Models\AShippingAddress;
use App\Models\ALineItems;



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
        // dd($tokenData);
        $check = $refreshtoken->refreshToken($tokenData);



        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:AddLineItemByProductId>
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
            return $responseArray1;
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
            if (count($products) > 1 && $key > 0) {
                DB::table('order_and_product')->where(['order_id' => $request->order_id, 'UID' => $request->ApiId])->update(['status' => 'order']);
                DB::table('cart')->where(['product_id' => $value, 'user_email' => $request->user_email])->delete();
                $checkOrder = DB::table('order_history')->where(['order_id' => $request->order_id, 'user_id' => $request->ApiId])->first();
                if (empty($checkOrder)) {
                    DB::table('order_history')->insert(['order_id' => $request->order_id, 'user_id' => $request->ApiId]);
                }
                $AddLineitem[] = $this->AddLineitem($request->order_id, $value, $quantity);
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

        //$purchaseHistory = $this->getPurchaseHistory($request);

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

        $filters = [];
        if (isset($request->startIndex) && isset($request->pageSize)) {
            $filters = [
                'startIndex' => $request->startIndex,
                'pageSize' =>  $request->pageSize,
            ];
        }

        $orders =  $this->GetOrdersByCriteria($request->user_id, $filters);
        foreach ($orders  as $order) {
            $aOrderId = '';
            foreach ($order['aLineItems'] as $product) {
                $aOrderId = $product['aOrderId'];
                $saveaLineItems = [
                    'aOrderNumber' => is_string($order['aOrderNumber']) ? $order['aOrderNumber'] : '',
                    'aId' => is_string($product['aId']) ? $product['aId'] : '',
                    'aLastUpdated' =>  is_string($product['aLastUpdated']) ? $product['aLastUpdated'] : '',
                    'aOrderId' =>  $aOrderId,
                    'aProductId' =>  is_string($product['aProductId']) ?  $product['aProductId'] : '',
                    'aProductName' =>  is_string($product['aProductName']) ?  $product['aProductName'] : '',
                    'aProductSku' =>  is_string($product['aProductSku']) ?  $product['aProductSku'] : '',
                    'aProductUnitOfMeasure' => is_string($product['aProductUnitOfMeasure']) ?   $product['aProductUnitOfMeasure'] : '',
                    'aQuantity' => is_string($product['aQuantity']) ?   $product['aQuantity'] : '',
                    'aUnitCost' =>  is_string($product['aUnitCost']) ?  $product['aUnitCost'] : '',
                    'aUnitPrice' =>  is_string($product['aUnitPrice']) ?  $product['aUnitPrice'] : '',
                    'aImageFileLarge' =>  is_string($product['aImageFileLarge']) ?  $product['aImageFileLarge'] : '',
                    'aVendorId' => is_string($product['aVendorId']) ?   $product['aVendorId'] : '',
                    'aLineTotal' => is_string($product['aLineTotal']) ?   $product['aLineTotal'] : '',
                ];
                $oldRecordALineItems = ALineItems::where($saveaLineItems)->first();
                if (!isset($oldRecordALineItems->id)) {
                    $ALineItems = new ALineItems;
                    $ALineItems->fill($saveaLineItems);
                    $ALineItems->save();
                }
            }
            $saveOrder = [
                'user_id' => $request->user_id,
                'aOrderId' =>  $aOrderId,
                'aIsPlaced' => is_string($order['aIsPlaced']) ?  $order['aIsPlaced'] : '',
                'aGrandTotal' =>  is_string($order['aGrandTotal']) ?  $order['aGrandTotal'] : '',
                'aCartId' =>  is_string($order['aCartId']) ?  $order['aCartId'] : '',
                'aLastUpdated' =>  is_string($order['aLastUpdated']) ?  $order['aLastUpdated'] : '',
                'aOrderNumber' =>  is_string($order['aOrderNumber']) ?  $order['aOrderNumber'] : '',
                'aOrderSource' =>  is_string($order['aOrderSource']) ?   $order['aOrderSource'] : '',
                'aOrderSourceValue' =>  is_string($order['aOrderSourceValue']) ?   $order['aOrderSourceValue'] : '',
                'aOrderStatusId' =>  is_string($order['aOrderStatusId']) ?   $order['aOrderStatusId'] : '',
                'aOrderStatusName' =>  is_string($order['aOrderStatusName']) ?   $order['aOrderStatusName'] : '',
                'aPaymentStatus' =>  is_string($order['aPaymentStatus']) ?   $order['aPaymentStatus'] : '',
            ];

            $oldRecordUserPurchaseHistory = UserPurchaseHistory::where([
                'user_id' => $request->user_id,
                'aOrderId' =>  $aOrderId
            ])->first();
            if (isset($oldRecordUserPurchaseHistory->id)) {
                $UserPurchaseHistory = $oldRecordUserPurchaseHistory;
                $UserPurchaseHistory->fill($saveOrder);
                $UserPurchaseHistory->update();
            } else {

                $UserPurchaseHistory = new UserPurchaseHistory;
                $UserPurchaseHistory->fill($saveOrder);
                $UserPurchaseHistory->save();
            }


            $saveABillingAddress = [
                'aOrderId' =>  $aOrderId,
                'aOrderNumber' => is_string($order['aOrderNumber']) ? $order['aOrderNumber'] : '',
                'aBranchCode' => is_string($order['aBillingAddress']['aBranchCode']) ? $order['aBillingAddress']['aBranchCode'] : '',
                'aCity' => is_string($order['aBillingAddress']['aCity']) ? $order['aBillingAddress']['aCity'] : '',
                'aCompany' => is_string($order['aBillingAddress']['aCompany']) ? $order['aBillingAddress']['aCompany'] : '',
                'aCountryCode' => is_string($order['aBillingAddress']['aCountryCode']) ? $order['aBillingAddress']['aCountryCode'] : '',
                'aDepartmentId' => is_string($order['aBillingAddress']['aDepartmentId']) ? $order['aBillingAddress']['aDepartmentId'] : '',
                'aDepartmentName' => is_string($order['aBillingAddress']['aDepartmentName']) ?  $order['aBillingAddress']['aDepartmentName'] : '',
                'aEnabled' => is_string($order['aBillingAddress']['aEnabled']) ?  $order['aBillingAddress']['aEnabled'] : '',
                'aFax' => is_string($order['aBillingAddress']['aFax']) ?  $order['aBillingAddress']['aFax'] : '',
                'aFirstName' => is_string($order['aBillingAddress']['aFirstName']) ?  $order['aBillingAddress']['aFirstName'] : '',
                'aId' => is_string($order['aBillingAddress']['aId']) ?  $order['aBillingAddress']['aId'] : '',
                'aLastName' => is_string($order['aBillingAddress']['aLastName']) ?  $order['aBillingAddress']['aLastName'] : '',
                'aLine1' => is_string($order['aBillingAddress']['aLine1']) ?  $order['aBillingAddress']['aLine1'] : '',
                'aLine2' => is_string($order['aBillingAddress']['aLine2']) ?  $order['aBillingAddress']['aLine2'] : '',
                'aLine3' => is_string($order['aBillingAddress']['aLine3']) ?  $order['aBillingAddress']['aLine3'] : '',
                'aMiddleInitial' => is_string($order['aBillingAddress']['aMiddleInitial']) ?  $order['aBillingAddress']['aMiddleInitial'] : '',
                'aNickName' => is_string($order['aBillingAddress']['aNickName']) ?  $order['aBillingAddress']['aNickName'] : '',
                'aPhone' => is_string($order['aBillingAddress']['aPhone']) ?  $order['aBillingAddress']['aPhone'] : '',
                'aPostalCode' => is_string($order['aBillingAddress']['aPostalCode']) ?  $order['aBillingAddress']['aPostalCode'] : '',
                'aRegionCode' => is_string($order['aBillingAddress']['aRegionCode']) ?  $order['aBillingAddress']['aRegionCode'] : '',
                'aRouteCode' => is_string($order['aBillingAddress']['aRouteCode']) ?  $order['aBillingAddress']['aRouteCode'] : '',
                'aThirdPartyId' => is_string($order['aBillingAddress']['aThirdPartyId']) ?  $order['aBillingAddress']['aThirdPartyId'] : '',
            ];
            $oldRecordABillingAddress = ABillingAddress::where($saveABillingAddress)->first();
            if (isset($oldRecordABillingAddress->id)) {
                $ABillingAddress =  $oldRecordABillingAddress;
                $ABillingAddress->fill($saveABillingAddress);
                $ABillingAddress->update();
            } else {
                $ABillingAddress =  new ABillingAddress;
                $ABillingAddress->fill($saveABillingAddress);
                $ABillingAddress->save();
            }


            $saveAShippingAddress = [
                'aOrderId' =>  $aOrderId,
                'aOrderNumber' => is_string($order['aOrderNumber']) ? $order['aOrderNumber'] : '',
                'aBranchCode' => is_string($order['aShippingAddress']['aBranchCode']) ? $order['aShippingAddress']['aBranchCode'] : '',
                'aCity' => is_string($order['aShippingAddress']['aCity']) ? $order['aShippingAddress']['aCity'] : '',
                'aCompany' => is_string($order['aShippingAddress']['aCompany']) ? $order['aShippingAddress']['aCompany'] : '',
                'aCountryCode' => is_string($order['aShippingAddress']['aCountryCode']) ? $order['aShippingAddress']['aCountryCode'] : '',
                'aDepartmentId' => is_string($order['aShippingAddress']['aDepartmentId']) ? $order['aShippingAddress']['aDepartmentId'] : '',
                'aDepartmentName' => is_string($order['aShippingAddress']['aDepartmentName']) ?  $order['aShippingAddress']['aDepartmentName'] : '',
                'aEnabled' => is_string($order['aShippingAddress']['aEnabled']) ?  $order['aShippingAddress']['aEnabled'] : '',
                'aFax' => is_string($order['aShippingAddress']['aFax']) ?  $order['aShippingAddress']['aFax'] : '',
                'aFirstName' => is_string($order['aShippingAddress']['aFirstName']) ?  $order['aShippingAddress']['aFirstName'] : '',
                'aId' => is_string($order['aShippingAddress']['aId']) ?  $order['aShippingAddress']['aId'] : '',
                'aLastName' => is_string($order['aShippingAddress']['aLastName']) ?  $order['aShippingAddress']['aLastName'] : '',
                'aLine1' => is_string($order['aShippingAddress']['aLine1']) ?  $order['aShippingAddress']['aLine1'] : '',
                'aLine2' => is_string($order['aShippingAddress']['aLine2']) ?  $order['aShippingAddress']['aLine2'] : '',
                'aLine3' => is_string($order['aShippingAddress']['aLine3']) ?  $order['aShippingAddress']['aLine3'] : '',
                'aMiddleInitial' => is_string($order['aShippingAddress']['aMiddleInitial']) ?  $order['aShippingAddress']['aMiddleInitial'] : '',
                'aNickName' => is_string($order['aShippingAddress']['aNickName']) ?  $order['aShippingAddress']['aNickName'] : '',
                'aPhone' => is_string($order['aShippingAddress']['aPhone']) ?  $order['aShippingAddress']['aPhone'] : '',
                'aPostalCode' => is_string($order['aShippingAddress']['aPostalCode']) ?  $order['aShippingAddress']['aPostalCode'] : '',
                'aRegionCode' => is_string($order['aShippingAddress']['aRegionCode']) ?  $order['aShippingAddress']['aRegionCode'] : '',
                'aRouteCode' => is_string($order['aShippingAddress']['aRouteCode']) ?  $order['aShippingAddress']['aRouteCode'] : '',
                'aThirdPartyId' => is_string($order['aShippingAddress']['aThirdPartyId']) ?  $order['aShippingAddress']['aThirdPartyId'] : '',
            ];
            $oldRecordAShippingAddress = AShippingAddress::where($saveAShippingAddress)->first();
            if (isset($oldRecordAShippingAddress->id)) {
                $AShippingAddress = $oldRecordAShippingAddress;
                $AShippingAddress->fill($saveAShippingAddress);
                $AShippingAddress->update();
            } else {
                $AShippingAddress = new AShippingAddress;
                $AShippingAddress->fill($saveAShippingAddress);
                $AShippingAddress->save();
            }
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



    public function GetOrdersByCriteria($user_id, $filters)
    {
        // dd($filters);
        $searchByUser = '<log:UserId>' . $user_id . '</log:UserId>';

        $startIndex = 0;
        $pageSize = 50;
        $orderbyStatus = '';
        $StartDate = '';
        $EndDate = '';
        if (isset($filters['StartDate']) && isset($filters['EndDate'])) {
            $startDate = Carbon::parse($filters['StartDate']);
            $endDate = Carbon::parse($filters['EndDate']);
            $formattedFirstDay = $startDate->format('Y-m-d\TH:i:s.uP');
            $formattedLastDay = $endDate->format('Y-m-d\TH:i:s.uP');
            $EndDate = '<log:EndDate>' . $formattedLastDay . '</log:EndDate>';
            $StartDate = '<log:StartDate>' . $formattedFirstDay . '</log:StartDate>';
        }

        if (isset($filters['startIndex']) && isset($filters['pageSize'])) {
            $startIndex =  $filters['startIndex'];
            $pageSize =   $filters['pageSize'];
        }

        if ($user_id == 0) {
            $searchByUser = '';
            $startIndex =  $filters['startIndex'];
            $pageSize =   $filters['pageSize'];
        }
        if (isset($filters['orderbyStatus']) && $filters['orderbyStatus'] == 'pending') {
            $orderbyStatus =  '<log:OrderStatusId>150C6299-B3CD-42da-907C-A380FDA03A93</log:OrderStatusId>';
            // dd($orderbyStatus);
        }

        if (isset($filters['orderbyStatus']) && $filters['orderbyStatus'] == 'approved') {
            $orderbyStatus =  '<log:OrderStatusId>d5414625-bfb3-4884-8015-f2fcb8296dc0</log:OrderStatusId>';
        }

        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);



        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:GetOrdersByCriteria>
         <tem:token>
            <log:ApiId>' . $check->ApiId . '</log:ApiId>
            <log:ExpirationDateUtc>' . $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
            <log:Id>' . $check->token . '</log:Id>
            <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
            <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
         </tem:token>
         <tem:criteria>
            ' . $orderbyStatus . '
            ' . $StartDate . '
            ' . $EndDate . '
            ' . $searchByUser . '
         </tem:criteria>
         <tem:startIndex>' . $startIndex . '</tem:startIndex>
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
        // dd($responseArray);
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
        $callClass = new ProductController;
        $aLineItems = [];

        if (!isset($orderInfoFromApi['aLineItems']['aLineItem'][0]) &&  isset($orderInfoFromApi['aLineItems']['aLineItem'])) {
            $aLineItem = array();
            array_push($aLineItem, $orderInfoFromApi['aLineItems']['aLineItem']);
            $aLineItems =  $aLineItem;
        } else if (isset($orderInfoFromApi['aLineItems']['aLineItem'])) {
            $aLineItems =  $orderInfoFromApi['aLineItems']['aLineItem'];
        }
        if (count($aLineItems)  === 1) {
            $productInfo =  $callClass->productInfo($aLineItems[0]['aProductId'], []);
            $aLineItems[0]['aImageFileLarge'] = $productInfo['aImageFileLarge'];
        }
        if (count($aLineItems) > 1) {
            foreach ($aLineItems as $key => $value) {
                $productInfo =  $callClass->productInfo($value['aProductId'], []);
                $aLineItems[$key]['aImageFileLarge'] = $productInfo['aImageFileLarge'];
            }
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
        // Assuming you have Carbon properly included at the beginning of your file
        // use Carbon\Carbon;

        $reorderInterval = 24;
        $suggestedNextOrderInfo = [];
        $addedProducts = [];
        // Ensure the necessity of the ProductController class
        // $callClass = new ProductController;

        foreach ($orderData as $order) {
            foreach ($order['aLineItems'] as $aLineItems) {
                $deliveryDate = Carbon::parse($order['aLastUpdated']);
                $nextOrderDate = $deliveryDate->copy()->addDays($reorderInterval);
                $productId = $aLineItems['aProductId'];
                $quantity = $aLineItems['aQuantity'];
                $orderId = $aLineItems['aOrderId'];

                if (!isset($addedProducts[$productId])) {
                    // Check if the next_order_date is not a past date from the current date
                    if ($nextOrderDate->isFuture() && $nextOrderDate->isAfter(Carbon::today())) {
                        $nextOrderInfo = [
                            'next_order_date' => $nextOrderDate->format('Y-m-d'),
                            'product_id' => $productId,
                            'product_info' => $aLineItems,
                            'order_id' => $orderId,
                            'quantity' => $quantity,
                        ];

                        // Check if the next order date is in the current or next month
                        $currentMonth = Carbon::now()->format('m');
                        $nextMonth = Carbon::now()->addMonth()->format('m');
                        $orderMonth = $nextOrderDate->format('m');

                        if ($orderMonth == $currentMonth || $orderMonth == $nextMonth) {
                            $suggestedNextOrderInfo[] = $nextOrderInfo;
                            $addedProducts[$productId] = true;
                        }
                    }
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
        $records = UserPurchaseHistory::where('user_id', $request->user_id)->orderby('id', 'desc')->with('aBillingAddress', 'aShippingAddress', 'aLineItems')->get();

        // $orders =  $this->GetOrdersByCriteria($user_id, $filters);
        $suggestedNextOrderInfo = $this->suggestNextOrderInfo($records, []);

        return response()->json(['status' => 1, 'message' => 'predictNextOrder fetched', 'response' => $suggestedNextOrderInfo]);
    }

    public function getPurchaseHistoryV2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $records = UserPurchaseHistory::where('user_id', $request->user_id)->orderby('id', 'desc')->with('aBillingAddress', 'aShippingAddress', 'aLineItems')->get();
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' => $records]);
    }

    public function getAllOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'startIndex' => 'required',
            'pageSize' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
            'orderbyStatus' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $RegisterClass  = new RegisterController();
        $check = $RegisterClass->GetUserAccountById($request);
        if (isset($check['aAccountRole']) && $check['aAccountRole'] != 'Admin') {
            return response()->json(['status' => 0, 'message' => __('Only For Admin..')]);
        }

        $filters = [
            'startIndex' => $request->startIndex,
            'pageSize' => $request->pageSize,
            // 'StartDate' => $request->startDate,
            // 'EndDate' => $request->endDate,
            'orderbyStatus' => $request->orderbyStatus
        ];
        $orders = $this->GetOrdersByCriteria(0, $filters);
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' => $orders]);
    }


    public function updateOrder(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'order_id' => 'required',
                'order_status_id' => 'required',
                'order_status_name' => 'required',
            ]);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
            }

            $tokenData = User::where('UID', '01HV60F7V1EN6DFY4R7PG351SK')->first();
            // dd($tokenData);
            $refreshtoken   = new RegisterController();
            $check = $refreshtoken->refreshTokenV1($tokenData);
            // dd($check);
            // Construct SOAP envelope
            $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
         <soapenv:Header/>
         <soapenv:Body>
            <tem:UpdateOrder>
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
               <tem:orderId>' . $request->order_id . '</tem:orderId>
               <!--Optional:-->
               <tem:order>
                  <!--Optional:-->
                  <log:OrderStatusId>' . $request->order_status_id . '</log:OrderStatusId>
                  <!--Optional:-->
                  <log:OrderStatusName>' . $request->order_status_name . '</log:OrderStatusName>
               </tem:order>
            </tem:UpdateOrder>
         </soapenv:Body>
      </soapenv:Envelope>';

            // Initialize cURL for the SOAP request
            $curl = curl_init();

            // Configure cURL options
            dd($soapRequest);
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
                    'SOAPAction: http://tempuri.org/IOrdersService/UpdateOrder'
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
            dd($responseArray);
            $order = [];
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['status' => 0, 'message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
    public function getOrderStatuses(Request $request)
    {
        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        // Construct SOAP envelope for retrieving order statuses
        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
           <soapenv:Header/>
           <soapenv:Body>
              <tem:GetOrderStatuses>
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
              </tem:GetOrderStatuses>
           </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

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
                'SOAPAction: http://tempuri.org/IOrdersService/GetOrderStatuses'
            ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            echo 'cURL error: ' . $response;
        }
        curl_close($curl);
        $xmlResponse = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xmlResponse = simplexml_load_string($xmlResponse);
        $jsonResponse = json_encode($xmlResponse);
        $responseArray = json_decode($jsonResponse, true);
        $statues = $responseArray['sBody']['GetOrderStatusesResponse']['GetOrderStatusesResult']['aOrderStatus'];
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' => $statues]);
    }
}
