<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Rating\RatingResource;
use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'user_id'=>$this->user_id,
            'category_id'=>$this->category_id,
            'subcategory_id'=>$this->subcategory_id,
            'childcategory_id'=>$this->childcategory_id,
            'name'=>$request->lang?$this->getTranslation('name',$request->lang):$this->name,
            'photo'=>asset('assets/images/products/'.$this->photo),
            'thumbnail'=>asset('assets/images/thumbnails/'.$this->thumbnail),
            'size'=>$this->size!=''?$this->size:[],
            'size_qty'=>$this->size_qty !=''?$this->size_qty:[],
            'size_price'=>$this->size_price!=''?array_map($size_price,$this->size_price):[],
            'color'=>$this->color!=''?$this->color:[],
            'price'=>preg_replace('/[^0-9\.]/', '', $this->showPrice()), //to replace currency sign
            'previous_price'=>$this->previous_price >0 ? preg_replace('/[^0-9\.]/', '', $this->showPreviousPrice()):'0',
            'details'=>$request->lang?$this->getTranslation('details',$request->lang):$this->details,
            'stock'=>$this->stock,
            'views'=>$this->views,
            'tags'=>$this->tags,
            'colors'=>$this->colors,
            'product_condition'=>$this->product_condition,
            'ship'=>$this->ship,
            'type'=>$this->type,
            'link'=>$this->link,
            'measurement_id'=>$this->measurement_id,
            'measurement_unit'=>$this->measurement_unit,
            'featured'=>$this->featured,
            'best'=>$this->best,
            'top'=>$this->top,
            'hot'=>$this->hot,
            'latest'=>$this->latest,
            'big'=>$this->big,
            'trending'=>$this->trending,
            'sale'=>$this->sale,
            'is_discount'=>$this->is_discount,
            'discount_date'=>$this->discount_date,
            'rating'=>$this->ratings->count()>0? $this->ratings->avg('rating'):'No rating yet',
            'reviews'=>RatingResource::collection($this->ratings)
        ];
    }
}
