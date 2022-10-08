<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Subcategory extends Model
{
    use HasTranslations;
    protected $fillable = ['category_id','name','slug','photo'];
    public $timestamps = false;
    public $translatable = ['name'];

    public function childs()
    {
    	return $this->hasMany('App\Models\Childcategory')->where('status','=',1);
    }

    public function category()
    {
    	return $this->belongsTo('App\Models\Category')->withDefault(function ($data) {
			foreach($data->getFillable() as $dt){
				$data[$dt] = __('Deleted');
			}
		});
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '-', $value);
    }

    public function attributes() {
        return $this->morphMany('App\Models\Attribute', 'attributable');
    }

}
