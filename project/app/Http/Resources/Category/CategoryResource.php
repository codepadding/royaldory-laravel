<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name'=>$request->lang?$this->getTranslation('name',$request->lang):$this->name,
            'slug'=>$this->slug,
            'photo'=>$this->photo?asset('assets/images/categories/app/'.$this->photo):'',
//            'image'=>$this->image?asset('assets/images/categories/'.$this->image):'',
            'is_featured'=>$this->is_featured??'',
            'has_subcategory'=>$this->subs->count()>0?true:false
        ];
    }
}
