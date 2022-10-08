<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class isPhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $user=Auth::guard('web')->user();
//        dd($user);
        if($user){
            if($user->phone_verified==1){
                return $next($request);
            }else{
                return redirect()->route('user.otp');
            }
        }else{
            return redirect('user.login');
        }

    }
}
