<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\WishListResource;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function get_wishlist(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $wishlist=Wishlist::query()
                    ->where('user_id',$request->user_id)
                    ->paginate(20);
                if($wishlist->isNotEmpty()){
                    return WishListResource::collection($wishlist);
                }else{
                    return response(['message'=>'Not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function add_wishlist(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                if($request->user_id && $request->product_id){
                    $exists=Wishlist::query()
                        ->where('user_id',$request->user_id)
                        ->where('product_id',$request->product_id)
                        ->count();
                    if ($exists>0){
                        return response('Product Already Exists in wishlist',200);
                    }
                    $wishlist=new Wishlist();
                    $wishlist->user_id=$request->user_id;
                    $wishlist->product_id=$request->product_id;
                    $wishlist->save();
                    return new WishListResource($wishlist);
                }else{
                    return response('Bad Request',400);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function remove_wishlist(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){
                $wishlist=Wishlist::query()
                    ->where('user_id',$request->user_id)
                    ->where('product_id',$request->product_id)
                    ->first();
                if($wishlist){
                    $wishlist->delete();
                    return response(['message'=>'wishlist deleted successfully'],200);
                }else{
                    return response(['message'=>'Not found'],200);
                }
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }
}
