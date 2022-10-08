<?php

namespace App\Http\Controllers\User;

use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Validator;

class ForgotController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showForgotForm()
    {
      return view('user.forgot');
    }

    public function forgot(Request $request)
    {
      $gs = Generalsetting::findOrFail(1);
      $input =  $request->all();
      if (User::where('phone', '=', $request->phone)->count() > 0) {
          // user found
          $admin = User::where('phone', '=', $request->phone)->firstOrFail();
          $autopass = str_random(8);
          $input['password'] = Hash::make($autopass);
          $admin->update($input);
//          $subject = "Reset Password Request";
          $msg = "Dear $admin->name\nYour New Password for http://evendory.com is : ".$autopass;
          SendSms($admin->phone,$msg);
//          if($gs->is_smtp == 1)
//          {
//              $data = [
//                      'to' => $request->email,
//                      'subject' => $subject,
//                      'body' => $msg,
//              ];
//
//              $mailer = new GeniusMailer();
//              $mailer->sendCustomMail($data);
//          }
//          else
//          {
//              $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
//              mail($request->email,$subject,$msg,$headers);
//          }
          return response()->json('Your Password Reseted Successfully. Please Check your Phone for new Password.');
      }else{
      // user not found
      return response()->json(array('errors' => [ 0 => 'No Account Found With This Phone.' ]));
      }  
    }

}
