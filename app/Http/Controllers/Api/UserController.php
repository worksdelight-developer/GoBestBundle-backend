<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{


    public function AddAddress(Request $request)
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
        //$input = $request->all();

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                 <soapenv:Header/>
                 <soapenv:Body>
                 <tem:AddAddress>
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
                <tem:userId>' . $request->userId . '</tem:userId>
                <!--Optional:-->
                <tem:address>
                    <!--Optional:-->
                    <log:City>' . $request->City . '</log:City>
                    <log:CountryCode>' . $request->CountryCode . '</log:CountryCode>
                    <log:Line1>' . $request->Line1 . '</log:Line1>
                    <log:Line2>' . $request->Line2 . '</log:Line2>
                </tem:address>
                </tem:AddAddress>
                </soapenv:Body>
                </soapenv:Envelope>';

        //  dd($addItem);

        $curl1 = curl_init();

        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://www.bundlesadvantage.com/API/UserAccountService.svc',
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
                'SOAPAction: http://tempuri.org/IUserAccountService/AddAddress'
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

        if (isset($responseArray1['sBody']['AddAddressResponse']['AddAddressResult']) && $responseArray1['sBody']['AddAddressResponse']['AddAddressResult'] == true) {
            return response()->json(['status' => 1, 'message' => 'Address Added Successfully']);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response1], 400);
        }
    }


    public function UpdateAddress(Request $request)
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
        //$input = $request->all();

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
         <soapenv:Header/>
         <soapenv:Body>
         <tem:UpdateAddress>
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
            <!--Optional:-->
            <tem:userId>' . $request->userId . '</tem:userId>
            <tem:address>
            <!--Optional:-->
            <log:City>' . $request->City . '</log:City>
            <!--Optional:-->
            <log:CountryCode>' . $request->CountryCode . '</log:CountryCode>
            <!--Optional:-->
            <log:Line1>' . $request->Line1 . '</log:Line1>
            <!--Optional:-->
            <log:Line2>' . $request->Line2 . '</log:Line2>
            </tem:address>
        </tem:UpdateAddress>
        </soapenv:Body>
        </soapenv:Envelope>';

        // dd($addItem);

        $curl1 = curl_init();

        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://www.bundlesadvantage.com/API/UserAccountService.svc',
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
                'SOAPAction: http://tempuri.org/IUserAccountService/UpdateAddress'
            ),
        ));

        $response1 = curl_exec($curl1);

        // echo "<pre>";
        // print_r($response1);
        // die;

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


    public function GetAddress(Request $request)
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
        //$input = $request->all();

        $addItem = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                 <soapenv:Header/>
                 <soapenv:Body>
                <tem:GetAddresses>
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
                <tem:userId>' . $request->userId . '</tem:userId>
                </tem:GetAddresses>
                </soapenv:Body>
                </soapenv:Envelope>';

        // dd($addItem);

        $curl1 = curl_init();

        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://www.bundlesadvantage.com/API/UserAccountService.svc',
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
                'SOAPAction: http://tempuri.org/IUserAccountService/GetAddresses'
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

        // echo "<pre>";
        // print_r($responseArray1);
        // die;

        if (isset($responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']) && !empty($responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult'])) {

            $address_array = array();
            if (array_key_exists("aCity", $responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress'])) {
                $data = array(
                    "aCity" => $responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress']['aCity'],
                    "aCountryCode" => $responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress']['aCountryCode'],
                    "aLine1" => $responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress']['aLine1'],
                    "aLine2" => $responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress']['aLine2'],
                );
                $address_array[] = $data;
            } else {

                foreach ($responseArray1['sBody']['GetAddressesResponse']['GetAddressesResult']['aAddress'] as $address) {
                    $data = array(
                        "aCity" => $address['aCity'],
                        "aCountryCode" => $address['aCountryCode'],
                        "aLine1" => $address['aLine1'],
                        "aLine2" => $address['aLine2'],
                    );
                    $address_array[] = $data;
                }
            }

            return response()->json(['status' => 1, 'message' => 'Get Addresses', 'data' => $address_array]);
        } else {
            return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response1], 400);
        }
    }
}
