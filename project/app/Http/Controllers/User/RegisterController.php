<?php

namespace App\Http\Controllers\User;

use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\User;
use App\Classes\GeniusMailer;
use App\Models\Notification;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Validator;

class RegisterController extends Controller
{

    public function register(Request $request)
    {

        $gs = Generalsetting::findOrFail(1);

        if ($gs->is_capcha == 1) {
            $value = session('captcha_string');
            if ($request->codes != $value) {
                return response()->json(array('errors' => [0 => 'Please enter Correct Capcha Code.']));
            }
        }


        //--- Validation Section

        $rules = [
            'phone' => 'required|unique:users,phone|regex:/(01)[0-9]{9}/',
            'password' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $gs = Generalsetting::find(1);
        $user = new User;
        $rand_string = strtoupper(substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 8));
        $input = $request->all();
        $input['password'] = bcrypt($request['password']);
        $token = md5(time() . $request->name . $request->email);
        $input['verification_link'] = $token;
        $input['affilate_code'] = md5($request->name . $request->email);
        $input['ref_id'] = $rand_string;
        if ($request->inv_code) {
            $refferer = User::where('ref_id', strtoupper($request->inv_code))->first();
            if ($refferer && $gs->is_refferal == 1) {
                $input['ref_by'] = $refferer->id;
            }
        }


        if (!empty($request->vendor)) {
            //--- Validation Section
            $rules = [
                'shop_name' => 'unique:users',
                'shop_number' => 'max:10'
            ];
            $customs = [
                'shop_name.unique' => 'This Shop Name has already been taken.',
                'shop_number.max' => 'Shop Number Must Be Less Then 10 Digit.'
            ];

            $validator = Validator::make($request->all(), $rules, $customs);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $input['is_vendor'] = 1;

        }

        $user->fill($input)->save();



        if ($gs->is_verification_email == 1) {
            $to = $request->email;
            $subject = 'Verify your email address.';
            $msg = "Dear Customer,<br> We noticed that you need to verify your email address. <a href=" . url('user/register/verify/' . $token) . ">Simply click here to verify. </a>";
            //Sending Email To Customer
            if ($gs->is_smtp == 1) {
                $data = [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $msg,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            } else {
                $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                mail($to, $subject, $msg, $headers);
            }
            return response()->json('We need to verify your email address. We have sent an email to ' . $to . ' to verify your email address. Please click link in that email to continue.');
        } else {

            $user->email_verified = 'Yes';
            $user->update();
            $notification = new Notification;
            $notification->user_id = $user->id;
//	        $notification->save();
            Auth::guard('web')->login($user);
            return response()->json(1);
        }

    }

    public function token($token)
    {
        $gs = Generalsetting::findOrFail(1);

        if ($gs->is_verification_email == 1) {
            $user = User::where('verification_link', '=', $token)->first();
            if (isset($user)) {
                $user->email_verified = 'Yes';
                $user->update();
                $notification = new Notification;
                $notification->user_id = $user->id;
                $notification->save();
                Auth::guard('web')->login($user);
                return redirect()->route('user-dashboard')->with('success', 'Email Verified Successfully');
            }
        } else {
            return redirect()->back();
        }
    }

    public function otp()
    {
        return view('user.otp');
    }

    public function sendOtp(Request $request)
    {
        $this->validate($request, [
            'phone' => 'nullable|unique:users,phone|regex:/(01)[0-9]{9}/',
        ]);
        $user = Auth::guard('web')->user();
        $otp = substr(str_shuffle(rand(100000, 999999) . rand(100000, 999999)), 3, 4);
        if ($user && $user->phone_verified == 0) {
            if (Carbon::parse($user->otp_validity) < Carbon::parse()) {
                if ($request->phone) {
                    $user->phone = $request->phone;
                }
                $user->temp_otp = $otp;
                $user->otp_validity = Carbon::parse()->addMinutes(5)->format('Y-m-d H:i:s');
                $user->update();
                $msg = "$otp is your One Time Password (OTP) for http://evendory.com. OTP is valid till " . Carbon::parse($user->otp_validity)->format('H:i:s');
                SendSms($user->phone, $msg);
                Session::forget(['success', 'alert']);
                Session::flash('success', 'OTP sent successfully');
                return view('user.otp');
            } else {
                Session::forget(['success', 'alert']);
                Session::flash('alert', 'already have a request pending, please try again after a few minutes');
                return view('user.otp');
            }
        } else {
            return view('user.login');
        }
    }

    public function checkOtp(Request $request)
    {
        $user = Auth::guard('web')->user();
        $gs = Generalsetting::find(1);
        if ($user) {
            if ($user->temp_otp == $request->otp && Carbon::parse($user->otp_validity) > Carbon::parse()) {
                $user->phone_verified = 1;
                $user->update();

                //to keep referral history
                if ($user->ref_by) {
                    $refferer = User::find($user->ref_by);
                    if ($refferer && $gs->is_refferal == 1) {
                        $refferer->balance += $gs->refferal_point;
                        $refferer->update();

                        $history = new Referral();
                        $history->referrer_id = $refferer->id;
                        $history->referee_id = $user->id;
                        $history->amount = $gs->refferal_point;
                        $history->save();
                    }
                }

                return response()->json(1);
            } else {
                return response()->json(['errors' => ['otp' => 'Invalid OTP']]);
            }
        }
    }

}