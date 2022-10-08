<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AppSliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = AppSlider::orderBy('id','desc')->get();
        //--- Integrating This Collection Into Datatables
        return DataTables::of($datas)
            ->editColumn('photo', function(AppSlider $data) {
                $photo = $data->photo ? url('assets/images/appsliders/'.$data->photo):url('assets/images/noimage.png');
                return '<img src="' . $photo . '" alt="Image">';
            })
            ->editColumn('title', function(AppSlider $data) {
                $title = strlen(strip_tags($data->title)) > 250 ? substr(strip_tags($data->title),0,250).'...' : strip_tags($data->title);
                return  $title;
            })
            ->addColumn('action', function(AppSlider $data) {
                return '<div class="action-list"><a href="' . route('app-sl-edit',$data->id) . '"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('app-sl-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['photo', 'action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.appslider.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.appslider.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'photo'      => 'required|mimes:jpeg,jpg,png,svg',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new AppSlider();
        $input = $request->all();
//        dd($input);
        if ($file = $request->file('photo'))
        {
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/appsliders',$name);
            $input['photo'] = $name;
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
        $data = AppSlider::findOrFail($id);
        return view('admin.appslider.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'photo'      => 'mimes:jpeg,jpg,png,svg',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = AppSlider::findOrFail($id);
        $input = $request->all();

        if ($file = $request->file('photo'))
        {
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/appsliders',$name);
            if($data->photo != null)
            {
                if (file_exists(public_path().'/assets/images/appsliders/'.$data->photo)) {
                    unlink(public_path().'/assets/images/appsliders/'.$data->photo);
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

    //*** GET Request Delete
    public function destroy($id)
    {
        $data = AppSlider::findOrFail($id);
        //If Photo Doesn't Exist
        if($data->photo == null){
            $data->delete();
            //--- Redirect Section
            $msg = 'Data Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //If Photo Exist
        if (file_exists(public_path().'/assets/images/appsliders/'.$data->photo)) {
            unlink(public_path().'/assets/images/appsliders/'.$data->photo);
        }
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
