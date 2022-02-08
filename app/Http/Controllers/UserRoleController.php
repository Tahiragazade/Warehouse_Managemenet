<?php

namespace App\Http\Controllers;

use App\Models\UserRole;
use App\Models\WarehouseRole;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
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
        $count=UserRole::count();

        $result=UserRole::query()->limit($limit)->offset($offset)->get();


        return response()->json(['data' => $result, 'total' => $count]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id'=>['required','integer'],
            'user_id'=>['required','integer']

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new UserRole();
        $model->role_id=$request->role_id;
        $model->user_id=$request->user_id;
        $model->warehouse_id=$request->warehouse_id;
        $model->created_by=Auth::id();
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

        $model= UserRole::find($request->id);
        $model->role_id=$request->role_id;
        $model->user_id=$request->user_id;
        $model->warehouse_id=$request->warehouse_id;
        $model->save();

        return response()->json(['data'=>$model]);
    }
    public function delete($id)
    {
        $roles=UserRole::find($id);
        if( !empty($roles))
        {
            $roles->delete();
            return response()->json(['message'=>$roles->id.' has been deleted']);
        }
        else
        {
            return response()->json(['message'=>'id number '.$id.' not found'],404);
        }
    }
    public function single($id)
    {
        $model=UserRole::find($id);

        return response()->json(['data'=>$model]);
    }
}
