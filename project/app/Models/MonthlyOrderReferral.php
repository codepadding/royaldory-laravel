<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyOrderReferral extends Model
{
    protected $fillable=['range_from','range_to','amount','status'];
}
