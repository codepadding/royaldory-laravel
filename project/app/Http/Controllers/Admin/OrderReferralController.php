<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyOrderReferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class OrderReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = MonthlyOrderReferral::query()->get();
        //--- Integrating This Collection Into Datatables
        return DataTables::of($datas)
            ->addColumn('status', function(MonthlyOrderReferral $data) {
                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-mor-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Activated</option><<option data-val="0" value="'. route('admin-mor-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>Deactivated</option>/select></div>';
            })
            ->addColumn('action', function(MonthlyOrderReferral $data) {
                return '<div class="action-list"><a href="' . route('admin-mor-edit',$data->id) . '"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-mor-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['status','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.mor.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.mor.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'range_from' => 'unique:monthly_order_referrals',
            'range_to' => 'unique:monthly_order_referrals',
            'amount' => 'integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new MonthlyOrderReferral();
        $input = $request->all();

        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.'.'<a href="'.route("admin-mor-index").'">View Ranges</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $data = MonthlyOrderReferral::findOrFail($id);
        return view('admin.mor.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section

        $rules = [
            'range_from' => 'unique:monthly_order_referrals,range_from,'.$id,
            'range_to' => 'unique:monthly_order_referrals,range_to,'.$id,
            'amount' => 'integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = MonthlyOrderReferral::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin-mor-index").'">View Ranges</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
    //*** GET Request Status
    public function status($id1,$id2)
    {
        $data = MonthlyOrderReferral::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }


    //*** GET Request Delete
    public function destroy($id)
    {
        $data = MonthlyOrderReferral::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
