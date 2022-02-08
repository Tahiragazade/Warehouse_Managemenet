<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRole;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseRoleController extends Controller
{
    public function index(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $offset = ($page - 1) * $limit;
        $count=WarehouseRole::count();

        $result=WarehouseRole::query()->limit($limit)->offset($offset)->get();


        return response()->json(['data' => $result, 'total' => $count]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id'=>['required','integer'],
            'user_id'=>['required','integer']

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new WarehouseRole();
        $model->warehouse_id=$request->warehouse_id;
        $model->user_id=$request->user_id;
        $model->save();

        return response()->json(['data'=>$model]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>['required','integer'],

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= WarehouseRole::find($request->id);
        $model->warehouse_id=$request->warehouse_id;
        $model->user_id=$request->user_id;
        $model->save();

        return response()->json(['data'=>$model]);
    }
    public function delete(Request $request)
    {
//        $used=WarehouseTransaction::query()
//            ->select(['*'
//            ])
//            ->where('category_id', $request->id)
//            ->get();
//        $roles=WarehouseRole::find($request->id);
//        if(count($used)==0 && !empty($roles))
//        {
//            $roles->delete();
//            return response()->json(['message'=>$roles->id.' has been deleted']);
//        }
//        elseif(count($used)>0)
//        {
//            return response()->json(['message'=>$roles->id.' can not be deleted'],400);
//        }
//        else
//        {
//            return response()->json(['message'=>'id number '.$request->id.' not found'],404);
//        }
    }
    public function single(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>['integer'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        $model=WarehouseRole::find($request->id);

        return response()->json(['data'=>$model]);
    }
}
