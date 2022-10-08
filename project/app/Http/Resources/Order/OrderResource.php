<?php

namespace App\Http\Resources\Order;

use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number'=>$this->order_number,
            'method'=>$this->method,
            'total_qty'=>$this->totalQty,
            'pay_amount'=>$this->pay_amount*$curr->value,
            'payment_status'=>$this->payment_status,
            'delivery_status'=>$this->status,
            'order_date'=>Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
