<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable=["referrer_id","referee_id","order_id","amount","type"];

    public function Referrer(){
        return $this->belongsTo(User::class,'referrer_id','id');
    }

    //user himself
    public function Referee(){
        return $this->belongsTo(User::class,'referee_id','id');
    }


}
