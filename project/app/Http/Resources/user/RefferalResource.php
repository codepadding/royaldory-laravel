<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class RefferalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'name'=>$this->Referrer?$this->Referrer->name:'',
            'ref_point'=>$this->amount,
            'date'=>$this->created_at?Carbon::parse($this->created_at)->format('Y-m-d H:i:s'):'',
        ];
    }
}
