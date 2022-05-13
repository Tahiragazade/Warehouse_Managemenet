<?php

namespace App\Http\Controllers;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{



    //Add this method to the Controller class
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }

//    public function test() {
//        $myUser = new \stdClass();
//        $myUser->role = \UserRoles::SYSTEM_OWNER;
//
//        if($myUser->role === \UserRoles::SYSTEM_OWNER) {
//            return true;
//        }
//
//        $query = $a->where('role', \UserRoles::OPERATOR)->get();
//    }
}
