<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Slider\SliderResource;
use App\Models\AppSlider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function sliders(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        if ($api_key != null && $partner_key !=null) {
            if ($api_key == $partner_key) {
                $sliders = AppSlider::query()
                    ->where('status',1)
                    ->get();
                if($sliders->isNotEmpty()){
                    return SliderResource::collection($sliders);
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
