<?php

namespace App\Http\Resources\Notification;

use App\Http\Resources\Order\OrderResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
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
            'title'=>$this->title,
            'sub_title'=>$this->sub_title,
            'message'=>$this->message,
            'thumb'=>$this->thumb?asset('assets/images/notification/'.$this->thumb):'',
            'image'=>$this->image?asset('assets/images/notification/images/'.$this->image):'',
            'is_read'=>$this->is_read,
            'status'=>$this->status,
            'date'=>$this->created_at?Carbon::parse($this->created_at)->format('Y-m-d H:i:s'):'',
        ];
    }
}
