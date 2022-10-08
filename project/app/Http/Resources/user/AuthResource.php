<?php

namespace App\Http\Resources\user;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
            'name'=>$this->name,
            'phone'=>$this->phone,
            'phone_verified'=>$this->phone_verified,
            'email'=>$this->email,
            'gender'=>$this->gender,
            'photo'=>asset("assets/images/users/".$this->photo),
            'client_id'=>$this->firebase_client_id,
            'ref_id'=>$this->ref_id,
            'balance'=>$this->balance,
        ];
    }
}
