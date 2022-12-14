<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WishListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'product'=>new ProductsResource($this->product)
        ];
    }
}
