<?php

namespace App\Http\Resources\Slider;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            'title'=>$this->title_text,
            'photo'=>asset('assets/images/appsliders/'.$this->photo),
        ];
    }
}
