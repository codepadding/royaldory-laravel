<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Rating\RatingResource;
use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsResource extends JsonResource
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
        //convert size price into currency
        $size_price=function ($val) use ($curr){
            return $val *$curr->value;
        };

        return [
            'id'=>$this->id,
            'sku'=>$this->sku,
            'name'=>$request->lang?$this->getTranslation('name',$request->lang):$this->name,
            'photo'=>asset('assets/images/products/'.$this->photo),
            'thumbnail'=>asset('assets/images/thumbnails/'.$this->thumbnail),
            'color'=>$this->color!=''?$this->color:[],
            'price'=>preg_replace('/[^0-9\.]/', '', $this->showPrice()), //to replace currency sign
            'previous_price'=>$this->previous_price >0 ? preg_replace('/[^0-9\.]/', '', $this->showPreviousPrice()):'0', //to replace currency sign
            'details'=>$request->lang?$this->getTranslation('details',$request->lang):$this->details,
            'stock'=>$this->stock,
            'in_wishlist'=>$this->wishlists->where('user_id',$request->user_id)->first()?true:false

        ];
    }
}
