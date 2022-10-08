<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProductUpdateController extends Controller
{
    public function index(Request $request){
        $categories=Category::query()->get(['name','id']);
        $curr = Currency::where('is_default', '=', 1)->first();
        $category_id=$request->category_id;
        $name=$request->product;
        $products=Product::query()
            ->when($category_id,function ($query) use($category_id){
                $query->where('category_id',$category_id);
            })
            ->when($name,function ($query) use($name){
                $query->where('name','like','%'.$name.'%');
            })

            ->paginate(15);
        return view('admin.productupdate.index',compact('products','categories','curr','category_id'));
    }

    public function update(Request $request){
        $products=$request->product_id;
        $prices=$request->product_price;
        $stocks=$request->product_stock;
        $inventory=$request->product_inventory;
        $curr = Currency::where('is_default', '=', 1)->first();
        foreach ($products as $product_id){
            $product=Product::find($product_id);
            $product->stock=$stocks[$product_id];
            if($inventory[$product_id]==0){
                $product->stock=0;
            }
            $product->price=$prices[$product_id] / $curr->value;
            $product->update();

        }
        return Redirect::back()->with('success','products updated successfully');
    }
}
