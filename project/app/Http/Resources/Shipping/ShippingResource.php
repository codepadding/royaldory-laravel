<?php

namespace App\Http\Resources\Shipping;

use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
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
            'title'=>$this->title,
            'price'=>round($this->price * $curr->value),
        ];
    }
}
