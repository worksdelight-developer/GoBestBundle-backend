<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    //


    public function resetPassword($token)
    {
        $data['email'] = decrypt($token);
        $data['token'] = $token;
        return view('changepassword', $data);
    }

    public function  updatePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'secret_token' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return redirect()->back()->with('error', $error);
        }
        $data['email'] = decrypt($request->secret_token);
        $userInfo =  User::where('email', $data['email'])->first();
        if ($userInfo != null) {

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
                                <log:ApiId>' . $userInfo->ApiId . '</log:ApiId>
                                <!--Optional:-->
                                <log:ExpirationDateUtc>' . $userInfo->ExpirationDateUtc . '</log:ExpirationDateUtc>
                                <!--Optional:-->
                                <log:Id>' . $check->token . '</log:Id>
                                <!--Optional:-->
                                <log:IsExpired>' . $userInfo->IsExpired . '</log:IsExpired>
                                <!--Optional:-->
                                <log:TokenRejected>' . $userInfo->TokenRejected . '</log:TokenRejected>
                            </tem:token>
                            <!--Optional:-->
                            <tem:id>' . $userInfo->UID . '</tem:id>
                            <!--Optional:-->
                            <tem:password>' . $request->password . '</tem:password>
                        </tem:SetUserAccountPassword>
                    </soapenv:Body>
                    </soapenv:Envelope>';

                $curl1 = curl_init();

                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'http://www.gobestbundles.com/api/UserAccountService.svc',
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
                    return redirect()->back()->with('error', 'crul error');
                }
                curl_close($curl1);

                $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response);
                $xml = simplexml_load_string($xml);
                $json = json_encode($xml);
                $responseArray = json_decode($json, true);
                if (isset($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult']) && !empty($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult'])) {
                    if ($responseArray['sBody']['SetUserAccountPasswordResponse']['SetUserAccountPasswordResult'] == "true") {
                        User::where('UID', $userInfo->UID)->update(['password' => $request->password]);
                        return redirect()->back()->with('success', 'Password updated successfully');
                    } else {
                        return redirect()->back()->with('error', 'error');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'error');
            }
        }
        return redirect()->back();
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
            CURLOPT_URL => 'http://www.gobestbundles.com/api/UserAccountService.svc',
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
                    CURLOPT_URL => 'http://www.gobestbundles.com/api/UserAccountService.svc',
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
}
