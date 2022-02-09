<?php

namespace App\Http\Controllers;


use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use  App\Models\User;
use App\Models\Log;


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
            $logs->table_name='users';
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
            $logs_1->table_name='roles';
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

}
