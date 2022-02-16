<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

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
        $userRoleQuery = UserRole::query()
            ->select('users.name as user_name','roles.name as role_name','warehouses.name as warehouse_name')
            ->leftJoin('roles','user_roles.role_id','=','roles.id')
            ->leftJoin('users','user_roles.user_id','=','users.id')
            ->leftJoin('warehouses','user_roles.warehouse_id','=','warehouses.id');
        if($request->has('name'))
        {
            $userRoleQuery->where('users.name','like','%'.$request->get('name').'%');
        }
        if($request->has('role'))
        {
            $userRoleQuery->where('roles.name','like','%'.$request->get('role').'%');
        }
        if($request->has('wh_name'))
        {
            $userRoleQuery->where('warehouses.name','like','%'.$request->get('wh_name').'%');
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($userRoleQuery->get());
            $roles = $userRoleQuery->limit($limit)->offset($offset)->get();
        }
        else
        {
            $count = count($userRoleQuery->get());
            $roles = $userRoleQuery->get();
        }

        return response()->json(['data' => $roles, 'total' => $count]);
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

        $logs= new Log();
        $logs->table_name='userRole';
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

        $logs= new Log();
        $logs->table_name='UserRole';
        $logs->record_id=$request->id;
        $logs->action='update';
        $logs->created_by=Auth::id();
        $logs->save();
        return response()->json(['data'=>$model]);
    }
    public function delete($id)
    {
        $roles=UserRole::find($id);
        if( !empty($roles))
        {
            $roles->delete();

            $logs= new Log();
            $logs->table_name='UserRole';
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
        $model=UserRole::find($id);

        return response()->json(['data'=>$model]);
    }
}
