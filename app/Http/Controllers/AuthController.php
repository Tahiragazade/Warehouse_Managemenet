<?php

namespace App\Http\Controllers;


use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use  App\Models\User;
use App\Models\Log;
use Illuminate\Validation\Rule;


class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role_id' => 'required|integer',

        ]);

        try {

            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->created_by=Auth::id();

            $user->save();
            //create logs
            $logs= new Log();
            $logs->table_name='User';
            $logs->record_id=$user->id;
            $logs->action='create';
            $logs->created_by=Auth::id();
            $logs->save();

            //add roles to use while creating user
            $userRole= new UserRole();
            $userRole->user_id=$user->id;
            $userRole->role_id=$request->input('role_id');
            $userRole->created_by=Auth::id();
            $userRole->warehouse_id=$request->input('warehouse_id');
            $userRole->save();

            //create logs
            $logs_1= new Log();
            $logs_1->table_name='UserRole';
            $logs_1->record_id=$userRole->id;
            $logs_1->action='create';
            $logs_1->created_by=Auth::id();
            $logs_1->save();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);


        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }


        public function login(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([ 'message' => 'Logged Out'], 200);
    }

    public function update($id,Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required|string',
            'email'=>['string',Rule::unique('users')->ignore($id)],
            'role_id' => 'required|integer',

        ]);

        try {

            $user =User::find($request->id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            if($request->has('password')) {
                $plainPassword = $request->input('password');
                $user->password = app('hash')->make($plainPassword);
            }

            $user->created_by=Auth::id();

            $user->save();
            //create logs
            $logs= new Log();
            $logs->table_name='User';
            $logs->record_id=$user->id;
            $logs->action='update';
            $logs->created_by=Auth::id();
            $logs->save();


            $user_roles=UserRole::query()
                ->where('user_id','=',$request->id)->first();
            $user_roles->role_id=$request->role_id;
            if($request->has('warehouse_id'))
            {
                $user_roles->warehouse_id=$request->warehouse_id;
            }
            $user_roles->save();

            $logs_2= new Log();
            $logs_2->table_name='UserRole';
            $logs_2->record_id=$user_roles->id;
            $logs_2->action='Update';
            $logs_2->created_by=Auth::id();
            $logs_2->save();

            $userRole= new UserRole();
            $userRole->user_id=$id;
            $userRole->role_id=$request->input('role_id');
            $userRole->created_by=Auth::id();
            $userRole->warehouse_id=$request->input('warehouse_id');
            $userRole->save();

            //create logs
            $logs_1= new Log();
            $logs_1->table_name='UserRole';
            $logs_1->record_id=$userRole->id;
            $logs_1->action='create';
            $logs_1->created_by=Auth::id();
            $logs_1->save();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'Updated'], 201);


        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Update Failed!'], 409);
        }
    }


}
