<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            "customer_name"=> $this->user->name,
            "customer_image"=> $this->user->photo,
            "review"=>$this->review,
            "rating"=>$this->rating,
            "review_date"=>$this->review_date
        ];
    }
}
