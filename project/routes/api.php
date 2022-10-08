<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(array('prefix'=>'v1'),function (){
    /*********Users Api Start**********/
    Route::post('register','Api\UserController@register');
    Route::post('login','Api\UserController@login');
    Route::post('send_otp','Api\UserController@sendOtp');
    Route::post('check_otp','Api\UserController@checkOtp');
    Route::post('reset_password','Api\UserController@resetPassword');
    Route::post('change_password','Api\UserController@changePassword');
    Route::post('user_update','Api\UserController@update');
    Route::get('get_ref_history','Api\UserController@refHistory');
    /*********Users Api End**********/

    /*********Slider Api Start**********/
    Route::get('get_sliders','Api\SliderController@sliders');
    /*********Slider Api End**********/

    /*********Product Api Start**********/
    Route::get('get_home_contents','Api\AppController@home_contents');
    Route::get('get_products','Api\ProductController@products');
    Route::get('get_flash_products','Api\ProductController@flash_products');
    Route::get('get_recent_products','Api\ProductController@recent_products');
    Route::post('get_single_product','Api\ProductController@product');
    Route::get('search_product','Api\ProductController@search_product');
    /*********Product Api End**********/

    /*********Category Api Start**********/
    Route::post('get_categories','Api\CategoryController@categories');
    /*********Category Api End**********/

    /*********Order Api Start**********/
    Route::post('make_order','Api\OrderController@cashondelivery');
    Route::get('get_orders','Api\OrderController@orders');
    Route::post('get_order_details','Api\OrderController@order');
    Route::get('get_wishlist','Api\WishlistController@get_wishlist');
    Route::post('cancel_order','Api\OrderController@cancel_order');
    /*********Order Api End**********/

    /*********Notification Api start**********/
    Route::get('get_notifications','Api\NotificationController@notifications');
    /*********Notification Api End**********/

    /*********wishlist Api start**********/
    Route::post('add_wishlist','Api\WishlistController@add_wishlist');
    Route::post('remove_wishlist','Api\WishlistController@remove_wishlist');
    Route::get('get_wishlist','Api\WishlistController@get_wishlist');
    /*********wishlist Api End**********/

    /*********shipping Api start**********/
    Route::get('get_shipping_methods','Api\AppController@shipping_methods');
    /*********shipping Api End**********/

    /*********coupon Api start**********/
    Route::post('verify_coupon','Api\AppController@verify_coupon');
    /*********coupon Api End**********/

    /*********pages Api start**********/
    Route::get('get_page_contents','Api\AppController@get_pages');
    /*********pages Api End**********/




//    Route::post('get_sliders','Api\UparzonAppController@sliders')->name('api.sliders');
//    Route::post('make_order','Api\OrderController@cashondelivery')->name('api.make_order');
//    Route::post('get_mall_categories','Api\ProductController@uparzon_categories')->name('api.get_mall_categories');
});
