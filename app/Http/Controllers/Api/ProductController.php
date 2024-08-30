<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Models\OrderHistory;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\FavouriteProduct;
use Carbon\Carbon;


class ProductController extends Controller
{

    public function productInfo($productIds, $userInfo)
    {
        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $findProduct =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
              <soapenv:Header/>
              <soapenv:Body>
                 <tem:FindProductsByIds>
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
                    <tem:productIds>
                       <!--Zero or more repetitions:-->
                       <arr:string>?</arr:string><arr:string>' . $productIds . '</arr:string>
                    </tem:productIds>
                 </tem:FindProductsByIds>
              </soapenv:Body>
           </soapenv:Envelope>';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $findProduct,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/ICatalogService/FindProductsByIds'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        $return = [];
        if (isset($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct']) && !empty($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'])) {
            $return = $responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'];
        }
        return $return;
    }



    public function orderInfo($order_id)
    {
        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $GetOrder  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:GetOrderById>
                <!--Optional:-->
                <tem:token>
                <log:ApiId>' . $check->ApiId . '</log:ApiId>
                <!--Optional:-->
                <log:ExpirationDateUtc>' .  $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
                <!--Optional:-->
                <log:Id>' .  $check->token . '</log:Id>
                <!--Optional:-->
                <log:IsExpired>' .  $check->IsExpired . '</log:IsExpired>
                <!--Optional:-->
                <log:TokenRejected>' .  $check->TokenRejected . '</log:TokenRejected>
                </tem:token>
                <tem:orderId>' . $order_id . '</tem:orderId>
                </tem:GetOrderById>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $GetOrder,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/GetOrderById'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArrays = json_decode($json, true);

        return $responseArrays;
    }


    public function FindAllVendors(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'token' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        try {

            $tokenData = User::first();
            $refreshtoken   =   new RegisterController();
            $check = $refreshtoken->refreshToken($tokenData);


            $allVendor =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:FindAllVendors>
                    <!--Optional:-->
                    <tem:token>
                        <!--Optional:-->
                        <log:ApiId>' . $request->ApiId . '</log:ApiId>
                        <!--Optional:-->
                        <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                        <!--Optional:-->
                        <log:Id>' . $check->token . '</log:Id>
                        <!--Optional:-->
                        <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                        <!--Optional:-->
                        <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                    </tem:token>
                </tem:FindAllVendors>
            </soapenv:Body>
            </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $allVendor,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/ICatalogService/FindAllVendors'
                ),
            ));

            $response = curl_exec($curl);


            if (curl_errno($curl)) {
                $response = curl_error($curl);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);

            if (isset($responseArray['sBody']['FindAllVendorsResponse']['FindAllVendorsResult']) && !empty($responseArray['sBody']['FindAllVendorsResponse']['FindAllVendorsResult'])) {

                return response()->json(['status' => 1, 'message' => 'vendor listing', 'vendor list' => $responseArray['sBody']['FindAllVendorsResponse']['FindAllVendorsResult'], 'response' => $response]);
            } else {
                return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
            }
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    public function FindProductsByIds(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
         //   'user_id' => 'required'

        ]);
        // dd('gg');
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected', 'productIds', 'user_id');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }
        try {
            $tokenData = User::first();
            $refreshtoken   =   new RegisterController();
            $check = $refreshtoken->refreshToken($tokenData);

            $findProduct =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
          <soapenv:Header/>
          <soapenv:Body>
             <tem:FindProductsByIds>
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
                <tem:productIds>
                   <!--Zero or more repetitions:-->
                   <arr:string>?</arr:string><arr:string>' . $request->productIds . '</arr:string>
                </tem:productIds>
             </tem:FindProductsByIds>
          </soapenv:Body>
       </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $findProduct,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/ICatalogService/FindProductsByIds'
                ),
            ));

            $response = curl_exec($curl);


            if (curl_errno($curl)) {
                $response = curl_error($curl);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);

            
            $FavouriteProduct =  FavouriteProduct::where('user_id', @$request->user_id)->where('product_id', $request->productIds)->first();
            $recordExiste = false;
            $Favoriteid = null;
            if (isset($FavouriteProduct->id)) {
                $recordExiste = true;
                $Favoriteid = $FavouriteProduct->id;
            }
            if (isset($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct']) && !empty($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'])) {
                $produtInfo =  $responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'];

                if (gettype($produtInfo['aLongDescription']) == 'string') {
                    $produtInfo['aLongDescription'] = strip_tags($produtInfo['aLongDescription']);
                } else {
                    $produtInfo['aLongDescription'] = '';
                }

                return response()->json(['status' => 1, 'message' => 'product', 'FavouriteProduct' => $recordExiste, 'Favoriteid' => $Favoriteid, 'product' => $produtInfo, 'response' => $response]);
            } else {
                return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
            }
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    public function GetRootCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        try {
            $tokenData = User::first();
            $refreshtoken   =   new RegisterController();
            $check = $refreshtoken->refreshToken($tokenData);
            $GetRootCategories  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:GetRootCategories>
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
                        </tem:GetRootCategories>
                    </soapenv:Body>
                </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $GetRootCategories,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/ICatalogService/GetRootCategories'
                ),
            ));

            $response = curl_exec($curl);


            if (curl_errno($curl)) {
                $response = curl_error($curl);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);

            if (isset($responseArray['sBody']['GetRootCategoriesResponse']['GetRootCategoriesResult']['aCategory']) && !empty($responseArray['sBody']['GetRootCategoriesResponse']['GetRootCategoriesResult']['aCategory'])) {
                $aCategoryfromLive = $responseArray['sBody']['GetRootCategoriesResponse']['GetRootCategoriesResult']['aCategory'];
                // Category::truncate();
                foreach ($aCategoryfromLive as $key => $value) {
                    // print_r($value['aName']);
                    $oldRecord = Category::where('aId', $value['aId'])->where('aName', $value['aName'])->where('aParentId', $value['aParentId'])->first();
                    if (!isset($oldRecord->id)) {
                        $save = [
                            'aName' =>  $value['aName'],
                            'aId' =>  $value['aId'],
                            'aParentId' =>  $value['aParentId'],
                        ];
                        $Category =  new Category;
                        $Category->fill($save);
                        $Category->save();
                    }
                }

                // dd($aCategoryfromLive);
                return response()->json(['status' => 1, 'message' => 'root category', 'category' => $responseArray['sBody']['GetRootCategoriesResponse']['GetRootCategoriesResult']['aCategory'], 'response' => $response]);
            } else {
                return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
            }
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    public function GetCategoryChildren(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'category_id' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'category_id', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        try {
            $GetRootCategories  =
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:GetCategoryChildren>
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
                    <tem:parentId>' . $request->category_id . '</tem:parentId>

                </tem:GetCategoryChildren>
            </soapenv:Body>
            </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $GetRootCategories,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/ICatalogService/GetCategoryChildren'
                ),
            ));

            $response = curl_exec($curl);


            if (curl_errno($curl)) {
                $response = curl_error($curl);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);

            // dd($responseArray['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']);

            if (isset($responseArray['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']) && !empty($responseArray['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory'])) {

                $sub_subCategory = [];
                $aCategory = $responseArray['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory'];
                if (isset($aCategory['aId'])) {
                    $newArray[] = [
                        "aDescription" => [],
                        "aId" => $aCategory['aId'],
                        "aName" => $aCategory['aName'],
                        "aParentId" =>  $aCategory['aParentId']
                    ];
                    $aCategory =  $newArray;
                }
                foreach ($aCategory as $key => $value) {
                    if (gettype($value) == 'array') {
                        $GetsubCategories  =
                            '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:GetCategoryChildren>
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
                            <tem:parentId>' . $value['aId'] . '</tem:parentId>
                        </tem:GetCategoryChildren>
                    </soapenv:Body>
                    </soapenv:Envelope>';

                        $curl1 = curl_init();

                        curl_setopt_array($curl1, array(
                            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => $GetsubCategories,
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: text/xml; charset=utf-8',
                                'SOAPAction: http://tempuri.org/ICatalogService/GetCategoryChildren'
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

                        if (isset($responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']) && !empty($responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory'])) {
                            if (array_keys($responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']) !== range(0, count($responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']) - 1)) {
                                $subCategory = [$responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory']];
                            } else {
                                $subCategory = $responseArray1['sBody']['GetCategoryChildrenResponse']['GetCategoryChildrenResult']['aCategory'];
                            }
                            $sub_subCategory[]  = ['aId' => $value['aId'], 'title' => $value['aName'], 'sub_Category' => $subCategory];
                        } else {
                        }
                    }
                }
            }
            // dd($sub_subCategory);
            if (isset($sub_subCategory) && !empty($sub_subCategory)) {
                foreach ($sub_subCategory as $key => $value) {
                    // print_r($value);
                    $oldRecord = Category::where('aName', $value['title'])->where('aParentId', $request->category_id)->first();
                    if (!isset($oldRecord->id)) {
                        $save = [
                            'aName' =>  $value['title'],
                            'aId' =>  $value['aId'],
                            'aParentId' =>   $request->category_id,
                        ];
                        $Category =  new Category;
                        $Category->fill($save);
                        $Category->save();
                    }

                    foreach ($value['sub_Category'] as $sub_subCategoryLive) {
                        // dd($sub_subCategoryLive['aId']);
                        $oldRecord = Category::where('aId', $sub_subCategoryLive['aId'])->where('aName', $sub_subCategoryLive['aName'])->where('aParentId',  $value['aId'])->first();
                        if (!isset($oldRecord->id)) {
                            $save = [
                                'aName' =>  $sub_subCategoryLive['aName'],
                                'aId' =>  $sub_subCategoryLive['aId'],
                                'aParentId' =>   $value['aId'],
                                // 'aDescription' => $sub_subCategoryLive['aDescription'],
                            ];
                            $Category =  new Category;
                            $Category->fill($save);
                            $Category->save();
                        }
                    }
                }
                // dd('ss');
                return response()->json(['status' => 1, 'message' => 'root category', 'category' => $sub_subCategory]);
            } else {
                return response()->json(['status' => 0, 'message' => 'something went wrong'], 400);
            }
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['status' => 0, 'message' => $e], 400);
        }
    }


    public function GetCategoryById(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'category_id' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'category_id', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        try {
            $GetRootCategories  =
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                <soapenv:Header/>
                <soapenv:Body>
                   <tem:GetCategoryById>
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
                      <tem:categoryId>' . $request->category_id . '</tem:categoryId>
                   </tem:GetCategoryById>
                </soapenv:Body>
             </soapenv:Envelope>';


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $GetRootCategories,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/ICatalogService/GetCategoryById'
                ),
            ));

            $response = curl_exec($curl);


            if (curl_errno($curl)) {
                $response = curl_error($curl);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);


            if (isset($responseArray['sBody']['GetCategoryByIdResponse']['GetCategoryByIdResult']) && !empty($responseArray['sBody']['GetCategoryByIdResponse']['GetCategoryByIdResult'])) {

                return response()->json(['status' => 1, 'message' => 'category', 'CategoryDetail' => $responseArray['sBody']['GetCategoryByIdResponse']['GetCategoryByIdResult'], 'response' => $response]);
            } else {
                return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
            }
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    // public function GetfeatureProduct(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [

    //         // 'ApiId' => 'required',
    //         // 'ExpirationDateUtc' => 'required',
    //         // // 'productIds' => 'required',
    //         // 'IsExpired' => 'required',
    //         // 'TokenRejected' => 'required'

    //     ]);
    //     $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
    //     $error_message = "";
    //     if ($validator->fails()) {
    //         foreach ($fields as $field) {
    //             if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

    //                 $error_message = __($validator->errors()->getMessages()[$field][0]);

    //                 return response()->json(['status' => 0, 'message' => $error_message]);
    //             }
    //         }
    //     }


    //     $tokenData = User::first();
    //     $refreshtoken   =   new RegisterController();
    //     $check = $refreshtoken->refreshToken($tokenData);
    //     $GetRootCategories  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
    //                             <soapenv:Header/>
    //                             <soapenv:Body>
    //                             <tem:GetCategoryProducts>
    //                                 <!--Optional:-->
    //                                 <tem:token>
    //                                 <log:ApiId>' . $check->ApiId . '</log:ApiId>
    //                                 <!--Optional:-->
    //                                 <log:ExpirationDateUtc>' . $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
    //                                 <!--Optional:-->
    //                                 <log:Id>' . $check->token . '</log:Id>
    //                                 <!--Optional:-->
    //                                 <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
    //                                 <!--Optional:-->
    //                                 <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
    //                                 </tem:token>
    //                                 <!--Optional:-->
    //                                 <tem:categoryId>01HP2YQ6ZQ6Y5SH34XH3VG7XX6</tem:categoryId>
    //                                 <!--Optional:-->
    //                                 <tem:startRowIndex>0</tem:startRowIndex>
    //                                 <!--Optional:-->
    //                                 <tem:maximumRows>10</tem:maximumRows>
    //                             </tem:GetCategoryProducts>
    //                             </soapenv:Body>
    //                         </soapenv:Envelope>';

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
    //         CURLOPT_POSTFIELDS => $GetRootCategories,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: text/xml; charset=utf-8',
    //             'SOAPAction: http://tempuri.org/ICatalogService/GetCategoryProducts'
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
    //     $responseArray = json_decode($json, true);
    //     if (isset($responseArray['sBody']['GetCategoryProductsResponse']['GetCategoryProductsResult']) && !empty($responseArray['sBody']['GetCategoryProductsResponse'])) {
    //         $finalResults = [];
    //         foreach ($responseArray['sBody']['GetCategoryProductsResponse']['GetCategoryProductsResult']['aList']['aProduct'] as $key => $product) {
    //             if ($product["aStatus"] == "Active") {
    //                 $finalResults[] = $product;
    //             }
    //         }
    //         return response()->json(['status' => 1, 'message' => 'feature Product', 'Products' => $finalResults]);
    //     } else {
    //         return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
    //     }
    // }


    public function GetfeatureProduct(Request $request)
    {

        $tokenData = User::first();
        $refreshtoken = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $skusXml = '<arr:string>GBBFOYWater</arr:string>
                   <arr:string>GBBCPAED598</arr:string>
                   <arr:string>GBBAAMC144</arr:string>
                   <arr:string>MOR400WY</arr:string>
                   <arr:string>GBBCPN123-90425</arr:string>
                   <arr:string>GBB-SI-ICX9078-0565</arr:string>
                   <arr:string>KCC47305</arr:string>
                   <arr:string>MORM1000</arr:string>
                   <arr:string>BWK6200</arr:string>
                   <arr:string>GBBCPN88-90415</arr:string>
                   <arr:string>BWK522</arr:string>
                   <arr:string>GBBSTBKT4PK</arr:string>
                    ';


        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
                        <soapenv:Header/>
                        <soapenv:Body>
                            <tem:FindProductsBySkus>
                                <tem:token>
                                    <log:ApiId>' . $check->ApiId . '</log:ApiId>
                                    <log:ExpirationDateUtc>' . $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
                                    <log:Id>' . $check->token . '</log:Id>
                                    <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
                                    <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
                                </tem:token>
                                <tem:skus>' . $skusXml . '</tem:skus>
                            </tem:FindProductsBySkus>
                        </soapenv:Body>
                    </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
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
                'SOAPAction: http://tempuri.org/ICatalogService/FindProductsBySkus'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'curl error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);


        if (isset($responseArray['sBody']['FindProductsBySkusResponse']['FindProductsBySkusResult']) && !empty($responseArray['sBody']['FindProductsBySkusResponse'])) {
            $finalResults = $responseArray['sBody']['FindProductsBySkusResponse']['FindProductsBySkusResult']['aProduct'];
            return response()->json(['status' => 1, 'message' => 'Feature Product', 'Products' => $finalResults]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }




    public function GetCategoryProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'ApiId' => 'required',
            // 'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            // 'IsExpired' => 'required',
            // 'TokenRejected' => 'required',
            'category_id' =>   'required',
            'startRowIndex' =>   'required',
            'maximumRows' =>   'required'
        ]);

        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $GetRootCategories  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:GetCategoryProducts>
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
                <tem:categoryId>' . $request->category_id . '</tem:categoryId>
                <!--Optional:-->
                <tem:startRowIndex>' . $request->startRowIndex . '</tem:startRowIndex>
                <!--Optional:-->
                <tem:maximumRows>' . $request->maximumRows . '</tem:maximumRows>
            </tem:GetCategoryProducts>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $GetRootCategories,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/ICatalogService/GetCategoryProducts'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        if (isset($responseArray['sBody']['GetCategoryProductsResponse']['GetCategoryProductsResult']['aList']['aProduct']) && !empty($responseArray['sBody']['GetCategoryProductsResponse']['GetCategoryProductsResult']['aList']['aProduct'])) {
            return response()->json(['status' => 1, 'message' => 'feature Product', 'Products' => $responseArray['sBody']['GetCategoryProductsResponse']['GetCategoryProductsResult']['aList']['aProduct']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }


    public function CreateNewUnplacedOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $street = '?';
        $code = '?';
        $postalCode = '?';
        $administrativeArea = '?';
        $subadministrativeArea = '?';
        $locality = '?';
        $subLocality = '?';
        if (isset($request->street) && !empty($request->street)) {
            $street = $request->street;
        }
        if (isset($request->code) && !empty($request->code)) {
            $code = $request->code;
        }
        if (isset($request->postalCode) && !empty($request->postalCode)) {
            $postalCode = $request->postalCode;
        }
        if (isset($request->administrativeArea) && !empty($request->administrativeArea)) {
            $administrativeArea = $request->administrativeArea;
        }
        if (isset($request->subadministrativeArea) && !empty($request->subadministrativeArea)) {
            $subadministrativeArea = $request->subadministrativeArea;
        }
        if (isset($request->locality) && !empty($request->locality)) {
            $locality = $request->locality;
        }
        if (isset($request->subLocality) && !empty($request->subLocality)) {
            $subLocality = $request->subLocality;
        }

        try {

            $tokenData = User::first();
            $userInfo   = DB::table('users')->where('UID', $request->ApiId)->first();
            $refreshtoken   =   new RegisterController();
            $getUserInfoThirdParty =  $refreshtoken->GetUserInfoThirdParty($request->ApiId);

            $check = $refreshtoken->refreshToken($tokenData);

            $checkOrder   = DB::table('order_and_product')->where(['UID' => $request->ApiId, 'product_id' => $request->product_id])->first();

            if (!empty($checkOrder)) {

                $updateLineItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                   <soapenv:Header/>
                   <soapenv:Body>
                   <tem:UpdateLineItems>
                   <!--Optional:-->
                   <tem:token>
                   <log:ApiId>' . $request->ApiId . '</log:ApiId>
                   <!--Optional:-->
                   <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                   <!--Optional:-->
                   <log:Id>' . $check->token . '</log:Id>
                   <!--Optional:-->
                   <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                   <!--Optional:-->
                   <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                    </tem:token>
                   <!--Optional:-->
                   <tem:orderId>' . $checkOrder->order_id . '</tem:orderId>
                   <!--Optional:-->
                   <tem:lineItems>
                   <!--Zero or more repetitions:-->
                   <log:LineItemExt>
                   <!--Optional:-->
                   <log:AdditionalDiscount>00.00</log:AdditionalDiscount>
                   <!--Optional:-->
                   <log:BasePrice>00.00</log:BasePrice>
                   <!--Optional:-->
                   <log:BudgetCode>?</log:BudgetCode>
                   <!--Optional:-->
                   <log:Comment>?</log:Comment>
                   <!--Optional:-->
                   <log:Cost>00.00</log:Cost>
                   <!--Optional:-->
                   <log:Discounts>00.00</log:Discounts>
                   <!--Optional:-->
                   <log:Id>' . $request->line_item_id . '</log:Id>
                   <!--Optional:-->
                   <log:IsAdminPrice>false</log:IsAdminPrice>
                   <!--Optional:-->
                   <log:IsChannelPrice>false</log:IsChannelPrice>
                   <!--Optional:-->
                   <log:LastUpdated>' . $request->today_date . '</log:LastUpdated>
                   <!--Optional:-->
                   <log:LineTotal>00.00</log:LineTotal>
                   <!--Optional:-->
                   <log:OrderId>' . $checkOrder->order_id . '</log:OrderId>
                   <!--Optional:-->
                   <log:OrderTransmissionItem>
                   <!--Optional:-->
                   <log:OrderTransmissionId>?</log:OrderTransmissionId>
                   <!--Optional:-->
                   <log:TransmissionItemStatus>Unknown</log:TransmissionItemStatus>
                   </log:OrderTransmissionItem>
                   <!--Optional:-->
                   <log:PresetName>?</log:PresetName>
                   <!--Optional:-->
                   <log:PriceRuleId>?</log:PriceRuleId>
                   <!--Optional:-->
                   <log:ProductId>' . $request->product_id . '</log:ProductId>
                   <!--Optional:-->
                   <log:ProductName>?</log:ProductName>
                   <!--Optional:-->
                   <log:ProductSku>?</log:ProductSku>
                   <!--Optional:-->
                   <log:ProductUnitOfMeasure>?</log:ProductUnitOfMeasure>
                   <!--Optional:-->
                   <log:Quantity>' . $request->quantity . '</log:Quantity>
                   <!--Optional:-->
                   <log:QuantityReturned>0</log:QuantityReturned>
                   <!--Optional:-->
                   <log:QuantityShipped>0</log:QuantityShipped>
                   <!--Optional:-->
                   <log:QuantityTransmitted>0</log:QuantityTransmitted>
                   <!--Optional:-->
                   <log:ShippingPortion>00.00</log:ShippingPortion>
                   <!--Optional:-->
                   <log:TaxExempt>true</log:TaxExempt>
                   <!--Optional:-->
                   <log:TaxPortion>00.00</log:TaxPortion>
                   <!--Optional:-->
                   <log:UnitCost>00.00</log:UnitCost>
                   <!--Optional:-->
                   <log:UnitPrice>00</log:UnitPrice>
                   <!--Optional:-->
                   <log:VendorId>?</log:VendorId>
                   </log:LineItemExt>
                   </tem:lineItems>
                   </tem:UpdateLineItems>
                   </soapenv:Body>
                </soapenv:Envelope>';
                $curl2 = curl_init();
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $updateLineItem,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: text/xml; charset=utf-8',
                        'SOAPAction: http://tempuri.org/IOrdersService/UpdateLineItems'
                    ),
                ));

                $response1 = curl_exec($curl2);
                if (curl_errno($curl2)) {
                    $response1 = curl_error($curl2);
                    return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response1], 400);
                }
                curl_close($curl2);
                $xml1 = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response1);
                $xml1 = simplexml_load_string($xml1);
                $json1 = json_encode($xml1);
                $responseArray1 = json_decode($json1, true);
                // dd($responseArray1);
                if (isset($responseArray1['sBody']['UpdateLineItemsResponse']['UpdateLineItemsResult']) && !empty($responseArray1['sBody']['UpdateLineItemsResponse']['UpdateLineItemsResult'])) {

                    return response()->json(['status' => 1, 'message' => 'add cart', 'order_id' => $checkOrder->order_id, 'line_item_id' => $request->line_item_id, 'item_update' => $responseArray1['sBody']['UpdateLineItemsResponse']['UpdateLineItemsResult']]);
                } else {
                    return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => []], 400);
                }
            }


            $addCart = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                <soapenv:Header/>
                <soapenv:Body>
                <tem:CreateNewUnplacedOrder>
                <!--Optional:-->
                <tem:token>
                    <log:ApiId>' . $request->ApiId . '</log:ApiId>
                    <!--Optional:-->
                    <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                    <!--Optional:-->
                    <log:Id>' . $check->token . '</log:Id>
                    <!--Optional:-->
                    <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                    <!--Optional:-->
                    <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                </tem:token>
                <!--Optional:-->
                <tem:billingAddress>
                <!--Optional:-->
                <log:BackendUserId>?</log:BackendUserId>
                <!--Optional:-->
                <log:BranchCode>?</log:BranchCode>
                <!--Optional:-->
                <log:City>?</log:City>
                <!--Optional:-->
                <log:Company>?</log:Company>
                <!--Optional:-->
                <log:CountryCode>' . $request->code . '</log:CountryCode>
                <!--Optional:-->
                <log:DepartmentId>?</log:DepartmentId>
                <!--Optional:-->
                <log:DepartmentName>?</log:DepartmentName>
                <!--Optional:-->
                <log:Enabled>true</log:Enabled>
                <!--Optional:-->
                <log:Fax>?</log:Fax>
                <!--Optional:-->
                <log:FirstName>' . $userInfo->name . '</log:FirstName>
                <!--Optional:-->
                <log:Id>?</log:Id>
                <!--Optional:-->
                <log:LastName>?</log:LastName>
                <!--Optional:-->
                <log:Line1>?</log:Line1>
                <!--Optional:-->
                <log:Line2>?</log:Line2>
                <!--Optional:-->
                <log:Line3>?</log:Line3>
                <!--Optional:-->
                <log:MiddleInitial>?</log:MiddleInitial>
                <!--Optional:-->
                <log:NickName>' . $userInfo->name . '</log:NickName>
                <!--Optional:-->
                <log:Phone>?</log:Phone>
                <!--Optional:-->
                <log:PostalCode>' . $postalCode . '</log:PostalCode>
                <!--Optional:-->
                <log:RegionCode>?</log:RegionCode>
                <!--Optional:-->
                <log:RouteCode>?</log:RouteCode>
                <!--Optional:-->
                <log:ThirdPartyId>?</log:ThirdPartyId>
                </tem:billingAddress>
                <!--Optional:-->
                <tem:shippingAddress>
                <!--Optional:-->
                <log:BackendUserId>?</log:BackendUserId>
                <!--Optional:-->
                <log:BranchCode>?</log:BranchCode>
                <!--Optional:-->
                <log:City>?</log:City>
                <!--Optional:-->
                <log:Company>?</log:Company>
                <!--Optional:-->
                <log:CountryCode>' . $code . '</log:CountryCode>
                <!--Optional:-->
                <log:DepartmentId>?</log:DepartmentId>
                <!--Optional:-->
                <log:DepartmentName>?</log:DepartmentName>
                <!--Optional:-->
                <log:Enabled>true</log:Enabled>
                <!--Optional:-->
                <log:Fax>?</log:Fax>
                <!--Optional:-->
                <log:FirstName>' . $userInfo->name . '</log:FirstName>
                <!--Optional:-->
                <log:Id>?</log:Id>
                <!--Optional:-->
                <log:LastName>' . $userInfo->name . '</log:LastName>
                <!--Optional:-->
                <log:Line1>?</log:Line1>
                <!--Optional:-->
                <log:Line2>?</log:Line2>
                <!--Optional:-->
                <log:Line3>?</log:Line3>
                <!--Optional:-->
                <log:MiddleInitial>?</log:MiddleInitial>
                <!--Optional:-->
                <log:NickName>' . $userInfo->name . '</log:NickName>
                <!--Optional:-->
                <log:Phone>?</log:Phone>
                <!--Optional:-->
                <log:PostalCode>' . $postalCode . '</log:PostalCode>
                <!--Optional:-->
                <log:RegionCode></log:RegionCode>
                <!--Optional:-->
                <log:RouteCode>?</log:RouteCode>
                <!--Optional:-->
                <log:ThirdPartyId>?</log:ThirdPartyId>
                </tem:shippingAddress>
                <!--Optional:-->
                <tem:userId>' . $request->ApiId . '</tem:userId>
                <!--Optional:-->
                <tem:userEmail>' . $userInfo->email . '</tem:userEmail>
                <!--Optional:-->
                <tem:timeoforder>2023-05-12T00:00:00.000+05:00</tem:timeoforder>
                </tem:CreateNewUnplacedOrder>
                </soapenv:Body>
            </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $addCart,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/IOrdersService/CreateNewUnplacedOrder'
                ),
            ));

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $response = curl_error($curl);
                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);

            if (isset($responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult']) && !empty($responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'])) {

                DB::table('order_and_product')->insert(['order_id' => $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'], 'UID' => $request->ApiId, 'product_id' => $request->product_id]);
                $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                    <soapenv:Header/>
                    <soapenv:Body>
                    <tem:AddLineItemByProductId>
                    <!--Optional:-->
                    <tem:token>
                    <log:ApiId>' . $request->ApiId . '</log:ApiId>
                    <!--Optional:-->
                    <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                    <!--Optional:-->
                    <log:Id>' . $check->token . '</log:Id>
                    <!--Optional:-->
                    <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                    <!--Optional:-->
                    <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                    </tem:token>
                    <!--Optional:-->
                    <tem:orderId>' . $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'] . '</tem:orderId>
                    <!--Optional:-->
                    <tem:productId>' . $request->product_id . '</tem:productId>
                    <!--Optional:-->
                    <tem:quantity>' . $request->quantity . '</tem:quantity>
                    </tem:AddLineItemByProductId>
                    </soapenv:Body>
                </soapenv:Envelope>';

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
                    return response()->json(['status' => 1, 'message' => 'add Cart sucessfully', 'order_id' => $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'], 'line_item_id' => $responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult'], 'getUserInfoThirdParty' => $getUserInfoThirdParty]);
                } else {
                    return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
                }
            }
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }


    public function CreateNewUnplacedOrderV1($userInfo, $product_id, $quantity)
    {
        $return = [];
        $code = '?';
        $postalCode = '?';
        $tokenData = User::first();
        // $userInfo   = DB::table('users')->where('UID', $data->ApiId)->first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $addCart = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                <soapenv:Header/>
                <soapenv:Body>
                <tem:CreateNewUnplacedOrder>
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
                <tem:billingAddress>
                <!--Optional:-->
                <log:BackendUserId>?</log:BackendUserId>
                <!--Optional:-->
                <log:BranchCode>?</log:BranchCode>
                <!--Optional:-->
                <log:City>?</log:City>
                <!--Optional:-->
                <log:Company>?</log:Company>
                <!--Optional:-->
                <log:CountryCode>' . $code . '</log:CountryCode>
                <!--Optional:-->
                <log:DepartmentId>?</log:DepartmentId>
                <!--Optional:-->
                <log:DepartmentName>?</log:DepartmentName>
                <!--Optional:-->
                <log:Enabled>true</log:Enabled>
                <!--Optional:-->
                <log:Fax>?</log:Fax>
                <!--Optional:-->
                <log:FirstName>' . $userInfo->name . '</log:FirstName>
                <!--Optional:-->
                <log:Id>?</log:Id>
                <!--Optional:-->
                <log:LastName>?</log:LastName>
                <!--Optional:-->
                <log:Line1>?</log:Line1>
                <!--Optional:-->
                <log:Line2>?</log:Line2>
                <!--Optional:-->
                <log:Line3>?</log:Line3>
                <!--Optional:-->
                <log:MiddleInitial>?</log:MiddleInitial>
                <!--Optional:-->
                <log:NickName>' . $userInfo->name . '</log:NickName>
                <!--Optional:-->
                <log:Phone>?</log:Phone>
                <!--Optional:-->
                <log:PostalCode>' . $postalCode . '</log:PostalCode>
                <!--Optional:-->
                <log:RegionCode>?</log:RegionCode>
                <!--Optional:-->
                <log:RouteCode>?</log:RouteCode>
                <!--Optional:-->
                <log:ThirdPartyId>?</log:ThirdPartyId>
                </tem:billingAddress>
                <!--Optional:-->
                <tem:shippingAddress>
                <!--Optional:-->
                <log:BackendUserId>?</log:BackendUserId>
                <!--Optional:-->
                <log:BranchCode>?</log:BranchCode>
                <!--Optional:-->
                <log:City>?</log:City>
                <!--Optional:-->
                <log:Company>?</log:Company>
                <!--Optional:-->
                <log:CountryCode>' . $code . '</log:CountryCode>
                <!--Optional:-->
                <log:DepartmentId>?</log:DepartmentId>
                <!--Optional:-->
                <log:DepartmentName>?</log:DepartmentName>
                <!--Optional:-->
                <log:Enabled>true</log:Enabled>
                <!--Optional:-->
                <log:Fax>?</log:Fax>
                <!--Optional:-->
                <log:FirstName>' . $userInfo->name . '</log:FirstName>
                <!--Optional:-->
                <log:Id>?</log:Id>
                <!--Optional:-->
                <log:LastName>' . $userInfo->name . '</log:LastName>
                <!--Optional:-->
                <log:Line1>?</log:Line1>
                <!--Optional:-->
                <log:Line2>?</log:Line2>
                <!--Optional:-->
                <log:Line3>?</log:Line3>
                <!--Optional:-->
                <log:MiddleInitial>?</log:MiddleInitial>
                <!--Optional:-->
                <log:NickName>' . $userInfo->name . '</log:NickName>
                <!--Optional:-->
                <log:Phone>?</log:Phone>
                <!--Optional:-->
                <log:PostalCode>' . $postalCode . '</log:PostalCode>
                <!--Optional:-->
                <log:RegionCode></log:RegionCode>
                <!--Optional:-->
                <log:RouteCode>?</log:RouteCode>
                <!--Optional:-->
                <log:ThirdPartyId>?</log:ThirdPartyId>
                </tem:shippingAddress>
                <!--Optional:-->
                <tem:userId>' . $userInfo->ApiId . '</tem:userId>
                <!--Optional:-->
                <tem:userEmail>' . $userInfo->email . '</tem:userEmail>
                <!--Optional:-->
                <tem:timeoforder>2023-05-12T00:00:00.000+05:00</tem:timeoforder>
                </tem:CreateNewUnplacedOrder>
                </soapenv:Body>
            </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $addCart,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/CreateNewUnplacedOrder'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return  $response;
            // return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);

        if (isset($responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult']) && !empty($responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'])) {
            DB::table('order_and_product')->insert(['order_id' => $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'], 'UID' => $userInfo->ApiId, 'product_id' => $product_id]);
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
                    <tem:orderId>' . $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'] . '</tem:orderId>
                    <!--Optional:-->
                    <tem:productId>' . $product_id . '</tem:productId>
                    <!--Optional:-->
                    <tem:quantity>' . $quantity . '</tem:quantity>
                    </tem:AddLineItemByProductId>
                    </soapenv:Body>
                </soapenv:Envelope>';

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
            $return = ['order_id' => $responseArray['sBody']['CreateNewUnplacedOrderResponse']['CreateNewUnplacedOrderResult'], 'line_item_id' => $responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult']];
        }
        return  $return;
    }


    public function GetOrderById(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'order_id' => 'required',
        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected', 'order_id');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {
                    $error_message = __($validator->errors()->getMessages()[$field][0]);
                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $GetOrder  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:GetOrderById>
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
                <tem:orderId>' . $request->order_id . '</tem:orderId>
                </tem:GetOrderById>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $GetOrder,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/GetOrderById'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArrays = json_decode($json, true);
        // dd($responseArrays);
        if (isset($responseArrays['sBody']) && !empty($responseArrays['sBody'])) {
            $products = [];

            if (!empty($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult'])) {
                foreach ($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems'] as $key => $aLineItem) {
                    // print_r($aLineItem[0]);
                    // die();
                    if (isset($aLineItem[0])) {
                        foreach ($aLineItem as $key => $product) {
                            // dd('ss');
                            $pData = [
                                'productIds' => $product['aProductId'],
                                "ApiId" => $check->ApiId,
                                "ExpirationDateUtc" => $check->ExpirationDateUtc,
                                "token" => $check->token,
                                "IsExpired" => $check->IsExpired,
                                "TokenRejected" => $check->TokenRejected,
                            ];
                            $product['productDetails'] = $this->getProductDetailsByID($pData);
                            $product['codeDe'] = 'if';
                            $products[] = $product;
                            // $products[] = 'if';
                        }
                    } else {
                        $pData = [
                            'productIds' => $aLineItem['aProductId'],
                            "ApiId" => $check->ApiId,
                            "ExpirationDateUtc" => $check->ExpirationDateUtc,
                            "token" => $check->token,
                            "IsExpired" => $check->IsExpired,
                            "TokenRejected" => $check->TokenRejected,
                        ];
                        $aLineItem['productDetails'] = $this->getProductDetailsByID($pData);
                        $aLineItem['codeDe'] = 'else';
                        $products[] = $aLineItem;
                        // $products[] = 'else';
                    }
                }

                $responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['products'] = $products;
                // unset($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems']);
                return response()->json(['status' => 1, 'message' => 'order detail', 'orderDetail' => $responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']]);
            } else {
                return response()->json(['status' => 0, 'message' => 'order details not found ']);
            }
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }

    public function GetOrderByIdV1(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'order_id' => 'required',
            'email' => 'required',
        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected', 'order_id', 'email');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $GetOrder  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:GetOrderById>
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
                <tem:orderId>' . $request->order_id . '</tem:orderId>
                </tem:GetOrderById>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $GetOrder,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/GetOrderById'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArrays = json_decode($json, true);
        // dd($responseArrays);
        if (isset($responseArrays['sBody']) && !empty($responseArrays['sBody'])) {
            $products = [];
            if (!empty($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult'])) {
                foreach ($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems'] as $key => $aLineItem) {
                    // print_r($aLineItem[0]);
                    // die();
                    if (isset($aLineItem[0])) {
                        foreach ($aLineItem as $key => $product) {
                            // // dd('ss');
                            $pData = [
                                'productIds' => $product['aProductId'],
                                "ApiId" => $request->ApiId,
                                "ExpirationDateUtc" => $request->ExpirationDateUtc,
                                "token" => $check->token,
                                "IsExpired" => $request->IsExpired,
                                "TokenRejected" => $request->TokenRejected,
                            ];

                            $product['productDetails'] = $this->getProductDetailsByID($pData);
                            $product['codeDe'] = 'if';
                            $products[] = $product;
                        }
                    } else {
                        if (!DB::table('removed_product_by_user')->where(['line_Item_Id' => $aLineItem['aId'], 'product_id' =>  $aLineItem['aProductId'], 'user_email' => $request->email, 'quantity' => '0'])->exists()) {
                            // $exist = DB::table('removed_product_by_user')->where(['line_Item_Id' => $aLineItem['aId'], 'product_id' =>  $aLineItem['aProductId'], 'user_email' => $request->email])->first();
                            // if (isset($exist->quantity)) {
                            //     $aLineItem['aQuantity'] = $exist->quantity;
                            // }
                            $pData = [
                                'productIds' => $aLineItem['aProductId'],
                                "ApiId" => $request->ApiId,
                                "ExpirationDateUtc" => $request->ExpirationDateUtc,
                                "token" => $check->token,
                                "IsExpired" => $request->IsExpired,
                                "TokenRejected" => $request->TokenRejected,
                            ];
                            $aLineItem['productDetails'] = $this->getProductDetailsByID($pData);
                            $aLineItem['codeDe'] = 'else';
                            $products[] = $aLineItem;
                        }
                    }
                }

                $responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['products'] = $products;
                unset($responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems']);
                return response()->json(['status' => 1, 'message' => 'order detail', 'orderDetail' => $responseArrays['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']]);
            } else {
                return response()->json(['status' => 0, 'message' => 'order details not found ']);
            }
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }


    public function getProductDetailsByID($data)
    {


        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $findProduct =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
          <soapenv:Header/>
          <soapenv:Body>
             <tem:FindProductsByIds>
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
                <tem:productIds>
                   <!--Zero or more repetitions:-->
                   <arr:string>?</arr:string><arr:string>' . $data['productIds'] . '</arr:string>
                </tem:productIds>
             </tem:FindProductsByIds>
          </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $findProduct,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/ICatalogService/FindProductsByIds'
            ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);



        if (isset($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct']) && !empty($responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'])) {
            return $responseArray['sBody']['FindProductsByIdsResponse']['FindProductsByIdsResult']['aProduct'];
        } else {
            return [];
        }
    }


    public function RemoveLineitem(Request $request)
    {
        $all = $request->all();
        $validator = Validator::make($request->all(), [
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'email' => 'required',
            'product_id' => 'required',
            'line_item_id'  => 'required',
            'quantity'  => 'required',
        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected', 'email', 'product_id', 'line_item_id', 'quantity');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }


        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        $removeItems = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
         <soapenv:Header/>
         <soapenv:Body>
         <tem:RemoveLineItem>
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
         <tem:lineItemId>' . $request->line_item_id . '</tem:lineItemId>
         </tem:RemoveLineItem>
         </soapenv:Body>
         </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $removeItems,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/RemoveLineItem'
            ),
        ));

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);

        if (isset($responseArray['sBody']['RemoveLineItemResponse']['RemoveLineItemResult']) && !empty($responseArray['sBody']['RemoveLineItemResponse']['RemoveLineItemResult'])) {
            if (DB::table('removed_product_by_user')->where(['line_Item_Id' => $request->line_item_id, 'product_id' => $request->product_id, 'user_email' => $request->email])->exists()) {
                DB::table('removed_product_by_user')->where(['line_Item_Id' => $request->line_item_id, 'product_id' => $request->product_id, 'user_email' => $request->email])->update(['quantity' =>  $request->quantity]);
            } else {
                DB::table('removed_product_by_user')->insert(['line_Item_Id' => $request->line_item_id, 'product_id' => $request->product_id, 'user_email' => $request->email, 'quantity' =>  $request->quantity]);
            }
            return response()->json(['status' => 1, 'message' => 'remove items', 'item' => '']);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }


    public function AddLineitem(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $input = $request->all();

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
                <tem:orderId>' . $request->order_id . '</tem:orderId>
                <!--Optional:-->
                <tem:productId>' . $request->product_id . '</tem:productId>
                <!--Optional:-->
                <tem:quantity>' . $request->quantity . '</tem:quantity>
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
            return response()->json(['status' => 1, 'message' => 'add cart', 'order_id' => $request->order_id, 'line_item_id' => $responseArray1['sBody']['AddLineItemByProductIdResponse']['AddLineItemByProductIdResult']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response1], 400);
        }
    }


    public function Inventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'
        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $input = $request->all();

        $checkOrder = DB::table('order_history')->where(['user_id' => $request->ApiId])->get();
        $orderData = [];
        if (!empty($checkOrder)) {
            $orderData = [];
            foreach ($checkOrder as $value) {
                $GetOrder  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                    <soapenv:Header/>
                    <soapenv:Body>
                    <tem:GetOrderById>
                    <!--Optional:-->
                    <tem:token>
                    <log:ApiId>' . $request->ApiId . '</log:ApiId>
                    <!--Optional:-->
                    <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                    <!--Optional:-->
                    <log:Id>' . $check->token . '</log:Id>
                    <!--Optional:-->
                    <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                    <!--Optional:-->
                    <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                    </tem:token>
                    <tem:orderId>' . $value->order_id . '</tem:orderId>
                    </tem:GetOrderById>
                    </soapenv:Body>
                </soapenv:Envelope>';

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $GetOrder,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: text/xml; charset=utf-8',
                        'SOAPAction: http://tempuri.org/IOrdersService/GetOrderById'
                    ),
                ));
                $response = curl_exec($curl);
                if (curl_errno($curl)) {
                    $response = curl_error($curl);
                    return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
                }
                curl_close($curl);
                $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
                $xml = simplexml_load_string($xml);
                $json = json_encode($xml);
                $orderDatas = json_decode($json, true);
                $products = [];

                if (!empty($orderDatas['sBody']['GetOrderByIdResponse']['GetOrderByIdResult'])) {
                    foreach ($orderDatas['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems'] as $key => $aLineItem) {
                        if (isset($aLineItem[0])) {
                            if ($aLineItem)
                                foreach ($aLineItem as $key => $product) {
                                    $pData = [
                                        'productIds' => $product['aProductId'],
                                        "ApiId" => $request->ApiId,
                                        "ExpirationDateUtc" => $request->ExpirationDateUtc,
                                        "token" => $check->token,
                                        "IsExpired" => $request->IsExpired,
                                        "TokenRejected" => $request->TokenRejected,
                                    ];
                                    $product['productDetails'] = $this->getProductDetailsByID($pData);
                                    $products[] = $product;
                                }
                        } else {
                            $pData = [
                                'productIds' => $aLineItem['aProductId'],
                                "ApiId" => $request->ApiId,
                                "ExpirationDateUtc" => $request->ExpirationDateUtc,
                                "token" => $check->token,
                                "IsExpired" => $request->IsExpired,
                                "TokenRejected" => $request->TokenRejected,
                            ];
                            $aLineItem['productDetails'] = $this->getProductDetailsByID($pData);
                            $products[] = $aLineItem;
                        }
                    }
                } else {
                    $products = array();
                }
                if (!empty($products)) {
                    $orderDatas['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['products'] = $products;
                    unset($orderDatas['sBody']['GetOrderByIdResponse']['GetOrderByIdResult']['aLineItems']);
                    $orderData[] =  $orderDatas['sBody']['GetOrderByIdResponse']['GetOrderByIdResult'];
                }
            }
        }
        return response()->json(['status' => 1, 'message' => 'Inventry list', 'Inventry' => $orderData]);
    }




    public function OrderPlaced(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'user_email'  => 'required',

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        //dd($check->token);
        $input = $request->all();


        $orderCod = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:AddCodPayment>
            <!--Optional:-->
            <tem:token>
            <log:ApiId>' . $request->ApiId . '</log:ApiId>
            <!--Optional:-->
            <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
            <!--Optional:-->
            <log:Id>' . $check->token . '</log:Id>
            <!--Optional:-->
            <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
            <!--Optional:-->
            <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
            </tem:token>
            <!--Optional:-->
            <tem:orderId>' . $request->order_id . '</tem:orderId>
            <!--Optional:-->
            <tem:amountCharged>' . $request->amount . '</tem:amountCharged>
            <!--Optional:-->
            <tem:auditDate>' . $request->today_date . '</tem:auditDate>
            </tem:AddCodPayment>
            </soapenv:Body>
            </soapenv:Envelope>';

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
            CURLOPT_POSTFIELDS => $orderCod,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/AddCodPayment'
            ),
        ));

        $response1 = curl_exec($curl1);


        if (curl_errno($curl1)) {
            $response1 = curl_error($curl1);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response1], 400);
        }
        curl_close($curl1);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response1);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);



        $orderPlaced  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:PlaceOrder>
            <!--Optional:-->
            <tem:token>
            <log:ApiId>' . $request->ApiId . '</log:ApiId>
            <!--Optional:-->
            <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
            <!--Optional:-->
            <log:Id>' . $check->token . '</log:Id>
            <!--Optional:-->
            <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
            <!--Optional:-->
            <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
            </tem:token>
            <!--Optional:-->
            <tem:orderId>' . $request->order_id . '</tem:orderId>
            <!--Optional:-->
            <tem:executeWorkflows>false</tem:executeWorkflows>
            </tem:PlaceOrder>
            </soapenv:Body>
            </soapenv:Envelope>';



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $orderPlaced,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/PlaceOrder'
            ),
        ));

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);


        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);

        $orderSave  = [
            'order_id' => @$request->order_id,
            'product_id' => @$request->product_id,
            'user_id' => @$request->ApiId,
            'quantity' => @$request->quantity,
            'created_at'  => Carbon::now(),
        ];
        DB::table('order_by_user')->insert($orderSave);

        if (isset($responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult']) && !empty($responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult'])) {
            DB::table('order_and_product')->where(['order_id' => $request->order_id, 'UID' => $request->ApiId])->update(['status' => 'order']);
            DB::table('cart')->where(['product_id' => $request->product_id, 'user_email' => $request->user_email])->delete();
            $checkOrder = DB::table('order_history')->where(['order_id' => $request->order_id, 'user_id' => $request->ApiId])->first();
            if (empty($checkOrder)) {
                DB::table('order_history')->insert(['order_id' => $request->order_id, 'user_id' => $request->ApiId]);
            }
            return response()->json(['status' => 1, 'message' => 'Order placed sucessfully', 'data' => $responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response1], 400);
        }
    }


    public function OrderPlaceV1($request, $order_id, $product_id, $quantity, $amount)
    {

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        //dd($check->token);
        $today_date = Carbon::now()->format('Y-m-d\TH:i:s.uP');
        $input = $request->all();
        $orderCod = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:AddCodPayment>
            <!--Optional:-->
            <tem:token>
            <log:ApiId>' . $request->ApiId . '</log:ApiId>
            <!--Optional:-->
            <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
            <!--Optional:-->
            <log:Id>' . $check->token . '</log:Id>
            <!--Optional:-->
            <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
            <!--Optional:-->
            <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
            </tem:token>
            <!--Optional:-->
            <tem:orderId>' . $order_id . '</tem:orderId>
            <!--Optional:-->
            <tem:amountCharged>' . $amount . '</tem:amountCharged>
            <!--Optional:-->
            <tem:auditDate>' . $today_date . '</tem:auditDate>
            </tem:AddCodPayment>
            </soapenv:Body>
            </soapenv:Envelope>';

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
            CURLOPT_POSTFIELDS => $orderCod,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/AddCodPayment'
            ),
        ));

        $response1 = curl_exec($curl1);


        if (curl_errno($curl1)) {
            $response1 = curl_error($curl1);

            return ['status' => 0, 'message' => 'crul error', 'response' => $response1];
        }
        curl_close($curl1);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response1);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);



        $orderPlaced  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
            <tem:PlaceOrder>
            <!--Optional:-->
            <tem:token>
            <log:ApiId>' . $request->ApiId . '</log:ApiId>
            <!--Optional:-->
            <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
            <!--Optional:-->
            <log:Id>' . $check->token . '</log:Id>
            <!--Optional:-->
            <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
            <!--Optional:-->
            <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
            </tem:token>
            <!--Optional:-->
            <tem:orderId>' . $order_id . '</tem:orderId>
            <!--Optional:-->
            <tem:executeWorkflows>false</tem:executeWorkflows>
            </tem:PlaceOrder>
            </soapenv:Body>
            </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $orderPlaced,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/PlaceOrder'
            ),
        ));

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return ['status' => 0, 'message' => 'crul error', 'response' => $response];
        }
        curl_close($curl);


        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        // dd($responseArray);
        $orderSave  = [
            'order_id' => $order_id,
            'product_id' => $product_id,
            'user_id' => @$request->ApiId,
            'quantity' => $quantity,
            'created_at'  => Carbon::now(),
        ];
        DB::table('order_by_user')->insert($orderSave);
        if (isset($responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult']) && !empty($responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult'])) {
            DB::table('order_and_product')->where(['order_id' => $order_id, 'UID' => $request->ApiId])->update(['status' => 'order']);
            DB::table('cart')->where(['user_email' => $request->email])->delete();
            $checkOrder = DB::table('order_history')->where(['order_id' => $order_id, 'user_id' => $request->ApiId])->first();
            if (empty($checkOrder)) {
                DB::table('order_history')->insert(['order_id' => $order_id, 'user_id' => $request->ApiId]);
            }
            return ['status' => 1, 'message' => 'Order placed sucessfully', 'data' => $responseArray['sBody']['PlaceOrderResponse']['PlaceOrderResult']];
        } else {
            return ['status' => 0, 'message' => 'something went wrong', 'response' => $response1];
        }
    }

    public function RemoveOrder(Request $request)
    {
        $all = $request->all();
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }


        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        $removeItems = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
         <soapenv:Header/>
         <soapenv:Body>
         <tem:RemoveOrder>
         <!--Optional:-->
         <tem:token>
         <log:ApiId>' . $request->ApiId . '</log:ApiId>
         <!--Optional:-->
         <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
         <!--Optional:-->
         <log:Id>' . $check->token . '</log:Id>
         <!--Optional:-->
         <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
         <!--Optional:-->
         <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
         </tem:token>
         <!--Optional:-->
         <tem:orderId>' . $request->order_id . '</tem:orderId>
         </tem:RemoveOrder>
         </soapenv:Body>
         </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gobestbundles.com/API/OrdersService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $removeItems,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IOrdersService/RemoveOrder'
            ),
        ));

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);



        if (isset($responseArray['sBody']['RemoveOrderResponse']['RemoveOrderResult']) && !empty($responseArray['sBody']['RemoveOrderResponse']['RemoveOrderResult'])) {

            DB::table('order_and_product')->where(['order_id' => $request->order_id])->delete();
            DB::table('order_history')->where(['order_id' => $request->order_id])->delete();

            return response()->json(['status' => 1, 'message' => 'Order Removed', 'item' => $responseArray['sBody']['RemoveOrderResponse']['RemoveOrderResult']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }


    public function UpdateLineItem(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            // 'productIds' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required'

        ]);
        $fields = array('ApiId', 'ExpirationDateUtc', 'IsExpired', 'TokenRejected');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $input = $request->all();

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
         <soapenv:Header/>
         <soapenv:Body>
            <tem:UpdateLineItem>
                <!--Optional:-->
                <tem:token>
                    <log:ApiId>' . $request->ApiId . '</log:ApiId>
                    <!--Optional:-->
                    <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                    <!--Optional:-->
                    <log:Id>' . $check->token . '</log:Id>
                    <!--Optional:-->
                    <log:IsExpired>' . $request->IsExpired . '</log:IsExpired>
                    <!--Optional:-->
                    <log:TokenRejected>' . $request->TokenRejected . '</log:TokenRejected>
                </tem:token>
                <!--Optional:-->
                <tem:lineItem>
                <log:Id>' . $request->line_item_id . '</log:Id>
                <log:OrderId>' . $request->order_id . '</log:OrderId>
                <!--Optional:-->
                <log:ProductId>' . $request->product_id . '</log:ProductId>
                <log:Quantity>' . $request->quantity . '</log:Quantity>
                <log:VendorId></log:VendorId>
                </tem:lineItem>
            </tem:UpdateLineItem>
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
                'SOAPAction: http://tempuri.org/IOrdersService/UpdateLineItem'
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


        if (isset($responseArray1['sBody']['UpdateLineItemResponse']['UpdateLineItemResult']) && !empty($responseArray1['sBody']['UpdateLineItemResponse']['UpdateLineItemResult'])) {

            return response()->json(['status' => 1, 'message' => 'Quantity Updated', 'response' => $responseArray1['sBody']['UpdateLineItemResponse']['UpdateLineItemResult']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response1], 400);
        }
    }


    public function getPurchaseHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'UID' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' =>  $error, 'response' => []], 400);
        }
        // $OrderHistoryProduct =   DB::table('order_by_user')->where(['user_id' => $request->UID])->groupBy('order_id')->get();

        $OrderHistoryProduct = DB::table('order_by_user')
            ->select('order_id', 'user_id', 'product_id')
            ->where(['user_id' => $request->UID])
            ->groupBy('order_id', 'user_id', 'product_id')
            ->get();
        // $OrderHistory =   DB::table('order_history')->where(['user_id' => $request->UID])->get();
        $userInfo = User::where('UID',  $request->UID)->first();
        foreach ($OrderHistoryProduct as $key => $value) {
            // $productInfoApi =  $this->productInfo($value->product_id, $userInfo);
            // dd($productInfoApi);
            $orderInfoApi = $this->orderInfo($value->order_id);
            $orderInfoOBJ = new \stdClass();
            $orderInfoOBJ = $orderInfoApi;
            $value->orderInfo = $orderInfoOBJ;
        }

        // dd();
        return response()->json(['status' => 1, 'message' => 'Purchase History Fetched', 'response' => $OrderHistoryProduct]);
    }




    public function addToCart(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'product_name' => 'required',
            'product_image' => 'required',
            'product_detail' => 'required',
            'product_quantity' => 'required',
            'product_price' => 'required',
            'user_email' => 'required'
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $exist = DB::table('cart')->where(['product_id' => $request->product_id,  'user_email' => $request->user_email])->first();
        if (isset($exist->product_quantity)) {
            DB::table('cart')->where('id', $exist->id)->update(['product_quantity' => $request->product_quantity]);
            $response = '0';
        } else {
            DB::table('cart')->insert($request->all());
            $response = '1';
        }

        return response()->json(['status' => 1, 'message' => 'item added into cart', 'response' => $response]);
    }

    public function removefromCartv1(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',

        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $exist = Cart::where(['id' => $request->id])->delete();

        return response()->json(['status' => 1, 'message' => 'item removed from cart successfully', 'response' => $exist]);
    }




    public function cartList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required'
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $response = DB::table('cart')->where(['user_email' => $request->user_email])->get();
        return response()->json(['status' => 1, 'message' => 'cart data fetched', 'response' => $response]);
    }

    public function addToFavourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $store = [
            'product_id' => $request->product_id,
            'user_id' => $request->user_id
        ];

        if (!FavouriteProduct::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->exists()) {
            $FavouriteProduct = new FavouriteProduct;
            $FavouriteProduct->fill($store);
            $FavouriteProduct->save();
        }

        return response()->json(['status' => 1, 'message' => 'Added To Favourite', 'response' => $request->all()]);
    }

    public function listFavourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $response = FavouriteProduct::where(['user_id' => $request->user_id])->orderby('created_at', 'desc')->get();
        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);
        $requiredInfo = [
            "ApiId" => $check->ApiId,
            "ExpirationDateUtc" => $check->ExpirationDateUtc,
            "token" => $check->token,
            "IsExpired" => $check->IsExpired,
            "TokenRejected" => $check->TokenRejected,
        ];
        foreach ($response as $key => $value) {
            $requiredInfo['productIds'] = $value->product_id;
            $value['productInfo'] =  $this->getProductDetailsByID($requiredInfo);
        }
        return response()->json(['status' => 1, 'message' => 'listFavourite fetched', 'response' => $response]);
    }

    public function removeFavourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $response = FavouriteProduct::where(['id' => $request->id])->delete();
        return response()->json(['status' => 1, 'message' => 'Favourite Product Removed', 'response' => $response]);
    }



    public function FindProductsBySkus(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            // 'skus' => 'required|array',
            'skus' => 'string'
        ]);

        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }

        $tokenData = User::first();
        $refreshtoken = new RegisterController();
        $check = $refreshtoken->refreshToken($tokenData);

        $skusXml = '<arr:string>' . $request->skus . '</arr:string>';
        $soapRequestBody = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:FindProductsBySkus>
                    <tem:token>
                        <log:ApiId>' . $check->ApiId . '</log:ApiId>
                        <log:ExpirationDateUtc>' . $check->ExpirationDateUtc . '</log:ExpirationDateUtc>
                        <log:Id>' . $check->token . '</log:Id>
                        <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
                        <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
                    </tem:token>
                    <tem:skus>' . $skusXml . '</tem:skus>
                </tem:FindProductsBySkus>
            </soapenv:Body>
        </soapenv:Envelope>';

        // CURL setup and execution
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.gobestbundles.com/api/CatalogService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $soapRequestBody,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/ICatalogService/FindProductsBySkus'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $response = curl_error($curl);
            return response()->json(['status' => 0, 'message' => 'curl error', 'response' => $response], 400);
        }

        curl_close($curl);

        // Handle XML response
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);

        // dd($responseArray);
        if (isset($responseArray['sBody']['FindProductsBySkusResponse']['FindProductsBySkusResult']['aProduct']) && !empty($responseArray['sBody']['FindProductsBySkusResponse']['FindProductsBySkusResult']['aProduct'])) {
            return response()->json(['status' => 1, 'message' => 'product details', 'Products' => $responseArray['sBody']['FindProductsBySkusResponse']['FindProductsBySkusResult']['aProduct']]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
        }
    }
}
