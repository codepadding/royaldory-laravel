<?php

namespace App\Http\Controllers\Api;

use App\Classes\GeniusMailer;
use App\Http\Resources\Order\OrderDetailsResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\WishListResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderTrack;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\VendorOrder;
use App\Models\Wishlist;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OrderController extends Controller
{


    public function cashondelivery(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
//        dd($request->all());
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key == $partner_key) {
                $curr = Currency::where('is_default', '=', 1)->first();
                $gs = Generalsetting::findOrFail(1);
                $app_cart = $request->cart;
                $cart=[];
                $total_qty=0;
                $total_price=0;
//                dd($app_cart);
                //cart items here
                if(count($app_cart)>0){
                    foreach ($app_cart as $key => $prod) {
                        $total_qty+=$prod['qty'];
                        $total_price+=$prod['price'];
//                        $product=Product::select('id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measurement_id','measurement_unit','whole_sell_qty','whole_sell_discount','attributes')->find($prod['product_id']);
                        $product=Product::select('id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','whole_sell_qty','whole_sell_discount','attributes')->find($prod['product_id']);
                        if($product->user_id != 0){
                            $prc = $product->price + $gs->fixed_commission + ($product->price/100) * $gs->percentage_commission ;
                            $product->price = round($prc,2);
                        }
//                        dd($product);
                        $cart['items'][$key]['qty']=$prod['qty'];
                        $cart['items'][$key]['size_key']=$prod['size_key'] ==""? 0: $prod['size_key'];
                        $cart['items'][$key]['size_qty']=$prod['size_qty'];
                        $cart['items'][$key]['size_price']=$product->size_price;
                        $cart['items'][$key]['size']=$prod['size'];
                        $cart['items'][$key]['color']=preg_replace('/#/', '', $prod['color']);
                        $cart['items'][$key]['stock']=$product->stock !=null?$product->stock - $prod['qty']: $product->stock;
                        $cart['items'][$key]['price']=round($prod['price']) / $curr->value;
                        $cart['items'][$key]['item']=$product;
                        $cart['items'][$key]['license']=$product->license;
                        $cart['items'][$key]['dp']=0;
                        $cart['items'][$key]['keys']="";
                        $cart['items'][$key]['values']="";
                    }
                    $cart['totalQty']=$total_qty;
                    $cart['totalPrice']=round($total_price / $curr->value,2);
                    $cart=New Cart((object)$cart);
//                        dd($cart);

                }else{
                    return response('You have an empty cart!',200);
                }


                if($request->shipping_cost !=null && $request->shipping_cost >0){
                    $payamonut=$total_price + $request->shipping_cost;
                }else{
                    $payamonut=$total_price;
                }

                if( $request->coupon_id && $request->coupon_id != ""){
                    $coupon = Coupon::findOrFail($request->coupon_id);
                    $payamonut= $payamonut - $request->coupon_discount;
                }else{
                    $coupon=null;
                }

                //Order Limit Validation
                if($gs->is_min_order=1){
                    $shipping_cost=$request->shipping_cost>0?$request->shipping_cost:0;
                    $packing_cost=$request->packing_cost>0?$request->packing_cost:0;
                    $ttlPrice=$payamonut-$shipping_cost-$packing_cost;
                    if($ttlPrice<$gs->min_order_limit){
                        return response()->json(['status'=>false,'message'=>"minimum order limit is tk $gs->min_order_limit"]);
                    }
                }

                //Royaldory balance checkout validation
                if ($request->payment_method == "evb") {
                    $payment_method = 'Royaldory Balance';
                    $customer = User::find($request->user_id);
                    if($customer){
                        if ($gs->evb_check == 1 && $customer->balance >= $gs->evb_limit && $customer->balance >= $payamonut) {
                            $new_balance = $customer->balance - $request->total;
                            $customer->balance = $new_balance;
                            $customer->update();
                            $payment_status="Completed";
                            $evb_used=$request->total;
                        } else {
                            return redirect()->back()->with('unsuccess', "Not enough balance");
                        }
                    }else{
                        return redirect()->back()->with('unsuccess', "User not found");
                    }
                } else {
                    $payment_method = "Cash On Delivery";
                }

                //order start here here
                $order = new Order;
                $item_name = $gs->title . " Order";
                $item_number = Str::random(4) . time();
                $user=User::find($request->user_id);
                $order['user_id'] = $request->user_id;
                $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
                $order['totalQty'] = $total_qty;
                $order['pay_amount'] =round($payamonut/$curr->value,2);
                $order['evb_used'] = isset($evb_used) ? $evb_used : 0;
                $order['method'] = $payment_method;
                $order['shipping'] = "shipto";
                $order['pickup_location'] = $request->shipping_city;
                $order['customer_email'] = $user->email??'';
                $order['customer_name'] = $user->name;
                $order['shipping_cost'] = $request->shipping_cost??0;
                $order['packing_cost'] = $request->packing_cost??0;
                $order['tax'] = $request->tax ?? 0;
                $order['customer_phone'] = $user->phone;
                $order['order_number'] = Str::random(4) . time();
                $order['customer_address'] = $user->present_add??"";
                $order['customer_country'] = $user->country??"";
                $order['customer_city'] = $user->city??"";
                $order['customer_zip'] = $user->zip??"";
                $order['shipping_email'] = $request->shipping_email??"";
                $order['shipping_name'] = $request->shipping_name;
                $order['shipping_phone'] = $request->shipping_phone;
                $order['shipping_address'] = $request->shipping_address;
                $order['shipping_country'] = $request->shipping_country;
                $order['shipping_city'] = $request->shipping_city??'';
                $order['shipping_zip'] = $request->shipping_zip??'';
                $order['order_note'] = "Order from App";
                $order['coupon_code'] = $coupon?$coupon->code:null;
                $order['coupon_discount'] = $request->coupon_discount;
                $order['dp'] = $request->dp ?? 0;
                $order['payment_status'] = isset($payment_status)?$payment_status:"Pending";
                $order['currency_sign'] = $curr->sign;
                $order['currency_value'] = $curr->value;
//                $order['vendor_shipping_id'] = $request->vendor_shipping_id;
//                $order['vendor_packing_id'] = $request->vendor_packing_id;
//                dd($order);


//                dd($order);
                $order->save();

                $track = new OrderTrack;
                $track->title = 'Pending';
                $track->text = 'You have successfully placed your order.';
                $track->order_id = $order->id;
                $track->save();

                $notification = new Notification;
                $notification->order_id = $order->id;
                $notification->save();


                //if coupon applied
                if($coupon)
                {
//                    $coupon = Coupon::findOrFail($request->coupon_id);
                    $coupon->used++;
                    if($coupon->times != null)
                    {
                        $i = (int)$coupon->times;
                        $i--;
                        $coupon->times = (string)$i;
                    }
                    $coupon->update();

                }

                foreach ($cart->items as $prod) {
                    $x = (string)$prod['size_qty'];
                    if (!empty($x)) {
                        $product = Product::findOrFail($prod['item']['id']);
                        $x = (int)$x;
                        $x = $x - $prod['qty'];
                        $temp = $product->size_qty;
                        $temp[$prod['size_key']] = $x;
                        $temp1 = implode(',', $temp);
                        $product->size_qty = $temp1;
                        $product->update();
                    }
                }

                //previous code of stock
//        foreach ($cart->items as $prod) {
//            $x = (string)$prod['stock'];
//            if ($x != null) {
//
//                $product = Product::findOrFail($prod['item']['id']);
//                $product->stock = $prod['stock'];
//                $product->update();
//                if ($product->stock <= 5) {
//                    $notification = new Notification;
//                    $notification->product_id = $product->id;
//                    $notification->save();
//                }
//            }
//        }

                //new code to update stock
                foreach ($cart->items as $prod) {
                    $x = (string)$prod['stock'];
                    if ($x != null) {

                        $product = Product::findOrFail($prod['item']['id']);
                        $newStock=$product->stock - $prod['qty'] * $product->measurement_unit??1;
                        $product->stock = $newStock;
                        $product->update();
                        if ($product->stock <= 5) {
                            $notification = new Notification;
                            $notification->product_id = $product->id;
                            $notification->save();
                        }
                    }
                }


                $notf = null;

                foreach ($cart->items as $prod) {
                    if ($prod['item']['user_id'] != 0) {
                        $vorder = new VendorOrder;
                        $vorder->order_id = $order->id;
                        $vorder->user_id = $prod['item']['user_id'];
                        $notf[] = $prod['item']['user_id'];
                        $vorder->qty = $prod['qty'];
                        $vorder->price = $prod['price'];
                        $vorder->order_number = $order->order_number;
                        $vorder->save();
                    }

                }

                if (!empty($notf)) {
                    $users = array_unique($notf);
                    foreach ($users as $user) {
                        $notification = new UserNotification;
                        $notification->user_id = $user;
                        $notification->order_number = $order->order_number;
                        $notification->save();
                    }
                }
                //Sending Email To Buyer
                //removed as user has no email


                //Sending Email To Admin
                if ($gs->is_smtp == 1) {

                    $data = [
                        'to' => $gs->email,
                        'subject' => "New Order Recieved!!",
                        'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
                    ];

                    $mailer = new GeniusMailer();
                    $mailer->sendCustomMail($data);
                } else {
                    $to = $gs->email;
                    $subject = "New Order Recieved!!";
                    $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is " . $order->order_number . ".Please login to your panel to check. \nThank you.";
                    $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                    mail($to, $subject, $msg, $headers);
                }
                return response('success! order placed successfully');
            }else{
                return response('Unauthorized',401);
            }
        }else{
            return response('Forbidden',403);
        }
    }

    public function orders(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        if ($api_key != null && $partner_key !=null) {
            if ($api_key == $partner_key) {
                $user = $request->user_id;
//                dd($user);
                $orders = Order::query()
                    ->where('user_id',$user)
                    ->orderBy('id','DESC')
                    ->paginate(15);
                if($orders->isNotEmpty()){
                    return OrderResource::collection($orders);
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

    public function order(Request $request){
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        $curr = Currency::where('is_default', '=', 1)->first();
        if ($api_key != null && $partner_key !=null) {
            if ($api_key == $partner_key) {
                $order_id=$request->order_id;
                $order = Order::find($order_id);
                if($order != null){
                    $cart=[];
                    $cart_details=unserialize(bzdecompress(utf8_decode($order->cart)));

                    foreach ($cart_details->items as $key=>$item){
//                    dd($item);
                        $cart[$key]['name']=$item['item']->name;
                        $cart[$key]['photo']=asset('assets/images/products/'.$item['item']->photo);
                        $cart[$key]['qty']=$item['qty'];
                        $cart[$key]['size']=$item['size'];
                        $cart[$key]['size_key']=$item['size_key'];
                        $cart[$key]['size_qty']=$item['size_qty'];
                        $cart[$key]['size_price']=$item['size_price'];
                        $cart[$key]['color']=$item['color'];
                        $cart[$key]['stock']=$item['stock'];
                        $cart[$key]['price']=round($item['price'] * $curr->value,2);
                    }
                    $order['cart']=$cart;
                    return new OrderDetailsResource($order);
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

    public function cancel_order(Request $request)
    {
        $partner_key=Partner_key();
        $api_key= $request->api_key;
        //check api keys received from users here
        if ($api_key != null && $partner_key !=null) {
            if ($api_key==$partner_key){

                $user = $request->user_id;
                $order_id = $request->order_id;
                $order = Order::query()
                    ->where('id',$order_id)
                    ->where('user_id',$user)
                    ->first();
                if($order && $order->status=='pending'){
                    $order->status='cancelled';
                    $order->update();
                    return response('order cancelled successfully',200);
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
