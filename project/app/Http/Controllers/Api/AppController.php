<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Coupon\CouponResource;
use App\Http\Resources\Page\PagesResource;
use App\Http\Resources\Product\ProductsResource;
use App\Http\Resources\Shipping\ShippingResource;
use App\Http\Resources\Slider\SliderResource;
use App\Models\AppSlider;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Product;
use App\Models\Shipping;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function home_contents(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $gs=Generalsetting::find(1);
                $flash = Product::query()->where('is_discount',1)->take(10)->orderBy('id','DESC')->get();
                $latest = Product::query()->where('latest',1)->take(10)->orderBy('id','DESC')->get();
                $sliders = AppSlider::query()->where('status',1)->get();
                $categories = Category::query()->take(10)->get();
                if($flash->isNotEmpty()||$latest->isNotEmpty()||$sliders->isNotEmpty()||$categories->isNotEmpty()){
                    return [
                        'sliders'=>SliderResource::collection($sliders),
                        'flash'=>ProductsResource::collection($flash),
                        'latest'=>ProductsResource::collection($latest),
                        'categories'=>CategoryResource::collection($categories),
                        'settings'=>[
                            'is_min_order'=>$gs->is_min_order,
                            'min_order_limit'=>$gs->min_order_limit,
                            'is_evb_checkout'=>$gs->evb_check,
                            'min_evb_limit'=>$gs->evb_limit,
                        ]
                    ];
                }else{
                    return response(['message'=>'Not Found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function shipping_methods(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $shippings=Shipping::all();
                if($shippings){
                    return ShippingResource::collection($shippings);
                }else{
                    return response(['message'=>'Not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function verify_coupon(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $coupon=Coupon::query()
                    ->where('code',$request->coupon_code)
                    ->where(function($query){
                        $query->whereNull('times')
                            ->orWhere('times','>',0);
                    })
                    ->where('start_date','<=',Carbon::parse()->format('Y-m-d'))
                    ->where('end_date','>=',Carbon::parse()->format('Y-m-d'))
                    ->first();
                if($coupon){
                    return new CouponResource($coupon);
                }else{
                    return response(['message'=>'Not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function get_pages(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $page_id=$request->page_id;
                $pages=Page::query()
                    ->when($page_id,function ($query) use($page_id){
                        $query->where('id',$page_id);
                    })
                    ->get();
                if($pages){
                    return PagesResource::collection($pages);
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
    public function api_format(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){

//                if($x){
//
//                }else{
//                    return response('Not found',200);
//                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }





}
