<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Warehouse;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $offset = ($page - 1) * $limit;
        $count=Warehouse::count();

        $result=Warehouse::query()->limit($limit)->offset($offset)->get();
        return response()->json(['data' => $result, 'total' => $count]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:warehouses'],
            'types'=>['required', 'integer']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new Warehouse();
        $model->name=$request->name;
        $model->types=$request->types;
        $model->created_by=Auth::id();
        $model->save();

        $logs= new Log();
        $logs->table_name='Warehouse';
        $logs->record_id=$model->id;
        $logs->action='create';
        $logs->created_by=Auth::id();
        $logs->save();
        return response()->json(['data'=>$model]);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:warehouses'],
            'types'=>['required', 'integer']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= Warehouse::find($request->id);
        $model->name=$request->name;
        $model->types=$request->types;
        $model->save();

        $logs= new Log();
        $logs->table_name='Warehouse';
        $logs->record_id=$request->id;
        $logs->action='update';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }
    public function delete($id)
    {
        $transactions=WarehouseTransaction::query()
            ->select(['*'
            ])
            ->where('from_wh_id', $id)
            ->OrWhere('destination_wh_id',$id)
            ->get();
        $warehouse=Warehouse::find($id);
        if(count($transactions)==0 && !empty($warehouse))
        {
            $warehouse->delete();
            $logs= new Log();
            $logs->table_name='Warehouse';
            $logs->record_id=$id;
            $logs->action='delete';
            $logs->created_by=Auth::id();
            $logs->save();
            return response()->json(['message'=>$warehouse->name.' has been deleted']);
        }
        elseif(count($transactions)>0)
        {
            return response()->json(['message'=>$warehouse->name.' can not be deleted'],400);
        }
        else
        {
            return response()->json(['message'=>'id number '.$id.' not found'],404);
        }
    }
    public function single($id)
    {

        $model=Warehouse::query()
            ->select('*')
            ->where('id', $id)
            ->get();
        return response()->json(['data'=>$model]);
    }
}
