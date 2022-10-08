<?php

namespace App\Http\Resources\Coupon;

use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $curr = Currency::where('is_default', '=', 1)->first();
        return [
            'id'=>$this->id,
            'code'=>$this->code,
            'price'=>$this->type==1?$this->price * $curr->value:null,
            'percentage'=>$this->type==0?$this->price:null
        ];
    }
}
