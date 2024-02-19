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



    // public function productInfo($productIds, $userInfo)
    // {
    //     $tokenData = User::first();
    //     $refreshtoken   =   new RegisterController();
    //     $check = $refreshtoken->refreshToken($tokenData);
    //     $findProduct =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
    //           <soapenv:Header/>
    //           <soapenv:Body>
    //              <tem:FindProductsByIds>
    //                 <!--Optional:-->
    //                 <tem:token>
    //                    <!--Optional:-->
    //                    <log:ApiId>' . $userInfo->ApiId . '</log:ApiId>
    //                    <!--Optional:-->
    //                    <log:ExpirationDateUtc>' . $userInfo->ExpirationDateUtc . '</log:ExpirationDateUtc>
    //                    <!--Optional:-->
    //                    <log:Id>' . $check->token . '</log:Id>
    //                    <!--Optional:-->
    //                    <log:IsExpired>' . $userInfo->IsExpired . '</log:IsExpired>
    //                    <!--Optional:-->
    //                    <log:TokenRejected>' . $userInfo->TokenRejected . '</log:TokenRejected>
    //                 </tem:token>
    //                 <!--Optional:-->
    //                 <tem:productIds>
    //                    <!--Zero or more repetitions:-->
    //                    <arr:string>?</arr:string><arr:string>' . $productIds . '</arr:string>
    //                 </tem:productIds>
    //              </tem:FindProductsByIds>
    //           </soapenv:Body>
    //        </soapenv:Envelope>';

    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $findProduct,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: text/xml; charset=utf-8',
    //             'SOAPAction: http://tempuri.org/ICatalogService/FindProductsByIds'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
    //     $xml = simplexml_load_string($xml);
    //     $json = json_encode($xml);
    //     $responseArray = json_decode($json, true);
    //     $return = [];
    //     if (isset($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct']) && !empty($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'])) {
    //         $return = $responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'];
    //     }
    //     return $return;
    // }

    // public function orderInfo($order_id)
    // {
    //     $tokenData = User::first();
    //     $refreshtoken   =   new RegisterController();
    //     $check = $refreshtoken->refreshToken($tokenData);
    //     $GetOrder  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
    //         <soapenv:Header/>
    //         <soapenv:Body>
    //         <tem:GetOrderById>
    //             <!--Optional:-->
    //             <tem:token>
    //             <log:ApiId>' . $check->ApiId . '</log:ApiId>
    //             <!--Optional:-->
    //             <log:ExpirationDateUtc>' .  $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
    //             <!--Optional:-->
    //             <log:Id>' .  $check->token . '</log:Id>
    //             <!--Optional:-->
    //             <log:IsExpired>' .  $check->IsExpired . '</log:IsExpired>
    //             <!--Optional:-->
    //             <log:TokenRejected>' .  $check->TokenRejected . '</log:TokenRejected>
    //             </tem:token>
    //             <tem:orderId>' . $order_id . '</tem:orderId>
    //             </tem:GetOrderById>
    //         </soapenv:Body>
    //     </soapenv:Envelope>';

    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $GetOrder,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: text/xml; charset=utf-8',
    //             'SOAPAction: http://tempuri.org/IOrdersService/GetOrderById'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     if (curl_errno($curl)) {
    //         $response = curl_error($curl);
    //         return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
    //     }
    //     curl_close($curl);
    //     $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
    //     $xml = simplexml_load_string($xml);
    //     $json = json_encode($xml);
    //     $responseArrays = json_decode($json, true);

    //     return $responseArrays;
    // }



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
        $userInfo = User::where('UID', $request->user_id)->first();
        foreach ($result as $key => $value) {
            $value['productInfo'] = $ProductClass->productInfo($value->product_id, $userInfo);
            $value['orderInfo'] = $ProductClass->orderInfo($value->order_id);
        }
        return response()->json(['status' => 1, 'message' => 'Record Fetched', 'response' =>  $result]);
    }

    public function addToInventory(Request $request)
    {

        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
            'product_id' => 'required',
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
        ];
        $Inventory = new Inventory;

        $existsRecord = Inventory::where([
            'user_id' => $request->user_id,  'order_id' => $request->order_id,
            'product_id' => $request->product_id,
        ])->first();

        if (isset($existsRecord->user_id)) {
            $Inventory =  $existsRecord;
        }
        $Inventory->fill($save);
        $Inventory->save();
        return response()->json(['status' => 2, 'message' => 'addToInventory', 'result' => $request->all()]);
    }
}
