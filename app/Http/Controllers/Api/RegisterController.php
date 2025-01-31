<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class RegisterController extends Controller
{
    public function CreateUserAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
         //   'userName' => 'required|unique:users,name',
            'firstName' => 'required',
            'lastName' => 'required',
            'company' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            //'notification_token' => 'required',
            // 'ApiId' => 'required',
            //'ExpirationDateUtc' => 'required',
            //'token' => 'required',
            // 'IsExpired' => 'required',
            //'TokenRejected' => 'required',


        ]);
         if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }


        try {

            $userStaus =  User::where('email', $request->email)->first();

            if (isset($userStaus->status) && $userStaus->status == 'inActive') {
                return response()->json(['status' => 0, 'message' => 'Your account has been deactivated. Please contact support ']);
            }
            $tokenData = User::first();

            $check = $this->refreshToken($tokenData);

            // dd($check->ApiId);

            $soapEnvelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:CreateUserAccount>
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
                    <tem:userName>' . $request->email . '</tem:userName>
                    <!--Optional:-->
                    <tem:email>' . $request->email . '</tem:email>
                    <!--Optional:-->
                    <tem:firstName>' . $request->firstName . '</tem:firstName>
                    <!--Optional:-->
                    <tem:lastName>' . $request->lastName . '</tem:lastName>
                    <!--Optional:-->
                    <tem:company>' . $request->company . '</tem:company>
                    <!--Optional:-->
                    <tem:password>' . $request->password . '</tem:password>
                    <!--Optional:-->
                    <tem:billingAddress>
                        <!--Optional:-->
                        <log:BackendUserId></log:BackendUserId>
                        <!--Optional:-->
                        <log:BranchCode></log:BranchCode>
                        <!--Optional:-->
                        <log:City></log:City>
                        <!--Optional:-->
                        <log:Company></log:Company>
                        <!--Optional:-->
                        <log:CountryCode>?</log:CountryCode>
                        <!--Optional:-->
                        <log:DepartmentId></log:DepartmentId>
                        <!--Optional:-->
                        <log:DepartmentName></log:DepartmentName>
                        <!--Optional:-->
                        <log:Enabled>false</log:Enabled>
                        <!--Optional:-->
                        <log:Fax></log:Fax>
                        <!--Optional:-->
                        <log:FirstName></log:FirstName>
                        <!--Optional:-->
                        <log:Id></log:Id>
                        <!--Optional:-->
                        <log:LastName></log:LastName>
                        <!--Optional:-->
                        <log:Line1></log:Line1>
                        <!--Optional:-->
                        <log:Line2></log:Line2>
                        <!--Optional:-->
                        <log:Line3></log:Line3>
                        <!--Optional:-->
                        <log:MiddleInitial></log:MiddleInitial>
                        <!--Optional:-->
                        <log:NickName></log:NickName>
                        <!--Optional:-->
                        <log:Phone></log:Phone>
                        <!--Optional:-->
                        <log:PostalCode></log:PostalCode>
                        <!--Optional:-->
                        <log:RegionCode></log:RegionCode>
                        <!--Optional:-->
                        <log:RouteCode></log:RouteCode>
                        <!--Optional:-->
                        <log:ThirdPartyId></log:ThirdPartyId>
                    </tem:billingAddress>
                    <!--Optional:-->
                    <tem:shippingAddress>
                        <!--Optional:-->
                        <log:BackendUserId></log:BackendUserId>
                        <!--Optional:-->
                        <log:BranchCode></log:BranchCode>
                        <!--Optional:-->
                        <log:City></log:City>
                        <!--Optional:-->
                        <log:Company></log:Company>
                        <!--Optional:-->
                        <log:CountryCode></log:CountryCode>
                        <!--Optional:-->
                        <log:DepartmentId></log:DepartmentId>
                        <!--Optional:-->
                        <log:DepartmentName></log:DepartmentName>
                        <!--Optional:-->
                        <log:Enabled>false</log:Enabled>
                        <!--Optional:-->
                        <log:Fax></log:Fax>
                        <!--Optional:-->
                        <log:FirstName></log:FirstName>
                        <!--Optional:-->
                        <log:Id></log:Id>
                        <!--Optional:-->
                        <log:LastName></log:LastName>
                        <!--Optional:-->
                        <log:Line1></log:Line1>
                        <!--Optional:-->
                        <log:Line2></log:Line2>
                        <!--Optional:-->
                        <log:Line3></log:Line3>
                        <!--Optional:-->
                        <log:MiddleInitial></log:MiddleInitial>
                        <!--Optional:-->
                        <log:NickName></log:NickName>
                        <!--Optional:-->
                        <log:Phone></log:Phone>
                        <!--Optional:-->
                        <log:PostalCode></log:PostalCode>
                        <!--Optional:-->
                        <log:RegionCode></log:RegionCode>
                        <!--Optional:-->
                        <log:RouteCode></log:RouteCode>
                        <!--Optional:-->
                        <log:ThirdPartyId></log:ThirdPartyId>
                    </tem:shippingAddress>
                    <!--Optional:-->
                    <tem:accountNumber></tem:accountNumber>
                    <!--Optional:-->
                    <tem:passwordFormat>ClearText</tem:passwordFormat>
                    <!--Optional:-->
                    <tem:taxExempt>false</tem:taxExempt>
                    <!--Optional:-->
                    <tem:shippingExempt>false</tem:shippingExempt>
                    <!--Optional:-->
                    <tem:customQuestionAnswers></tem:customQuestionAnswers>
                    <!--Optional:-->
                    <tem:comment>?</tem:comment>
                </tem:CreateUserAccount>
            </soapenv:Body>
            </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $soapEnvelope,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/IUserAccountService/CreateUserAccount'
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
            $responseArray = json_decode($json, true); // true to have an array, false for an object

           // dd($responseArray);
            if (isset($responseArray['sBody']['CreateUserAccountResponse']['CreateUserAccountResult']) && !empty($responseArray['sBody']['CreateUserAccountResponse']['CreateUserAccountResult'])) {
                $user_id   = $responseArray['sBody']['CreateUserAccountResponse']['CreateUserAccountResult'];
                $checkuser  = User::where('name', $request->email)->first();
                if (!empty($checkuser)) {
                    User::where('name', $request->email)->delete();
                }
                $store = [
                    'name' => $request->firstName,
                    'email' => $request->email,
                    'UId' => $user_id,
                    'ApiId' => $user_id,
                    'IsExpired' => $check->IsExpired,
                    'token' => $check->token,
                    'ExpirationDateUtc' => $check->ExpirationDateUtc,
                    'TokenRejected' => $check->TokenRejected,
                    'password' => $request->password,
                    'notification_token' => @$request->notification_token
                ];
                $userInfo = new User;
                $userInfo->fill($store);
                $userInfo->save();

                return response()->json(['status' => 1, 'message' => 'user account created sucessfully', 'user_id' => $user_id, 'response' => $userInfo, 'check' => '1']);
            } else {
                $user_id   = "";
            }
            if (empty($user_id)) {
                $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
                $xml = simplexml_load_string($xml);
                $json = json_encode($xml);
                $responseArray = json_decode($json, true);
                if (isset($responseArray['sBody']['sFault']['faultstring']) && !empty($responseArray['sBody']['sFault']['faultstring'])) {

                    $response = $responseArray['sBody']['sFault']['faultstring'];
                } else {

                    $response = $response;
                }

                return response()->json(['status' => 0, 'message' => 'something went wrong', 'user_id' => $user_id, 'response' => $response], 400);
            }

            return response()->json(['status' => 1, 'message' => 'user account created sucessfully', 'user_id' => $user_id, 'response' => $response, 'check' => '2']);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }



    public function GetUserAccountById(Request $request)
    {
        try {
            $tokenData = User::first();
            $check = $this->refreshToken($tokenData);
            $soapEnvelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
               <tem:GetUserAccountById>
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
                  <tem:userId>' . $request->user_id . '</tem:userId>
               </tem:GetUserAccountById>
            </soapenv:Body>
         </soapenv:Envelope>';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $soapEnvelope,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/IUserAccountService/GetUserAccountById'
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
            $responseArray = json_decode($json, true); // true to have an array, false for an object
            $UserDetail = [];
            if (isset($responseArray['sBody']['sFault']['faultstring']) && !empty($responseArray['sBody']['sFault']['faultstring'])) {
                $UserDetail = [];
                // return response()->json(['status' => 0, 'message' => $responseArray['sBody']['sFault']['faultstring'], 'response' => $response], 400);
            }

            if (isset($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult']) && !empty($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'])) {

                $UserDetail = $responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'];
            }
            return $UserDetail;
            // return response()->json(['status' => 1, 'message' => 'user detail', 'userDetail' => $UserDetail, 'response' => $response]);
        } catch (\Exception $e) {
            return [];
        }
    }






    public function refreshToken($data)
    {
        $refreshToken =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
        <soapenv:Header/>
        <soapenv:Body>
            <tem:IsTokenValid>
                <!--Optional:-->
                <tem:token>
                    <!--Optional:-->
                    <log:ApiId>' . $data->ApiId . '</log:ApiId>
                     <!--Optional:-->
                     <log:ExpirationDateUtc>' . $data->ExpirationDateUtc . '</log:ExpirationDateUtc>
                     <!--Optional:-->
                     <log:Id>' . $data->token . '</log:Id>
                     <!--Optional:-->
                     <log:IsExpired>' . $data->IsExpired . '</log:IsExpired>
                     <!--Optional:-->
                     <log:TokenRejected>' . $data->TokenRejected . '</log:TokenRejected>
                </tem:token>
            </tem:IsTokenValid>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $refreshToken,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IAuthenticationService/IsTokenValid',

            ),
            
        ));

        $response = curl_exec($curl);
     //    dd('here', $response);
        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
	//	dd($xml);
        $xml = simplexml_load_string($xml);

	
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        if (isset($responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult']) && !empty($responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult'])) {

            $response = $responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult'];

            if ($response == "true") {

                $tokenData = User::first();
            } else {

                $login  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                <soapenv:Header/>
                <soapenv:Body>
                   <tem:Login>
                      <!--Optional:-->
                      <tem:username>webservice</tem:username>
                      <!--Optional:-->
                      <tem:password>au2B1O9Evr1L)</tem:password>
                   </tem:Login>
                </soapenv:Body>
                </soapenv:Envelope>';

                $curl1 = curl_init();

                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $login,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: text/xml; charset=utf-8',
                        'SOAPAction: http://tempuri.org/IAuthenticationService/Login'
                    ),
                ));

                $response = curl_exec($curl1);
                if (curl_errno($curl1)) {
                    $response = curl_error($curl1);

                    return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
                }
                curl_close($curl1);

                $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
                $xml = simplexml_load_string($xml);
                $json = json_encode($xml);
                $responseArray = json_decode($json, true);

                if (isset($responseArray['sBody']['LoginResponse']['LoginResult']) && !empty($responseArray['sBody']['LoginResponse']['LoginResult'])) {

                    $response = $responseArray['sBody']['LoginResponse']['LoginResult'];
                    $tokenData = User::first();
                    $tokenData->ApiId = $responseArray['sBody']['LoginResponse']['LoginResult']['aApiId'];
                    $tokenData->token = $responseArray['sBody']['LoginResponse']['LoginResult']['aId'];
                    $tokenData->ExpirationDateUtc = $responseArray['sBody']['LoginResponse']['LoginResult']['aExpirationDateUtc'];
                    $tokenData->IsExpired = $responseArray['sBody']['LoginResponse']['LoginResult']['aIsExpired'];
                    $tokenData->TokenRejected = $responseArray['sBody']['LoginResponse']['LoginResult']['aTokenRejected'];
                    $tokenData->update();
                }
            }

            return $tokenData;
        }
    }


    public function refreshTokenV1($data)
    {
        $refreshToken =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
        <soapenv:Header/>
        <soapenv:Body>
            <tem:IsTokenValid>
                <!--Optional:-->
                <tem:token>
                    <!--Optional:-->
                    <log:ApiId>' . $data->ApiId . '</log:ApiId>
                     <!--Optional:-->
                     <log:ExpirationDateUtc>' . $data->ExpirationDateUtc . '</log:ExpirationDateUtc>
                     <!--Optional:-->
                     <log:Id>' . $data->token . '</log:Id>
                     <!--Optional:-->
                     <log:IsExpired>' . $data->IsExpired . '</log:IsExpired>
                     <!--Optional:-->
                     <log:TokenRejected>' . $data->TokenRejected . '</log:TokenRejected>
                </tem:token>
            </tem:IsTokenValid>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $refreshToken,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IAuthenticationService/IsTokenValid'
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
        // dd($responseArray);
        if (isset($responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult']) && !empty($responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult'])) {
            $response = $responseArray['sBody']['IsTokenValidResponse']['IsTokenValidResult'];
            if ($response == "true") {
                $tokenData = User::where('ApiId', $data->ApiId)->first();
            } else {

                $login  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                <soapenv:Header/>
                <soapenv:Body>
                   <tem:Login>
                      <!--Optional:-->
                      <tem:username>webservice</tem:username>
                      <!--Optional:-->
                      <tem:password>au2B1O9Evr1L)</tem:password>
                   </tem:Login>
                </soapenv:Body>
                </soapenv:Envelope>';

                $curl1 = curl_init();

                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $login,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: text/xml; charset=utf-8',
                        'SOAPAction: http://tempuri.org/IAuthenticationService/Login'
                    ),
                ));

                $response = curl_exec($curl1);
                if (curl_errno($curl1)) {
                    $response = curl_error($curl1);

                    return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
                }
                curl_close($curl1);

                $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
                $xml = simplexml_load_string($xml);
                $json = json_encode($xml);
                $responseArray = json_decode($json, true);

                if (isset($responseArray['sBody']['LoginResponse']['LoginResult']) && !empty($responseArray['sBody']['LoginResponse']['LoginResult'])) {

                    $response = $responseArray['sBody']['LoginResponse']['LoginResult'];
                    $tokenData = User::where('ApiId',  $data->ApiId)->first();
                    // $tokenData->ApiId = $responseArray['sBody']['LoginResponse']['LoginResult']['aApiId'];
                    $tokenData->token = $responseArray['sBody']['LoginResponse']['LoginResult']['aId'];
                    $tokenData->ExpirationDateUtc = $responseArray['sBody']['LoginResponse']['LoginResult']['aExpirationDateUtc'];
                    $tokenData->IsExpired = $responseArray['sBody']['LoginResponse']['LoginResult']['aIsExpired'];
                    $tokenData->TokenRejected = $responseArray['sBody']['LoginResponse']['LoginResult']['aTokenRejected'];
                    $tokenData->update();
                }
            }

            return $tokenData;
        }
    }

    public function login(Request $request)
    {

        $tokenData = User::first();
        $refreshtoken   =   new RegisterController();
        // $check = $refreshtoken->refreshToken($tokenData);
        // dd($check);
        $validator = Validator::make($request->all(), [
            'userName' => 'required',
            'password' => 'required',
            'notification_token' => 'required'
        ]);
        $fields = array('userName', 'password');
        $error_message = "";
        if ($validator->fails()) {
            foreach ($fields as $field) {
                if (isset($validator->errors()->getMessages()[$field][0]) && !empty($validator->errors()->getMessages()[$field][0]) && empty($error_message)) {

                    $error_message = __($validator->errors()->getMessages()[$field][0]);

                    return response()->json(['status' => 0, 'message' => $error_message]);
                }
            }
        }

        $userStaus =  User::where('email', $request->userName)->first();
        if (isset($userStaus->status) && $userStaus->status == 'inActive') {
            return response()->json(['status' => 0, 'message' => 'Your account has been deactivated. Please contact support ']);
        }
        // $tokenData1 = User::where('name', $request->userName)->where('password', $request->password)->first();

        // if (empty($tokenData1)) {
        $login  = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
        <soapenv:Body>
           <tem:Login>
              <!--Optional:-->
              <tem:username>' . $request->userName . '</tem:username>
              <!--Optional:-->
              <tem:password>' . $request->password . '</tem:password>
           </tem:Login>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl1 = curl_init();

        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $login,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IAuthenticationService/Login'
            ),
        ));

        $response = curl_exec($curl1);
        if (curl_errno($curl1)) {
            $response = curl_error($curl1);

            return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
        }
        curl_close($curl1);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);

      //  dd($xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);

      //  dd($responseArray);

        // $tokenData1 = User::where('name', $request->userName)
        //     ->orWhere('email', $userInfoFrom['aEmail'])
        //     ->first();

        if (isset($responseArray['sBody']['LoginResponse']['LoginResult']) && !empty($responseArray['sBody']['LoginResponse']['LoginResult'])) {
            if (empty($responseArray['sBody']['LoginResponse']['LoginResult']['aApiId'])) {

                //dd('jhh');
                return response()->json(['status' => 0, 'message' => 'Incorrect email or password'], 400);
                // return response()->json(['status' => 0, 'message' => 'There is some problem with your account plz contact super admin'], 400);
            }
            $tokenData = new \stdClass();

            $userInfoFrom = $this->GetUserInfoByaApiId($responseArray['sBody']['LoginResponse']['LoginResult']['aApiId']);

            $saveUserInfo =  [
                'name' => $userInfoFrom['aFirstName'],
                'email' => $userInfoFrom['aEmail'],
                'UId' => $userInfoFrom['aId'],
                'ApiId' => $responseArray['sBody']['LoginResponse']['LoginResult']['aApiId'],
                'IsExpired' => $responseArray['sBody']['LoginResponse']['LoginResult']['aIsExpired'],
                'token' => $responseArray['sBody']['LoginResponse']['LoginResult']['aId'],
                'ExpirationDateUtc' =>  $responseArray['sBody']['LoginResponse']['LoginResult']['aExpirationDateUtc'],
                'TokenRejected' => $responseArray['sBody']['LoginResponse']['LoginResult']['aTokenRejected'],
                'password' => $request->password,
                'notification_token' => @$request->notification_token,
            ];
            $tokenData1 = User::where('name', $request->userName)
                ->orWhere('email', $userInfoFrom['aEmail'])
                ->first();
            $User = new User;
            if (isset($tokenData1->name)) {
                $User = $tokenData1;
            }
            $User->fill($saveUserInfo);
            $User->save();
            // dd($User);
            // dd($responseArray, $saveUserInfo, $userInfoFrom);
            $tokenData->ApiId = $responseArray['sBody']['LoginResponse']['LoginResult']['aApiId'];
            $tokenData->token = $responseArray['sBody']['LoginResponse']['LoginResult']['aId'];
            $tokenData->ExpirationDateUtc = $responseArray['sBody']['LoginResponse']['LoginResult']['aExpirationDateUtc'];
            $tokenData->IsExpired = $responseArray['sBody']['LoginResponse']['LoginResult']['aIsExpired'];
            $tokenData->TokenRejected = $responseArray['sBody']['LoginResponse']['LoginResult']['aTokenRejected'];
            $tokenData->user = ['id' => $userInfoFrom['aId'], 'name' => $User->name, 'email' => $User->email];
            $tokenData->order_id = DB::table('order_and_product')->where('UID', $User->UID)->first();
            $request['user_id'] =  $tokenData->ApiId;
            $userInfo = $this->GetUserAccountById($request);
            $tokenData->aAccountRole = @$userInfo['aAccountRole'];
            return response()->json(['status' => 1, 'message' => 'user login sucessfully', 'data' => $tokenData, 'response' => $response]);
            // }
        } else {

            return response()->json(['status' => 0, 'message' => 'Incorrect email or password'], 400);
        }
    }

    public function SetUserAccountPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            //'token' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'password' => 'required'
        ]);
        $fields = array('user_id', 'ApiId', 'ExpirationDateUtc', 'token', 'IsExpired', 'TokenRejected', 'password');
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
            $check = $this->refreshToken($tokenData);

            $forgetPassword = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
                <soapenv:Header/>
                <soapenv:Body>
                    <tem:SetUserAccountPassword>
                        <!--Optional:-->
                        <tem:token>
                            <!--Optional:-->
                            <log:ApiId>' . $request->ApiId . '</log:ApiId>
                            <!--Optional:-->
                            <log:ExpirationDateUtc>' . $request->ExpirationDateUtc . '</log:ExpirationDateUtc>
                            <!--Optional:-->
                            <log:Id>' . $check->token . '</log:Id>
                            <!--Optional:-->
                            <log:IsExpired>' . $check->IsExpired . '</log:IsExpired>
                            <!--Optional:-->
                            <log:TokenRejected>' . $check->TokenRejected . '</log:TokenRejected>
                        </tem:token>
                        <!--Optional:-->
                        <tem:id>' . $request->user_id . '</tem:id>
                        <!--Optional:-->
                        <tem:password>' . $request->password . '</tem:password>
                    </tem:SetUserAccountPassword>
                </soapenv:Body>
                </soapenv:Envelope>';

            $curl1 = curl_init();

            curl_setopt_array($curl1, array(
                CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $forgetPassword,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/IUserAccountService/SetUserAccountPassword'
                ),
            ));

            $response = curl_exec($curl1);
            if (curl_errno($curl1)) {
                $response = curl_error($curl1);

                return response()->json(['status' => 0, 'message' => 'crul error', 'response' => $response], 400);
            }
            curl_close($curl1);

            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $responseArray = json_decode($json, true);
            if (isset($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult']) && !empty($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult'])) {
                if ($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult'] == "true") {
                    User::where('UID', $request->user_id)->update(['password' => $request->password]);
                    return response()->json(['status' => 1, 'message' => 'Password update sucessfully', 'response' => $response]);
                } else {

                    return response()->json(['status' => 0, 'message' => 'something went wrong'], 400);
                }
            }
        } catch (\Exception $e) {

            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    public function UpdateUserAccount(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'userName' => 'required',
            'firstName' => 'required',
            'lastName' => 'required',
            'company' => 'required',
            'email' => ['required', 'email'],
            'ApiId' => 'required',
            'ExpirationDateUtc' => 'required',
            //'token' => 'required',
            'IsExpired' => 'required',
            'TokenRejected' => 'required',
            'CreationDate' => 'required',
            'LastLoginDate' => 'required',
            'LastUpdated' => 'required',
            'LockedUntil' => 'required',

        ]);
        $fields = array(
            'userName',
            'firstName',
            'lastName',
            'email',
            'ApiId',
            'ExpirationDateUtc',
            'token',
            'IsExpired',
            'TokenRejected',
            'CreationDate',
            'LastLoginDate',
            'LastUpdated',
            'LockedUntil'
        );
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
            $check = $this->refreshToken($tokenData);

            $billing_city = ($request->has('billing_city')) ? $request->billing_city : "";
            $billing_country_code = ($request->has('billing_country_code')) ? $request->billing_country_code : "";
            $billing_first_name = ($request->has('billing_first_name')) ? $request->billing_first_name : "";
            $billing_last_name = ($request->has('billing_last_name')) ? $request->billing_last_name : "";
            $billing_line1 = ($request->has('billing_line1')) ? $request->billing_line1 : "";
            $billing_line2 = ($request->has('billing_line2')) ? $request->billing_line2 : "";
            $billing_phone = ($request->has('billing_phone')) ? $request->billing_phone : "";
            $billing_postal = ($request->has('billing_postal')) ? $request->billing_postal : "";


            $shipping_city = ($request->has('shipping_city')) ? $request->shipping_city : "";
            $shipping_country_code = ($request->has('shipping_country_code')) ? $request->shipping_country_code : "";
            $shipping_first_name = ($request->has('shipping_first_name')) ? $request->shipping_first_name : "";
            $shipping_last_name = ($request->has('shipping_last_name')) ? $request->shipping_last_name : "";
            $shipping_line1 = ($request->has('shipping_line1')) ? $request->shipping_line1 : "";
            $shipping_line2 = ($request->has('shipping_line2')) ? $request->shipping_line2 : "";
            $shipping_phone = ($request->has('shipping_phone')) ? $request->shipping_phone : "";
            $shipping_postal = ($request->has('shipping_postal')) ? $request->shipping_postal : "";

            $profileUpdate   =   '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
               <soapenv:Header/>
               <soapenv:Body>
                  <tem:UpdateUserAccount>
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
                     <tem:user>
                        <!--Optional:-->
                        <log:AccountBackendDepartmentId>?</log:AccountBackendDepartmentId>
                        <!--Optional:-->
                        <log:AccountBackendUserId>?</log:AccountBackendUserId>
                        <!--Optional:-->
                        <log:AccountManagerId>?</log:AccountManagerId>
                        <!--Optional:-->
                        <log:AccountNumber>?</log:AccountNumber>
                        <!--Optional:-->
                        <log:AccountRole>Admin</log:AccountRole>
                        <!--Optional:-->
                        <log:AccountSuperAdminId>?</log:AccountSuperAdminId>
                        <!--Optional:-->
                        <log:BackendDepartmentId>?</log:BackendDepartmentId>
                        <!--Optional:-->
                        <log:BackendUserId>?</log:BackendUserId>
                        <!--Optional:-->
                        <log:BillingAddress>
                           <!--Optional:-->
                           <log:BackendUserId xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
                           <!--Optional:-->
                           <log:BranchCode>?</log:BranchCode>
                           <!--Optional:-->
                           <log:City>' . $billing_city . '</log:City>
                           <!--Optional:-->
                           <log:Company>?</log:Company>
                           <!--Optional:-->
                           <log:CountryCode>' . $billing_country_code . '</log:CountryCode>
                           <!--Optional:-->
                           <log:DepartmentId>?</log:DepartmentId>
                           <!--Optional:-->
                           <log:DepartmentName>test</log:DepartmentName>
                           <!--Optional:-->
                           <log:Enabled>false</log:Enabled>
                           <!--Optional:-->
                           <log:Fax>?</log:Fax>
                           <!--Optional:-->
                           <log:FirstName>' . $billing_first_name . '</log:FirstName>
                           <!--Optional:-->
                           <log:Id xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
                           <!--Optional:-->
                           <log:LastName>' . $billing_last_name . '</log:LastName>
                           <!--Optional:-->
                           <log:Line1>' . $billing_line1 . '</log:Line1>
                           <!--Optional:-->
                           <log:Line2>' . $billing_line2 . '</log:Line2>
                           <!--Optional:-->
                           <log:Line3>?</log:Line3>
                           <!--Optional:-->
                           <log:MiddleInitial>?</log:MiddleInitial>
                           <!--Optional:-->
                           <log:NickName>?</log:NickName>
                           <!--Optional:-->
                           <log:Phone>' . $billing_phone . '</log:Phone>
                           <!--Optional:-->
                           <log:PostalCode>' . $billing_postal . '</log:PostalCode>
                           <!--Optional:-->
                           <log:RegionCode>?</log:RegionCode>
                           <!--Optional:-->
                           <log:RouteCode>?</log:RouteCode>
                           <!--Optional:-->
                           <log:ThirdPartyId>?</log:ThirdPartyId>
                        </log:BillingAddress>
                        <!--Optional:-->
                        <log:Comment>?</log:Comment>
                        <!--Optional:-->
                        <log:Company>?</log:Company>
                        <!--Optional:-->
                        <log:CreationDate>2023-04-13T00:00:00.000+05:00</log:CreationDate>
                        <!--Optional:-->
                        <log:CustomQuestionAnswers>?</log:CustomQuestionAnswers>
                        <!--Optional:-->
                        <log:Email>' . $request->email . '</log:Email>
                        <!--Optional:-->
                        <log:Email2>?</log:Email2>
                        <!--Optional:-->
                        <log:Email3>?</log:Email3>
                        <!--Optional:-->
                        <log:Email4>?</log:Email4>
                        <!--Optional:-->
                        <log:FailedLoginCount>0</log:FailedLoginCount>
                        <!--Optional:-->
                        <log:FirstName>' . $request->firstName . '</log:FirstName>
                        <!--Optional:-->
                        <log:Id>' . $request->ApiId . '</log:Id>
                        <!--Optional:-->
                        <log:LastLoginDate>' . $request->LastLoginDate . '</log:LastLoginDate>
                        <!--Optional:-->
                        <log:LastName>' . $request->lastName . '</log:LastName>
                        <!--Optional:-->
                        <log:LastUpdated>' . $request->LastLoginDate . '</log:LastUpdated>
                        <!--Optional:-->
                        <log:Locked>true</log:Locked>
                        <!--Optional:-->
                        <log:LockedUntil>' . $request->LockedUntil . '</log:LockedUntil>
                        <!--Optional:-->
                        <log:PasswordHint>?</log:PasswordHint>
                        <!--Optional:-->
                        <log:PriceRuleId>?</log:PriceRuleId>
                        <!--Optional:-->
                        <log:PricingGroupId>?</log:PricingGroupId>
                        <!--Optional:-->
                        <log:SalesPersonId>?</log:SalesPersonId>
                        <!--Optional:-->
                        <log:ShippingAddress>
                           <!--Optional:-->
                           <log:BackendUserId>?</log:BackendUserId>
                           <!--Optional:-->
                           <log:BranchCode>?</log:BranchCode>
                           <!--Optional:-->
                           <log:City>' . $shipping_city . '</log:City>
                           <!--Optional:-->
                           <log:Company>?</log:Company>
                           <!--Optional:-->
                           <log:CountryCode>' . $shipping_country_code . '</log:CountryCode>
                           <!--Optional:-->
                           <log:DepartmentId>?</log:DepartmentId>
                           <!--Optional:-->
                           <log:DepartmentName>?</log:DepartmentName>
                           <!--Optional:-->
                           <log:Enabled>false</log:Enabled>
                           <!--Optional:-->
                           <log:Fax>?</log:Fax>
                           <!--Optional:-->
                           <log:FirstName>' . $shipping_first_name . '</log:FirstName>
                           <!--Optional:-->
                           <log:Id xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
                           <!--Optional:-->
                           <log:LastName>' . $shipping_last_name . '</log:LastName>
                           <!--Optional:-->
                           <log:Line1>' . $shipping_line1 . '</log:Line1>
                           <!--Optional:-->
                           <log:Line2>' . $shipping_line2 . '</log:Line2>
                           <!--Optional:-->
                           <log:Line3>?</log:Line3>
                           <!--Optional:-->
                           <log:MiddleInitial>?</log:MiddleInitial>
                           <!--Optional:-->
                           <log:NickName>?</log:NickName>
                           <!--Optional:-->
                           <log:Phone>' . $shipping_phone . '</log:Phone>
                           <!--Optional:-->
                           <log:PostalCode>' . $shipping_postal . '</log:PostalCode>
                           <!--Optional:-->
                           <log:RegionCode>?</log:RegionCode>
                           <!--Optional:-->
                           <log:RouteCode>?</log:RouteCode>
                           <!--Optional:-->
                           <log:ThirdPartyId>?</log:ThirdPartyId>
                        </log:ShippingAddress>
                        <!--Optional:-->
                        <log:ShippingExempt>false</log:ShippingExempt>
                        <!--Optional:-->
                        <log:TaxExempt>false</log:TaxExempt>
                        <!--Optional:-->
                        <log:UserName>' . $request->userName . '</log:UserName>
                     </tem:user>
                  </tem:UpdateUserAccount>
               </soapenv:Body>
            </soapenv:Envelope>';
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $profileUpdate,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: http://tempuri.org/IUserAccountService/UpdateUserAccount'
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
            $responseArray = json_decode($json, true); // true to have an array, false for an object

            if (isset($responseArray['sBody']['UpdateUserAccountResponse']['UpdateUserAccountResult']) && !empty($responseArray['sBody']['UpdateUserAccountResponse']['UpdateUserAccountResult'])) {
                if ($responseArray['sBody']['UpdateUserAccountResponse']['UpdateUserAccountResult'] == "true") {
                    // User::where('UID',$request->user_id)->update(['email'=>$request->email]);
                    return response()->json(['status' => 1, 'message' => 'profile update sucessfully', 'response' => $response]);
                } else {

                    return response()->json(['status' => 0, 'message' => 'something went wrong', 'response' => $response], 400);
                }
            }
        } catch (\Exception $e) {

            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }


    public function forgetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' =>  $error, 'response' => []], 400);
        }
        $userInfo =  User::where('email', $request->email)->first();
        $mailStatus =  $this->sendMail($userInfo->name,  $userInfo->email);
        if ($mailStatus != false) {
            return response()->json(['status' => 1, 'message' =>  $mailStatus['message'], 'response' =>  $mailStatus['response']]);
        } else {
            return response()->json(['status' => 0, 'message' => $mailStatus['message']], 400);
        }
    }

    public  function sendMail($username, $useremail)
    {
        try {
            $verifilink = encrypt($useremail);
            $response =  Mail::send('emails.forgot-password', ['username' => $username, 'verifilink' => $verifilink], function ($message)  use ($useremail) {
                $message->to($useremail);
                $message->subject('Reset Password');
            });
            $data['status'] = true;
            $data['message'] =  'email sent sucessfully';
            $data['response'] =  $response;
            return $data;
        } catch (\Exception $e) {
            $data['status'] = false;
            $data['message'] =  $e->getMessage();
            $data['response'] =  [];
            return $data;
        }
    }


    public function GetUserInfoThirdParty($user_id)
    {

        $tokenData = User::first();
        $check = $this->refreshToken($tokenData);
        $soapEnvelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
               <tem:GetUserAccountById>
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
                  <tem:userId>' . $user_id . '</tem:userId>
               </tem:GetUserAccountById>
            </soapenv:Body>
         </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $soapEnvelope,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IUserAccountService/GetUserAccountById'
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
        $responseArray = json_decode($json, true); // true to have an array, false for an object
        if (isset($responseArray['sBody']['sFault']['faultstring']) && !empty($responseArray['sBody']['sFault']['faultstring'])) {

            return response()->json(['status' => 0, 'message' => $responseArray['sBody']['sFault']['faultstring'], 'response' => $response], 400);
        }
        $UserDetail = [];

        if (isset($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult']) && !empty($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'])) {

            $UserDetail = $responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'];
        }

        return response()->json(['status' => 1, 'message' => 'user detail', 'userDetail' => $UserDetail, 'response' => $response]);
    }



    public function GetUserInfoByaApiId($aApiId)
    {
        $tokenData = User::first();
        $check = $this->refreshToken($tokenData);
      //  dd($check);
        $soapEnvelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:log="http://schemas.datacontract.org/2004/07/Logicblock.Commerce.Domain">
            <soapenv:Header/>
            <soapenv:Body>
               <tem:GetUserAccountById>
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
                  <tem:userId>' . $aApiId . '</tem:userId>
               </tem:GetUserAccountById>
            </soapenv:Body>
         </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.bundlesadvantage.com/api/UserAccountService.svc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $soapEnvelope,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://tempuri.org/IUserAccountService/GetUserAccountById'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);

            return $response;
        }


        curl_close($curl);
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true); // true to have an array, false for an object
        if (isset($responseArray['sBody']['sFault']['faultstring']) && !empty($responseArray['sBody']['sFault']['faultstring'])) {

            return $response;
        }
        $UserDetail = [];

        if (isset($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult']) && !empty($responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'])) {

            $UserDetail = $responseArray['sBody']['GetUserAccountByIdResponse']['GetUserAccountByIdResult'];
        }
        return $UserDetail;
    }


    public function getUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstErrorMessage = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => __($firstErrorMessage)]);
        }
        $UserDetail =  $this->GetUserAccountById($request);
        return response()->json(['status' => 1, 'message' => 'user detail', 'userDetail' => $UserDetail, 'response' => $UserDetail]);
    }

    public function removeAccount(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users,email',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return response()->json(['status' => 0, 'message' =>  $error, 'response' => []], 200);
            }

            User::where('email', $request->email)->update(['status' => 'inActive']);

            return response()->json(['status' => 1, 'message' => 'Your account has been deactivated', 'response' => []]);
        } catch (\Exception $e) {
            $data['status'] = false;
            $data['message'] =  $e->getMessage();
            $data['response'] =  [];
            return $data;
        }
    }
}
