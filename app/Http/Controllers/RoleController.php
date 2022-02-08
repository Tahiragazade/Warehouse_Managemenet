<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Role;
use App\Models\WarehouseRole;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
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
        $count=Role::count();

        $result=Role::query()->limit($limit)->offset($offset)->get();


        return response()->json(['data' => $result, 'total' => $count]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string'],

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new Role();
        $model->name=$request->name;
        $model->save();

        $logs= new Log();
        $logs->table_name='Role';
        $logs->record_id=$model->id;
        $logs->action='create';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>['required','integer'],
            'name'=>['required','string']

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= Role::find($request->id);
        $model->name=$request->name;
        $model->save();

        $logs= new Log();
        $logs->table_name='Role';
        $logs->record_id=$request->id;
        $logs->action='update';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }
    public function delete($id)
    {

        $roles=Role::find($id);
        if( !empty($roles))
        {
            $roles->delete();
            $logs= new Log();
            $logs->table_name='Roles';
            $logs->record_id=$id;
            $logs->action='delete';
            $logs->created_by=Auth::id();
            $logs->save();

            $logs= new Log();
            $logs->table_name='Role';
            $logs->record_id=$id;
            $logs->action='delete';
            $logs->created_by=Auth::id();
            $logs->save();
            return response()->json(['message'=>$roles->id.' has been deleted']);

        }
        else
        {
            return response()->json(['message'=>'id number '.$id.' not found'],404);
        }
    }
    public function single($id)
    {
        $model=Role::find($id);

        return response()->json(['data'=>$model]);
    }
}