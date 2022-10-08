<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PushNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = PushNotification::orderBy('id','desc')->get();
        //--- Integrating This Collection Into Datatables
        return DataTables::of($datas)
            ->addColumn('status', function(PushNotification $data) {
                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-pn-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Activated</option><option data-val="0" value="'. route('admin-pn-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>Deactivated</option>/select></div>';
            })

            ->addColumn('action', function(PushNotification $data) {
                return '<div class="action-list"><a data-href="' . route('admin-pn-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-pn-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['status','attributes','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.push_notification.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.push_notification.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'image' => 'mimes:jpeg,jpg,png,svg',
            'thumb' => 'mimes:jpeg,jpg,png,svg'
        ];
        $customs = [
            'thumb.mimes' => 'Icon Type is Invalid.'
        ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new PushNotification();
        $input = $request->all();
        if ($file = $request->file('thumb'))
        {
            $name = time().rand('1000','9999').'.'.$file->getClientOriginalExtension();
            $file->move('assets/images/notification',$name);
            $input['thumb'] = $name;
        }
        if ($file = $request->file('image'))
        {
            $name = time().rand('1000','9999').'.'.$file->getClientOriginalExtension();
            $file->move('assets/images/notification/images',$name);
            $input['image'] = $name;
        }

        $data->fill($input)->save();

        $userTokens=User::query()
            ->whereNotNull('firebase_client_id')
            ->pluck('firebase_client_id');
        $imageUrl=asset('assets/images/notification/images/'.$data->image);
        $thumbUrl=asset('assets/images/notification/'.$data->thumb);
        sendNotification($data->id, $data->title,$data->sub_title, $data->message, 'multiple', $userTokens,$thumbUrl,$imageUrl);

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $data = PushNotification::findOrFail($id);
        return view('admin.push_notification.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'thumb' => 'nullable|mimes:jpeg,jpg,png,svg',
            'image' => 'nullable|mimes:jpeg,jpg,png,svg',
        ];
        $customs = [
            'thumb.mimes' => 'Thumb Type is Invalid.',
        ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = PushNotification::findOrFail($id);
        $input = $request->all();
        if ($file = $request->file('thumb'))
        {
            $name = time().rand('1000','9999').'.'.$file->getClientOriginalExtension();
            $file->move('assets/images/notification',$name);
            if($data->thumb != null)
            {
                if (file_exists(public_path().'/assets/images/notification/'.$data->thumb)) {
                    unlink(public_path().'/assets/images/notification/'.$data->thumb);
                }
            }
            $input['thumb'] = $name;
        }

        if ($file = $request->file('image'))
        {
            $name = time().rand('1000','9999').'.'.$file->getClientOriginalExtension();
            $file->move('assets/images/notification/images/',$name);
            if($data->image != null)
            {
                if (file_exists(public_path().'assets/images/notification/images/'.$data->image)) {
                    unlink(public_path().'assets/images/notification/images'.$data->image);
                }
            }
            $input['image'] = $name;
        }
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request Status
    public function status($id1,$id2)
    {
        $data = PushNotification::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }


    //*** GET Request Delete
    public function destroy($id)
    {
        $data = PushNotification::findOrFail($id);
        //If Photo Doesn't Exist
        if($data->thumb == null){
            $data->delete();
            //--- Redirect Section
            $msg = 'Data Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //If Photo Exist
        if($data->photo){
            if (file_exists(public_path().'/assets/images/notification/'.$data->photo)) {
                unlink(public_path().'/assets/images/notification/'.$data->photo);
            }
        }
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
