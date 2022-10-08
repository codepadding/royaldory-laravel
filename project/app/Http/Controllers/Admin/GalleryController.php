<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Product;
use Image;

class GalleryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show()
    {
        $data[0] = 0;
        $id = $_GET['id'];
        $prod = Product::findOrFail($id);
        if (count($prod->galleries)) {
            $data[0] = 1;
            $data[1] = $prod->galleries;
        }
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = null;
        $lastid = $request->product_id;
        if ($files = $request->file('gallery')) {
            foreach ($files as $key => $file) {
                $val = $file->getClientOriginalExtension();
                if ($val == 'jpeg' || $val == 'jpg' || $val == 'png' || $val == 'svg') {
                    $gallery = new Gallery;

                    $thumbnail = time() . str_random(8) . '.jpg';
                    //keeping original photo
                    $file->move('assets/images/galleries/original', $thumbnail);

                    //ratio set to 800px for web gallery
                    $save_path=public_path('assets/images/galleries/');
                    $img = Image::make(public_path().'/assets/images/galleries/original/'.$thumbnail)->fit(800)->encode('jpg',75);
                    $img->save($save_path.$thumbnail);


                    // Set App Gallery to height 200 and aspect ratio
                    $img = Image::make(public_path().'/assets/images/galleries/original/'.$thumbnail)->fit(200)->encode('jpg',75);
                    $save_path=public_path('assets/images/galleries/app/');
                    $img->save($save_path.$thumbnail);
//                    file_put_contents($save_path.$thumbnail,$img);


                    $gallery['photo'] = $thumbnail;
                    $gallery['product_id'] = $lastid;
                    $gallery->save();
                    $data[] = $gallery;
                }
            }
        }
        return response()->json($data);
    }

    public function destroy()
    {

        $id = $_GET['id'];
        $gal = Gallery::findOrFail($id);

        if (file_exists(public_path() . '/assets/images/galleries/original/' . $gal->photo)) {
            unlink(public_path() . '/assets/images/galleries/original/' . $gal->photo);
        }

        if (file_exists(public_path() . '/assets/images/galleries/' . $gal->photo)) {
            unlink(public_path() . '/assets/images/galleries/' . $gal->photo);
        }

        if (file_exists(public_path() . '/assets/images/galleries/app/' . $gal->photo)) {
            unlink(public_path() . '/assets/images/galleries/app/' . $gal->photo);
        }


        $gal->delete();

    }

}
