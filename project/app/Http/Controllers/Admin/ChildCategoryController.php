<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;
use Image;

class ChildCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = Childcategory::orderBy('id', 'desc')->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('category', function (Childcategory $data) {
                return $data->subcategory->category->name;
            })
            ->addColumn('subcategory', function (Childcategory $data) {
                return $data->subcategory->name;
            })
            ->editColumn('name', function (Childcategory $data) {
                return $data->getTranslation('name', 'en');
            })
            ->addColumn('name_bn', function (Childcategory $data) {
                return $data->getTranslation('name', 'bn');
            })
            ->addColumn('status', function (Childcategory $data) {
                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                return '<div class="action-list"><select class="process select droplinks ' . $class . '"><option data-val="1" value="' . route('admin-childcat-status', ['id1' => $data->id, 'id2' => 1]) . '" ' . $s . '>Activated</option><option data-val="0" value="' . route('admin-childcat-status', ['id1' => $data->id, 'id2' => 0]) . '" ' . $ns . '>Deactivated</option>/select></div>';
            })
            ->addColumn('attributes', function (Childcategory $data) {
                $buttons = '<div class="action-list"><a data-href="' . route('admin-attr-createForChildcategory', $data->id) . '" class="attribute" data-toggle="modal" data-target="#attribute"> <i class="fas fa-edit"></i>Create</a>';
                if ($data->attributes()->count() > 0) {
                    $buttons .= '<a href="' . route('admin-attr-manage', $data->id) . '?type=childcategory' . '" class="edit"> <i class="fas fa-edit"></i>Manage</a>';
                }
                $buttons .= '</div>';

                return $buttons;
            })
            ->addColumn('action', function (Childcategory $data) {
                return '<div class="action-list"><a data-href="' . route('admin-childcat-edit', $data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-childcat-delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['status', 'attributes', 'action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }


    //*** GET Request
    public function index()
    {
        return view('admin.childcategory.index');
    }

    //*** GET Request
    public function create()
    {
        $cats = Category::all();
        return view('admin.childcategory.create', compact('cats'));
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'photo' => 'mimes:jpeg,jpg,png,svg',
            'slug' => 'unique:childcategories|regex:/^[a-zA-Z0-9\s-]+$/'
        ];
        $customs = [
            'photo.mimes' => 'Icon Type is Invalid.',
            'slug.unique' => 'This slug has already been taken.',
            'slug.regex' => 'Slug Must Not Have Any Special Characters.'
        ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Childcategory();
        $data->setTranslations('name',['en' => $request->name,'bn' => $request->name_bn]);
        $input = $request->except('name','name_bn');
        if ($file = $request->file('photo')) {
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images/childcategories/originals/', $name);

            $save_path = public_path('assets/images/childcategories/');
            $img = Image::make(public_path() . '/assets/images/childcategories/originals/' . $name)->fit(300)->encode('jpg', 75);
            $img->save($save_path . $name);

            $input['photo'] = $name;


            // Set App images to 150px
            $save_path = public_path('assets/images/childcategories/app/');
            if (!file_exists($save_path)) {
                mkdir($save_path, 666, true);
            }
            $img = Image::make(public_path() . '/assets/images/childcategories/' . $name)->fit(150)->encode('jpg', 75);
            $img->save($save_path . $name);
        }

        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $cats = Category::all();
        $subcats = Subcategory::all();
        $data = Childcategory::findOrFail($id);
        return view('admin.childcategory.edit', compact('data', 'cats', 'subcats'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'photo' => 'mimes:jpeg,jpg,png,svg',
            'slug' => 'unique:childcategories,slug,' . $id . '|regex:/^[a-zA-Z0-9\s-]+$/'
        ];

        $customs = [
            'photo.mimes' => 'Icon Type is Invalid.',
            'slug.unique' => 'This slug has already been taken.',
            'slug.regex' => 'Slug Must Not Have Any Special Characters.'
        ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = Childcategory::findOrFail($id);
        $data->setTranslations('name',['en' => $request->name,'bn' => $request->name_bn]);
        $input = $request->except('name','name_bn');

        if ($file = $request->file('photo')) {
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images/childcategories/originals/', $name);

            $save_path = public_path('assets/images/childcategories/');
            $img = Image::make(public_path() . '/assets/images/childcategories/originals/' . $name)->fit(300)->encode('jpg', 75);
            $img->save($save_path . $name);

            $input['photo'] = $name;


            // Set App images to 150px
            $save_path = public_path('assets/images/childcategories/app/');
            if (!file_exists($save_path)) {
                mkdir($save_path, 666, true);
            }
            $img = Image::make(public_path() . '/assets/images/childcategories/' . $name)->fit(150)->encode('jpg', 75);
            $img->save($save_path . $name);


            //unlinking existing photo
            if ($data->photo != null) {
                if (file_exists(public_path() . '/assets/images/childcategories/originals/' . $data->photo)) {
                    unlink(public_path() . '/assets/images/childcategories/originals/' . $data->photo);
                }

                if (file_exists(public_path() . '/assets/images/childcategories/' . $data->photo)) {
                    unlink(public_path() . '/assets/images/childcategories/' . $data->photo);
                }

                if (file_exists(public_path() . '/assets/images/childcategories/app/' . $data->photo)) {
                    unlink(public_path() . '/assets/images/childcategories/app/' . $data->photo);
                }
            }
            $input['photo'] = $name;
        }

        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request Status
    public function status($id1, $id2)
    {
        $data = Childcategory::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }

    //*** GET Request
    public function load($id)
    {
        $subcat = Subcategory::findOrFail($id);
        return view('load.childcategory', compact('subcat'));
    }


    //*** GET Request Delete
    public function destroy($id)
    {
        $data = Childcategory::findOrFail($id);

        if ($data->attributes->count() > 0) {
            //--- Redirect Section
            $msg = 'Remove the Attributes first !';
            return response()->json($msg);
            //--- Redirect Section Ends
        }

        if ($data->products->count() > 0) {
            //--- Redirect Section
            $msg = 'Remove the products first !';
            return response()->json($msg);
            //--- Redirect Section Ends
        }

        if ($data->photo != null) {
            if (file_exists(public_path() . '/assets/images/childcategories/originals/' . $data->photo)) {
                unlink(public_path() . '/assets/images/childcategories/originals/' . $data->photo);
            }

            if (file_exists(public_path() . '/assets/images/childcategories/' . $data->photo)) {
                unlink(public_path() . '/assets/images/childcategories/' . $data->photo);
            }

            if (file_exists(public_path() . '/assets/images/childcategories/app/' . $data->photo)) {
                unlink(public_path() . '/assets/images/childcategories/app/' . $data->photo);
            }
        }

        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
