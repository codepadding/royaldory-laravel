<?php

use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;

function Partner_key(){
//    $api_url= "https://partner.uparzon.com/api/partner/api_key";
//    $client = new Client();
//    $response = $client->post($api_url);
//    $partner_key=$response->getBody()->getContents();
//    return $partner_key;
        return "4e38d8be3269aa17280d0468b89caa4c7d39a699";
}
function SendSms($to,$msg){
    $smsKey="1540e926472650e28fa88c38f461b72a";
    $url = "http://api.greenweb.com.bd/api.php";
    $data = [
        "to" => $to,
        "message" => $msg,
        "token" => $smsKey
    ];
    $client=New Client();
    $res=$client->request('POST',$url,['form_params'=>$data]);
    return $res->getStatusCode();
}


function sendNotification($notification_id, $title, $subtitle, $body, $type, $token,$thumb, $image_url = null)
{
    define('API_ACCESS_KEY', 'AAAAFpwNopM:APA91bH92R18cm88mKir7_BmxCp4NEhhsqpu8-EtqoQOcPVpuIOXe_q0t7DGdo7TUmaP45QmuvAjkyCM1ei53S2ZcMDPQCZyeiObK_AIWgAq3sq2_QRYAOlD-Kw1hUZaFW-6NNnb6nli');
    $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    $notification = [
        'title' => "$title",
        'sub_title' => "$subtitle",
        'message' => "$body",
        'thumb' => "$thumb",
        'image' => $image_url
    ];

    $extraNotificationData = ["message" => $notification, "id" => "$notification_id"];

    if ($type == 'selected') {

        $fcmNotification = [
            'registration_ids' => $token, //multiple User token array
//            'notification' => $notification,
            'data' => $extraNotificationData
        ];

    } elseif ($type == 'multiple') {
        $fcmNotification = [
            'to' => '/topics/Evendory',
//            'notification' => $notification,
            'data' => $extraNotificationData
        ];

    }


    $headers = [
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    ];


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fcmUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


//function SendOtp($user_id){
//    $otp=substr(str_shuffle(rand(100000,999999).rand(100000,999999)),3,6);
//    $user=User::find($user_id);
//    if($user && $user->phone_verified==0){
//        $user->temp_otp=$otp;
//        $user->otp_validity=Carbon::parse()->addMinutes(5)->format('Y-m-d H:i:s');
//        $user->update();
//        return new \App\Http\Resources\user\AuthResource($user);
//    }else{
//        return response('Bad Request',402);
//    }
//}
//
//function CheckOtp($user_id,$otp){
//    $user=User::find($user_id);
//    if($user){
//        if($user->temp_otp==$otp && Carbon::parse($user->otp_validity)>Carbon::parse()){
//            $user->phone_verified=1;
//            $user->update();
//            return response('OTP Matched Successfully',200);
//        }else{
//            return response('Invalid OTP',402);
//        }
//    }
//}
