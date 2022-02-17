<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Role;
use App\Models\UserRole;
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
        $roleQuery = Role::query();

        if($request->has('name')) {
            $roleQuery->where('name', 'like', '%'.$request->get('name').'%');
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($roleQuery->get());
            $roles = $roleQuery->limit($limit)->offset($offset)->get();
        }
        else{
            $count = count($roleQuery->get());
            $roles = $roleQuery->get();

        }

        return response()->json(['data' => $roles, 'total' => $count]);
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
        $user_roles=UserRole::query()
            ->select(['*'])
            ->where('role_id',$id)
            ->count();
        $roles=Role::find($id);
        if($user_roles<=0 && !empty($roles))
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
        elseif ($user_roles>0)
        {
            return response()->json(['message'=>$roles->id.' can not be deleted']);
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
    public function dropdown(){
        $datas=Role::all();
        $dropdown=GenerateDropdownTree($datas);
        return response()->json(['data'=>$dropdown]);

    }
}
