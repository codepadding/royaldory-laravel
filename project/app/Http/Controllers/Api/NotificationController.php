<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationsResource;
use App\Models\PushNotification;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notifications(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $user_id=$request->user_id;
                $notifications=PushNotification::query()
                    ->where('status',1)
                    ->orderBy('id','DESC')
                    ->paginate(15);
                if($notifications->isNotEmpty()){
                    return NotificationsResource::collection($notifications);
                }else{
                    return response('Not found',200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }
}
