<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\JsonResource;

class ChildCategoryResource extends JsonResource
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
            'photo'=>$this->photo?asset('assets/images/childcategories/app/'.$this->photo):'',
        ];
    }
}
