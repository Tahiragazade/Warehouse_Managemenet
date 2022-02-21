<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;

class UserController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the authenticated User.
     *
     * @return Response
     */
    public function profile()
    {

        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * Get all User.
     *
     * @return Response
     */
    public function allUsers(Request $request)
    {
        $userQuery = User::query()
            ->select('users.id as user_id','users.name as user_name','users.email as user_email','roles.name as role_name')
            ->leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->leftJoin('roles','user_roles.role_id','=','roles.id');


        if($request->has('name')) {
            $userQuery->where('roles.name', 'like', '%'.$request->get('name').'%');
        }
        if($request->has('email')) {
            $userQuery->where('email', 'like', '%'.$request->get('email').'%');
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($userQuery->get());
            $users = $userQuery->limit($limit)->offset($offset)->get();
        }
        else{
            $count = count($userQuery->get());
            $users = $userQuery->get();

        }

        return response()->json(['data' => $users, 'total' => $count]);

    }

    /**
     * Get one user.
     *
     * @return Response
     */
    public function singleUser($id)
    {

        try {
            $model = User::query()
                ->select('users.id as id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'roles.name as user_role',
                    'warehouses.name as warehouse_name')
                ->where('users.id','=',$id)
                ->leftJoin('user_roles', 'user_id','=','users.id')
                ->leftJoin('roles','roles.id','=','user_roles.role_id')
                ->leftJoin('warehouses','warehouses.id','=','user_roles.warehouse_id')
                ->first();
            return response()->json($model);

        } catch (\Exception $e) {

            return response()->json(['message' =>'user not found!'], 404);
        }

    }
    public function dropdown(){
        $datas=User::all();
        $dropdown=GenerateDropdownTree($datas);
        return response()->json(['data'=>$dropdown]);

    }

}
