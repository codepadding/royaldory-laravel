<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductsResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function product(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $product = Product::find($request->product_id);
                if($product !=null){
                    return new ProductResource($product);
                }else{
                    return response('Not found',200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function products(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $category = $request->category_id;
                $sub_category = $request->subcategory_id;
                $child_category = $request->childcategory_id;
                $top = $request->is_top;
                $hot=$request->is_hot;
                $trending=$request->is_trending;
                $sale=$request->is_sale;

                $products = Product::query()
                    ->when($category, function ($query) use ($category) {
                        $query->where('category_id', $category);
                    })
                    ->when($sub_category, function ($query) use ($sub_category) {
                        $query->where('subcategory_id', $sub_category);
                    })
                    ->when($child_category, function ($query) use ($child_category) {
                        $query->where('childcategory_id', $child_category);
                    })
                    ->when($top, function ($query) {
                        $query->where('top', 1);
                    })
                    ->when($hot, function ($query) {
                        $query->where('hot', 1);
                    })
                    ->when($trending, function ($query) {
                        $query->where('trending', 1);
                    })
                    ->when($sale, function ($query) {
                        $query->where('sale', 1);
                    })
                    ->where('status',1)
                    ->orderBy('id','DESC')
                    ->paginate(20);
                if($products->isNotEmpty()){
                    return ProductsResource::collection($products);
                }else{
                    return response(['message'=>'not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function flash_products(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $products = Product::query()
                    ->where('is_discount',1)
                    ->where('status',1)
                    ->orderBy('id','DESC')
                    ->paginate(10);
                if($products->isNotEmpty()){
                    return ProductsResource::collection($products);
                }else{
                    return response('Not found',200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function recent_products(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $products = Product::query()
                    ->where('latest',1)
                    ->where('status',1)
                    ->orderBy('id','DESC')
                    ->paginate(10);
                if($products->isNotEmpty()){
                    return ProductsResource::collection($products);
                }else{
                    return response('Not found',200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function search_product(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $search_string=$request->keyword;
                $products=Product::query()
                    ->where(function($query) use($search_string){
                        $query->where('tags','like','%'.$search_string.'%')
                            ->orWhere('name','like','%'.$search_string.'%');
                    })
                    ->where(function($query){
                        $query->whereNull('stock')
                            ->orWhere('stock','>',0);
                    })
                    ->where('status',1)
                    ->orderBy('id','DESC')
                    ->paginate(10);
                if($products->isNotEmpty()){
                    return ProductsResource::collection($products);
                }else{
                    return response(['message'=>'not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }
}
