<?php

namespace App\Http\Resources\Order;

use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
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
            'order_number'=>$this->order_number,
            'order_date'=>Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'payment_status'=>$this->payment_status,
            'delivery_status'=>$this->status,
            'customer_name'=>$this->customer_name,
            'customer_phone'=>$this->customer_phone,
            'customer_address'=>$this->customer_address,
            'shipping_name'=>$this->shipping_name,
            'shipping_phone'=>$this->shipping_phone,
            'shipping_address'=>$this->shipping_address,
            'shipping_city'=>$this->shipping_city,
            'method'=>$this->method,
            'total_qty'=>$this->totalQty,
            'pay_amount'=>round($this->pay_amount*$curr->value,2),
            'shipping_cost'=>$this->shipping_cost,
            'coupon_code'=>$this->coupon_code,
            'coupon_discount'=>$this->coupon_discount,
            'cart'=>$this->cart,
        ];
    }
}
