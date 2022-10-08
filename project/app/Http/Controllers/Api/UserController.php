<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\AuthResource;
use App\Http\Resources\user\RefferalResource;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Referral;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function GuzzleHttp\Psr7\str;

class UserController extends Controller
{
    public function register(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        $gs=Generalsetting::find(1);
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $validation = Validator::make($request->all(),[
                    'name' => 'required',
                    'phone' => 'required|unique:users',
                    'password' => 'required',
                ]);

                if($validation->fails()){
                    $errors = $validation->errors();
                    return response($errors,400);
                }else{
                    $user=new User();
                    $rand_string=strtoupper(substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 8));
                    $user->name=$request->name;
                    $user->phone=$request->phone;
                    $user->password=Hash::make($request->password);
                    $user->invitation_code=$request->invitation_code;
                    $user->firebase_client_id=$request->firebase_client_id;
                    $user->affilate_code=md5($request->name.$request->email);
                    $user->ref_id=$rand_string;
                    if($request->ref_by){
                        $refferer=User::where('ref_id',strtoupper($request->ref_by))->first();
                        if($refferer && $gs->is_refferal==1){
                            $user->ref_by=$refferer->id;
                        }
                    }

                    $user->save();

                    //sending notification to admin

                    $otp=substr(str_shuffle(rand(100000,999999).rand(100000,999999)),3,4);
                    $user->temp_otp=$otp;
                    $user->otp_validity=Carbon::parse()->addMinutes(5)->format('Y-m-d H:i:s');
                    $user->update();
                    $msg = "$otp is your One Time Password (OTP) for Royaldory. OTP is valid till ".Carbon::parse($user->otp_validity)->format('H:i:s');
                    SendSms($user->phone, $msg);
                    $notification = new Notification;
                    $notification->user_id = $user->id;
//                    $notification->save();
                    return new AuthResource($user);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function login(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $validation = Validator::make($request->all(),[
                    'phone' => 'required',
                    'password' => 'required',
                ]);

                if($validation->fails()){
                    $errors = $validation->errors();
                    return response($errors,400);
                }else{
                    $login=Auth::attempt(['phone' => $request->phone, 'password' => $request->password]);
                    if($login){
//                        return response('Login Success',200);
                        $user=User::query()->where('phone',$request->phone)->first();
                        if($request->firebase_client_id && $user->firebase_client_id != null){
                            $user->firebase_client_id=$request->firebase_client_id;
                            $user->update();
                        }
                        return new AuthResource($user);
                    }else{
                        return response('Invalid Credentials, Login Failed',401);
                    }
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function sendOtp(Request $request){
        $rules = [
            'phone' => 'nullable|regex:/(01)[0-9]{9}/|unique:users,phone,'.$request->user_id.',id',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response('phone number is invalid or already taken',409);
        }
        $otp=substr(str_shuffle(rand(100000,999999).rand(100000,999999)),3,4);
        $user=User::find($request->user_id);
        $update=$request->is_update;
        if($user){
            if(Carbon::parse($user->otp_validity)<Carbon::parse()) {
                if ($request->phone && $update!=1) {
                    $user->phone = $request->phone;
                }
                $phone=$request->phone??$user->phone;
                $user->temp_otp = $otp;
                $user->otp_validity = Carbon::parse()->addMinutes(5)->format('Y-m-d H:i:s');
                $user->update();
                $msg = "$otp is your One Time Password (OTP) for Royaldory. OTP is valid till ".Carbon::parse($user->otp_validity)->format('H:i:s');
                SendSms($phone, $msg);
//                return response('OTP sent successfully', 200);
                return new AuthResource($user);
            }else{
                return response('Already have a request pending',403);
            }
        }else{
            return response('Bad Request',400);
        }
    }


    public function checkOtp(Request $request){
        $user=User::find($request->user_id);
        $gs=Generalsetting::find(1);
        if($user){
            if($user->temp_otp==$request->otp && Carbon::parse($user->otp_validity)>Carbon::parse()){
                $user->phone_verified=1;
                $user->update();

                if($user->ref_by){
                    $refferer=User::find($user->ref_by);
                    if ($refferer && $gs->is_refferal == 1) {
                        $refferer->balance+=$gs->refferal_point;
                        $refferer->update();
                        $history = new Referral();
                        $history->referrer_id = $refferer->id;
                        $history->referee_id = $user->id;
                        $history->amount = $gs->refferal_point;
                        $history->save();
                    }
                }
                return response('OTP Verified Successfully',200);
            }else{
                return response('Invalid OTP',400);
            }
        }
    }

    public function resetPassword(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $user=User::query()->where('phone',$request->phone)->first();
                if($user){
                    $key=str_random(8);
                    $user->password=Hash::make($key);
                    $user->update();
                    $msg="Your new Password for Royaldory is $key";
                    SendSms($user->phone,$msg);
                    return response('Password Reset was successful',200);
                }else{
                    return response('Bad Request',400);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function changePassword(Request $request){

        $validation = Validator::make($request->all(),[
            'user_id' => 'required',
            'old_password' => 'required',
            'password' => 'required',
        ]);

        if($validation->fails()){
            $errors = $validation->errors();
            return response($errors,400);
        }

        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $user=User::find($request->user_id);
                if($user){
                    $is_match=Hash::check($request->old_password,$user->password);
                    if($is_match){
                        $user->password=Hash::make($request->password);
                        $user->update();
                        return response()->json(['status'=>true,'message'=>'Password Changed Successfully']);
                    }else{
                        return response()->json(['status'=>false,'message'=>'Invalid Request']);
                    }

                }else{
                    return response()->json(['status'=>false,'message'=>'User Not Found']);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }

    }

    public function Update(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key == $partner_key) {
                $validation = Validator::make($request->all(),[
                    'user_id'=>'required',
                    'email' => 'nullable|email|unique:users,email,'.$request->user_id.',id',
                    'phone' => 'nullable|regex:/(01)[0-9]{9}/|unique:users,phone,'.$request->user_id.',id',
                ]);
                if($validation->fails()) {
                    $errors = $validation->errors();
                    return response($errors, 400);
                }
                $user=User::find($request->user_id);
                if($user){
                    if($request->name){
                        $user->name=$request->name;
                    }
                    if($request->phone){
                        $user->phone=$request->phone;
//                        if($user->phone != $request->phone){
//                            $user->phone_verified=0;
//                        }
                    }
                    if($request->gender){
                        $user->gender=$request->gender;
                    }
                    if($request->email){
                        $user->email=$request->email;
                    }
                    if($request->photo){
                        if ($file = $request->file('photo'))
                        {
                            $name = time().$file->getClientOriginalName();
                            $file->move('assets/images/users',$name);
                            if($user->photo != null)
                            {
                                if (file_exists(public_path().'/assets/images/users/'.$user->photo)) {
                                    unlink(public_path().'/assets/images/users/'.$user->photo);
                                }
                            }
                            $user->photo = $name;
                        }
                    }
                    $user->update();
                    return new AuthResource($user);
                }else{
                    return response('Bad Request',400);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function refHistory(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                    $user=User::find($request->user_id);
                    $refferals=Referral::query()
                        ->where('referrer_id',$request->user_id)
                        ->orderBy('id','DESC')
                        ->get();
                if($user && $refferals->isNotEmpty()){
                    return RefferalResource::collection($refferals);
                }else{
                    return response()->json(['status'=>false,'message'=>'Refferal History not found']);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }
}
