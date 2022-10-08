<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Category\ChildCategoryResource;
use App\Http\Resources\Category\SubCategoryResource;
use App\Models\Category;
use App\Models\Childcategory;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categories(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                if($request->category_id){
                    $subcategories=Subcategory::query()->where('category_id',$request->category_id)->get();
                    if($subcategories->isNotEmpty()){
                        return SubCategoryResource::collection($subcategories);
                    }
                }elseif($request->subcategory_id){
                    $childcategories=Childcategory::query()->where('subcategory_id',$request->subcategory_id)->get();
                    if($childcategories->isNotEmpty()){
                        return ChildCategoryResource::collection($childcategories);
                    }
                }else{
                    $categories=Category::all();
                    if($categories->isNotEmpty()){
                        return CategoryResource::collection($categories);
                    }
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }
}
