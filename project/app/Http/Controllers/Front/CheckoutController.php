<?php

namespace App\Http\Controllers\Front;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderTrack;
use App\Models\Pagesetting;
use App\Models\PaymentGateway;
use App\Models\Pickup;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\VendorOrder;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Session;
use App\Library\SslCommerz\SslCommerzNotification;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function loadpayment($slug1, $slug2)
    {
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $payment = $slug1;
        $pay_id = $slug2;
        $gateway = '';
        if ($pay_id != 0) {
            $gateway = PaymentGateway::findOrFail($pay_id);
        }
        return view('load.payment', compact('payment', 'pay_id', 'gateway', 'curr'));
    }

    public function checkout()
    {
        $this->code_image();
        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success', "You don't have any product to checkout.");
        }
        $gs = Generalsetting::findOrFail(1);
        $dp = 1;
        $vendor_shipping_id = 0;
        $vendor_packing_id = 0;
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }

// If a user is Authenticated then there is no problm user can go for checkout
//            dd(Session::get('cart')->totalPrice * $curr->value);

        //minimum order validation
        if(Session::has('cart')){
            $carttotalprice=Session::get('cart')->totalPrice * $curr->value;
            if($gs->is_min_order=1 && $carttotalprice<$gs->min_order_limit){
                return redirect()->route('front.cart')->with('unsuccess', "Minimum order limit is $gs->min_order_limit ৳");
            }
        }

        if (Auth::guard('web')->check()) {
            $gateways = PaymentGateway::where('status', '=', 1)->get();
            $pickups = Pickup::all();
            $oldCart = Session::get('cart');
            $cart = new Cart($oldCart);
            $products = $cart->items;
            // Shipping Method

            if ($gs->multiple_shipping == 1) {
                $user = null;
                foreach ($cart->items as $prod) {
                    $user[] = $prod['item']['user_id'];
                }
                $users = array_unique($user);
                if (count($users) == 1) {

                    $shipping_data = DB::table('shippings')->where('user_id', '=', $users[0])->get();
                    if (count($shipping_data) == 0) {
                        $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                    } else {
                        $vendor_shipping_id = $users[0];
                    }
                } else {
                    $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                }

            } else {
                $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
            }

            // Packaging

            if ($gs->multiple_packaging == 1) {
                $user = null;
                foreach ($cart->items as $prod) {
                    $user[] = $prod['item']['user_id'];
                }
                $users = array_unique($user);
                if (count($users) == 1) {
                    $package_data = DB::table('packages')->where('user_id', '=', $users[0])->get();
                    if (count($package_data) == 0) {
                        $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                    } else {
                        $vendor_packing_id = $users[0];
                    }
                } else {
                    $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                }

            } else {
                $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
            }


            foreach ($products as $prod) {
                if ($prod['item']['type'] == 'Physical') {
                    $dp = 0;
                    break;
                }
            }
            if ($dp == 1) {
                $ship = 0;
            }
            $total = $cart->totalPrice;
            $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
            if ($gs->tax != 0) {
                $tax = ($total / 100) * $gs->tax;
                $total = $total + $tax;
            }
            if (!Session::has('coupon_total')) {
                $total = $total - $coupon;
                $total = $total + 0;
            } else {
                // $total = Session::get('coupon_total');
                //added later to exclude currency sign
                $total = preg_replace('/[^0-9\.]/', '', Session::get('coupon_total'));
                $total = $total + round(0 * $curr->value, 2);
            }
            return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'digital' => $dp, 'curr' => $curr, 'shipping_data' => $shipping_data, 'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
        } else {
// If guest checkout is activated then user can go for checkout
            if ($gs->guest_checkout == 1) {
                $gateways = PaymentGateway::where('status', '=', 1)->get();
                $pickups = Pickup::all();
                $oldCart = Session::get('cart');
                $cart = new Cart($oldCart);
                $products = $cart->items;

                // Shipping Method

                if ($gs->multiple_shipping == 1) {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if (count($users) == 1) {
                        $shipping_data = DB::table('shippings')->where('user_id', '=', $users[0])->get();

                        if (count($shipping_data) == 0) {
                            $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                        } else {
                            $vendor_shipping_id = $users[0];
                        }
                    } else {
                        $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                    }

                } else {
                    $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                }

                // Packaging

                if ($gs->multiple_packaging == 1) {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if (count($users) == 1) {
                        $package_data = DB::table('packages')->where('user_id', '=', $users[0])->get();

                        if (count($package_data) == 0) {
                            $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                        } else {
                            $vendor_packing_id = $users[0];
                        }
                    } else {
                        $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                    }

                } else {
                    $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                }


                foreach ($products as $prod) {
                    if ($prod['item']['type'] == 'Physical') {
                        $dp = 0;
                        break;
                    }
                }
                if ($dp == 1) {
                    $ship = 0;
                }
                $total = $cart->totalPrice;
                $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
                if ($gs->tax != 0) {
                    $tax = ($total / 100) * $gs->tax;
                    $total = $total + $tax;
                }
                if (!Session::has('coupon_total')) {
                    $total = $total - $coupon;
                    $total = $total + 0;
                } else {
                    $total = Session::get('coupon_total');
                    $total = str_replace($curr->sign, '', $total) + round(0 * $curr->value, 2);
                }
                foreach ($products as $prod) {
                    if ($prod['item']['type'] != 'Physical') {
                        if (!Auth::guard('web')->check()) {
                            $ck = 1;
                            return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'checked' => $ck, 'digital' => $dp, 'curr' => $curr, 'shipping_data' => $shipping_data, 'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
                        }
                    }
                }
                return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'digital' => $dp, 'curr' => $curr, 'shipping_data' => $shipping_data, 'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
            } // If guest checkout is Deactivated then display pop up form with proper error message

            else {
                $gateways = PaymentGateway::where('status', '=', 1)->get();
                $pickups = Pickup::all();
                $oldCart = Session::get('cart');
                $cart = new Cart($oldCart);
                $products = $cart->items;

                // Shipping Method

                if ($gs->multiple_shipping == 1) {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if (count($users) == 1) {
                        $shipping_data = DB::table('shippings')->where('user_id', '=', $users[0])->get();

                        if (count($shipping_data) == 0) {
                            $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                        } else {
                            $vendor_shipping_id = $users[0];
                        }
                    } else {
                        $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                    }

                } else {
                    $shipping_data = DB::table('shippings')->where('user_id', '=', 0)->get();
                }

                // Packaging

                if ($gs->multiple_packaging == 1) {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if (count($users) == 1) {
                        $package_data = DB::table('packages')->where('user_id', '=', $users[0])->get();

                        if (count($package_data) == 0) {
                            $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                        } else {
                            $vendor_packing_id = $users[0];
                        }
                    } else {
                        $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                    }

                } else {
                    $package_data = DB::table('packages')->where('user_id', '=', 0)->get();
                }


                $total = $cart->totalPrice;
                $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
                if ($gs->tax != 0) {
                    $tax = ($total / 100) * $gs->tax;
                    $total = $total + $tax;
                }
                if (!Session::has('coupon_total')) {
                    $total = $total - $coupon;
                    $total = $total + 0;
                } else {
                    $total = Session::get('coupon_total');
                    $total = $total + round(0 * $curr->value, 2);
                }
                $ck = 1;
                return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'checked' => $ck, 'digital' => $dp, 'curr' => $curr, 'shipping_data' => $shipping_data, 'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
            }
        }

    }


    public function cashondelivery(Request $request)
    {

        if ($request->pass_check) {
            $users = User::where('email', '=', $request->personal_email)->get();
            if (count($users) == 0) {
                if ($request->personal_pass == $request->personal_confirm) {
                    $user = new User;
                    $user->name = $request->personal_name;
                    $user->email = $request->personal_email;
                    $user->password = bcrypt($request->personal_pass);
                    $token = md5(time() . $request->personal_name . $request->personal_email);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($request->name . $request->email);
                    $user->emai_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);
                } else {
                    return redirect()->back()->with('unsuccess', "Confirm Password Doesn't Match.");
                }
            } else {
                return redirect()->back()->with('unsuccess', "This Email Already Exist.");
            }
        }


        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success', "You don't have any product to checkout.");
        }
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
//        dd($cart);


        //Order Limit Validation
        if($gs->is_min_order=1){
            $shipping_cost=$request->shipping_cost>0?$request->shipping_cost:0;
            $packing_cost=$request->packing_cost>0?$request->packing_cost:0;
            $ttlPrice=$request->total-$shipping_cost-$packing_cost;
            if($ttlPrice < $gs->min_order_limit){
                return response()->json(['status'=>false,'message'=>"minimum order limit is tk $gs->min_order_limit"]);
            }
        }

        //for Royaldory balance checkout
        $payment_status="Pending";
        if ($request->method == "evb") {
            $payment_method = 'Royaldory Balance';
            $customer = auth()->user();
            if($customer){
                if ($gs->evb_check == 1 && $customer->balance >= $gs->evb_limit && $customer->balance >= $request->total) {
                    $new_balance = $customer->balance - $request->total;
                    $customer->balance = $new_balance;
                    $customer->update();
                    $payment_status="Completed";
                    $evb_used=$request->total;
                } else {
                    return redirect()->back()->with('unsuccess', "Not enough balance");
                }
            }else{
                return redirect()->back()->with('unsuccess', "Not enough balance");
            }
        } else {
            $payment_method = $request->method;
        }


        foreach ($cart->items as $key => $prod) {
            if (!empty($prod['item']['license']) && !empty($prod['item']['license_qty'])) {
                foreach ($prod['item']['license_qty'] as $ttl => $dtl) {
                    if ($dtl != 0) {
                        $dtl--;
                        $produc = Product::findOrFail($prod['item']['id']);
                        $temp = $produc->license_qty;
                        $temp[$ttl] = $dtl;
                        $final = implode(',', $temp);
                        $produc->license_qty = $final;
                        $produc->update();
                        $temp = $produc->license;
                        $license = $temp[$ttl];
                        $oldCart = Session::has('cart') ? Session::get('cart') : null;
                        $cart = new Cart($oldCart);
                        $cart->updateLicense($prod['item']['id'], $license);
                        Session::put('cart', $cart);
                        break;
                    }
                }
            }
        }


        $order = new Order;
        $success_url = action('Front\PaymentController@payreturn');
        $item_name = $gs->title . " Order";
        $item_number = str_random(4) . time();
        $order['user_id'] = $request->user_id;
        $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
        $order['totalQty'] = $request->totalQty;
        $order['pay_amount'] = round($request->total / $curr->value, 2);
        $order['method'] = $payment_method;
        $order['evb_used'] = isset($evb_used) ? $evb_used : 0;
        $order['shipping'] = $request->shipping;
        $order['pickup_location'] = $request->pickup_location;
        $order['customer_email'] = $request->email;
        $order['customer_name'] = $request->name;
        $order['shipping_cost'] = $request->shipping_cost;
        $order['packing_cost'] = $request->packing_cost;
        $order['tax'] = $request->tax;
        $order['customer_phone'] = $request->phone;
        $order['order_number'] = str_random(4) . time();
        $order['customer_address'] = $request->address;
        $order['customer_country'] = $request->customer_country;
        $order['customer_city'] = $request->city;
        $order['customer_zip'] = $request->zip;
        $order['shipping_email'] = $request->shipping_email;
        $order['shipping_name'] = $request->shipping_name;
        $order['shipping_phone'] = $request->shipping_phone;
        $order['shipping_address'] = $request->shipping_address;
        $order['shipping_country'] = $request->shipping_country;
        $order['shipping_city'] = $request->shipping_city;
        $order['shipping_zip'] = $request->shipping_zip;
        $order['order_note'] = $request->order_notes;
        $order['coupon_code'] = $request->coupon_code;
        $order['coupon_discount'] = $request->coupon_discount;
        $order['dp'] = $request->dp;
        $order['payment_status'] = $payment_status;
        $order['currency_sign'] = $curr->sign;
        $order['currency_value'] = $curr->value;
        $order['vendor_shipping_id'] = $request->vendor_shipping_id;
        $order['vendor_packing_id'] = $request->vendor_packing_id;
//        dd($order);
        if (Session::has('affilate')) {
            $val = $request->total / $curr->value;
            $val = $val / 100;
            $sub = $val * $gs->affilate_charge;
            $user = User::findOrFail(Session::get('affilate'));
            $user->affilate_income += $sub;
            $user->update();
            $order['affilate_user'] = $user->name;
            $order['affilate_charge'] = $sub;
        }
        $order->save();

        $track = new OrderTrack;
        $track->title = 'Pending';
        $track->text = 'You have successfully placed your order.';
        $track->order_id = $order->id;
        $track->save();

        $notification = new Notification;
        $notification->order_id = $order->id;
        $notification->save();
        if ($request->coupon_id != "") {
            $coupon = Coupon::findOrFail($request->coupon_id);
            $coupon->used++;
            if ($coupon->times != null) {
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


        foreach ($cart->items as $prod) {
            $x = (string)$prod['stock'];
            if ($x != null) {

                $product = Product::findOrFail($prod['item']['id']);
                $product->stock = $prod['stock'];
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

        Session::put('temporder', $order);
        Session::put('tempcart', $cart);

        Session::forget('cart');

        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');

        //Sending Email To Buyer

        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $request->email,
                'type' => "new_order",
                'cname' => $request->name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMail($data, $order->id);
        } else {
            $to = $request->email;
            $subject = "Your Order Placed!!";
            $msg = "Hello " . $request->name . "!\nYou have placed a new order.\nYour order number is " . $order->order_number . ".Please wait for your delivery. \nThank you.";
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }
        //Sending Email To Admin
        if ($gs->is_smtp == 1) {
            $data = [
                'to' => Pagesetting::find(1)->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {
            $to = Pagesetting::find(1)->contact_email;
            $subject = "New Order Recieved!!";
            $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is " . $order->order_number . ".Please login to your panel to check. \nThank you.";
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }

        return redirect($success_url);
    }

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }
    
    
    public function gateway(Request $request)
    {
        $input = (array) json_decode($request->cart_json);
        info($input);
        $input['tran_id'] = uniqid();
        $rules = [
            'tran_id' => 'required',
        ];


        $messages = [
            'required' => 'The Transaction ID field is required.',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            Session::flash('unsuccess', $validator->messages()->first());
            return redirect()->back()->withInput();
        }

        if (isset($input['pass_check']) && $input['pass_check'] == 1) {
            $users = User::where('email', '=', $input['personal_email'])->get();
            if (count($users) == 0) {
                if ($input['personal_pass'] == $input['personal_confirm_pass']) {
                    $user = new User;
                    $user->name = $input['personal_name'];
                    $user->email = $input['personal_email'];
                    $user->password = bcrypt($input['personal_pass']);
                    $token = md5(time() .$input['personal_name'] . $input['personal_email']);
                    $user->verification_link = $token;
                    $user->affilate_code = md5($input['name'] . $input['email']);
                    $user->email_verified = 'Yes';
                    $user->save();
                    Auth::guard('web')->login($user);
                } else {
                    return redirect()->back()->with('unsuccess', "Confirm Password Doesn't Match.");
                }
            } else {
                return redirect()->back()->with('unsuccess', "This Email Already Exist.");
            }
        }

        $gs = Generalsetting::findOrFail(1);
        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success', "You don't have any product to checkout.");
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
           $curr = Currency::where('is_default', '=', 1)->first();
    }
        foreach ($cart->items as $key => $prod) {
            if (!empty($prod['item']['license']) && !empty($prod['item']['license_qty'])) {
                foreach ($prod['item']['license_qty'] as $ttl => $dtl) {
                    if ($dtl != 0) {
                        $dtl--;
                        $produc = Product::findOrFail($prod['item']['id']);
                        $temp = $produc->license_qty;
                        $temp[$ttl] = $dtl;
                        $final = implode(',', $temp);
                        $produc->license_qty = $final;
                        $produc->update();
                        $temp = $produc->license;
                        $license = $temp[$ttl];
                        $oldCart = Session::has('cart') ? Session::get('cart') : null;
                        $cart = new Cart($oldCart);
                        $cart->updateLicense($prod['item']['id'], $license);
                        Session::put('cart', $cart);
                        break;
                    }
                }
            }
        }
        $settings = Generalsetting::findOrFail(1);
        $order = new Order;
        $item_name = $settings->title . " Order";
        $item_number = str_random(4) . time();
        $order['user_id'] = ($input['user_id'] == '') ? null : $input['user_id'];
        $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
        $order['totalQty'] = $input['totalQty'];
        $order['pay_amount'] = round($input['total'] / $curr->value, 2);
        $order['method'] = $input['method'];
        $order['shipping'] = $input['shipping'];
        $order['pickup_location'] = $input['pickup_location'];
        $order['customer_email'] = $input['email'];
        $order['customer_name'] = $input['name'];
        $order['shipping_cost'] = $input['shipping_cost'];
        $order['packing_cost'] = $input['packing_cost'];
        $order['tax'] = $input['tax'];
        $order['customer_phone'] = $input['phone'];
        $order['order_number'] = str_random(4) . time();
        $order['customer_address'] = $input['address'];
        $order['customer_country'] = $input['customer_country'];
        $order['customer_city'] = $input['city'];
        $order['customer_zip'] = $input['zip'];
        $order['shipping_email'] = ($input['shipping_email'] == '') ? null : $input['shipping_email'];
        $order['shipping_name'] = ($input['shipping_name'] == '') ? null : $input['shipping_name'];
        $order['shipping_phone'] = ($input['shipping_phone'] == '') ? null : $input['shipping_phone'];
        $order['shipping_address'] = ($input['shipping_address'] == '') ? null : $input['shipping_phone'];
        $order['shipping_country'] = ($input['shipping_country'] == '') ? null : $input['shipping_country'];
        $order['shipping_city'] = ($input['shipping_city'] == '') ? null : $input['shipping_city'];
        $order['shipping_zip'] = ($input['shipping_zip'] == '') ? null : $input['shipping_zip'];
        $order['order_note'] = $input['order_notes'];
        $order['txnid'] = $input['tran_id'];
        $order['coupon_code'] = ($input['coupon_code'] == '') ? null : $input['coupon_code'];
        $order['coupon_discount'] = ($input['coupon_discount'] == '') ? null : $input['coupon_code'];
        $order['dp'] = $input['dp'];
        $order['payment_status'] = "pending";
        $order['currency_sign'] = $curr['sign'];
        $order['currency_value'] = $curr['value'];
        $order['vendor_shipping_id'] = $input['vendor_shipping_id'];
        $order['vendor_packing_id'] = $input['vendor_packing_id'];
        if (Session::has('affilate')) {
            $val = $input['total'] / $curr->value;
            $val = $val / 100;
            $sub = $val * $gs->affilate_charge;
            $user = User::findOrFail(Session::get('affilate'));
            $user->affilate_income += $sub;
            $user->update();
            $order['affilate_user'] = $user->name;
            $order['affilate_charge'] = $sub;
        }
        $order->save();

        $track = new OrderTrack;
        $track->title = 'Pending';
        $track->text = 'You have successfully placed your order.';
        $track->order_id = $order->id;
        $track->save();

        $notification = new Notification;
        $notification->order_id = $order->id;
        $notification->save();
        if ($input['coupon_id'] != "") {
            $coupon = Coupon::findOrFail($input['coupon_id']);
            $coupon->used++;
            if ($coupon->times != null) {
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


        foreach ($cart->items as $prod) {
            $x = (string)$prod['stock'];
            if ($x != null) {

                $product = Product::findOrFail($prod['item']['id']);
                $product->stock = $prod['stock'];
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

        // Session::put('temporder', $order);
        // Session::put('tempcart', $cart);
        // Session::forget('cart');
        // Session::forget('already');
        // Session::forget('coupon');
        // Session::forget('coupon_total');
        // Session::forget('coupon_total1');
        // Session::forget('coupon_percentage');


        //Sending Email To Buyer
        if ($gs->is_smtp == 1) {
            $data = [
                'to' => $input['email'],
                'type' => "new_order",
                'cname' => $input['name'],
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMail($data, $order->id);
        } else {
            $to = $input['email'];
            $subject = "Your Order Placed!!";
            $msg = "Hello " . $input['name'] . "!\nYou have placed a new order.\nYour order number is " . $order->order_number . ".Please wait for your delivery. \nThank you.";
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }
        //Sending Email To Admin
        if ($gs->is_smtp == 1) {
            $data = [
                'to' => Pagesetting::find(1)->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {
            $to = Pagesetting::find(1)->contact_email;
            $subject = "New Order Recieved!!";
            $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is " . $order->order_number . ".Please login to your panel to check. \nThank you.";
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }

         $post_data['total_amount'] = round($input['total'] / $curr->value, 2); # You cant not pay less than 10
         $post_data['currency'] = $curr['name'];
        $post_data['tran_id'] = $input['tran_id']; // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $input['name'] ?? '';
        $post_data['cus_email'] = $input['email'] ?? '';
        $post_data['cus_add1'] = $input['address'] ?? '';
        $post_data['cus_add2'] = $input['address'] ?? '';
        $post_data['cus_city'] = $input['city'] ?? '';
        $post_data['cus_state'] = $input['city'] ?? '';
        $post_data['cus_postcode'] = $input['zip'] ?? '';
        $post_data['cus_country'] = $input['customer_country'] ?? 'Bangladesh';
        $post_data['cus_phone'] = $input['phone'] ?? '';
        $post_data['cus_fax'] = $input['phone'] ?? '';

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = ($input['shipping_name'] == '') ? $input['name'] : $input['shipping_name'];
        $post_data['ship_add1'] = ($input['shipping_address'] == '') ? $input['address'] : $input['shipping_address'];
        $post_data['ship_add2'] = ($input['shipping_address'] == '') ? $input['address'] : $input['shipping_address'];
        $post_data['ship_city'] = ($input['shipping_city'] == '') ? $input['city'] : $input['shipping_city'];
        $post_data['ship_state'] = ($input['shipping_city'] == '') ? $input['city'] : $input['shipping_city'];
        $post_data['ship_postcode'] = ($input['shipping_zip'] == '') ? $input['zip'] : $input['shipping_zip'];
        $post_data['ship_phone'] = ($input['shipping_phone'] == '') ? $input['phone'] : $input['shipping_phone'];
        $post_data['ship_country'] = ($input['shipping_country'] == '') ? $input['customer_country'] : $input['shipping_country'];

        $post_data['shipping_method'] = $input['method'];
        $post_data['product_name'] = "NO";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

     

info($post_data);
// $post_data['total_amount'] = '10'; # You cant not pay less than 10
// $post_data['currency'] = "BDT";
// $post_data['tran_id'] = $input['tran_id']; // tran_id must be unique

// # CUSTOMER INFORMATION
// $post_data['cus_name'] = 'Customer Name';
// $post_data['cus_email'] = 'customer@mail.com';
// $post_data['cus_add1'] = 'Customer Address';
// $post_data['cus_add2'] = "";
// $post_data['cus_city'] = "";
// $post_data['cus_state'] = "";
// $post_data['cus_postcode'] = "";
// $post_data['cus_country'] = "Bangladesh";
// $post_data['cus_phone'] = '880137604100';
// $post_data['cus_fax'] = "";

// # SHIPMENT INFORMATION
// $post_data['ship_name'] = "Store Test";
// $post_data['ship_add1'] = "Dhaka";
// $post_data['ship_add2'] = "Dhaka";
// $post_data['ship_city'] = "Dhaka";
// $post_data['ship_state'] = "Dhaka";
// $post_data['ship_postcode'] = "1000";
// $post_data['ship_phone'] = "";
// $post_data['ship_country'] = "Bangladesh";

// $post_data['shipping_method'] = "NO";
// $post_data['product_name'] = "Computer";
// $post_data['product_category'] = "Goods";
// $post_data['product_profile'] = "physical-goods";

// # OPTIONAL PARAMETERS
// $post_data['value_a'] = "ref001";
// $post_data['value_b'] = "ref002";
// $post_data['value_c'] = "ref003";
// $post_data['value_d'] = "ref004";

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }
    }

    public function success(Request $request)
    {
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }

        info($request->all());
        $tran_id = $request->input('tran_id');
        echo "Transaction is Successful.$tran_id";
        $amount = $request->input('amount');
        $currency = $curr;
        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_detials = DB::table('orders')
            ->where('txnid', $tran_id)
            ->select('txnid', 'status', 'pay_amount')->first();
            info('jkhj');
            info($request->all());
            info($tran_id);
            info($amount);
            info($currency);
        if ($order_detials->status == 'pending') {
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $curr['name']);
            info($validation);
            if ($validation == TRUE) {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                */
                $update_product = DB::table('orders')
                    ->where('txnid', $tran_id)
                    ->update(['status' => 'processing']);

                echo "<br >Transaction is successfully Completed";
            } else {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
                Here you need to update order status as Failed in order table.
                */
                $update_product = DB::table('orders')
                    ->where('txnid', $tran_id)
                    ->update(['status' => 'failed']);
                echo "validation Fail";
            }
        } else if ($order_detials->status == 'processing' || $order_detials->status == 'completed') {
            /*
             That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
             */
            echo "Transaction is successfully Completed";
        } else {
            #That means something wrong happened. You can redirect customer to your product page.
            echo "Invalid Transaction";
        }


    }

    // Capcha Code Image
    private function code_image()
    {
        $actual_path = str_replace('project', '', base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 200, 50, $background_color);

        $pixel = imagecolorallocate($image, 0, 0, 255);
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixel);
        }

        $font = $actual_path . 'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length - 1)];
        $word = '';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length = 6;// No. of character in image
        for ($i = 0; $i < $cap_length; $i++) {
            $letter = $allowed_letters[rand(0, $length - 1)];
            imagettftext($image, 25, 1, 35 + ($i * 25), 35, $text_color, $font, $letter);
            $word .= $letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path . "assets/images/capcha_code.png");
    }

}
