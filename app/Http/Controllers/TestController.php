<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
   public function get(){
       $categories=Category::all();
       $a=1;
       $category=sum($a);
       return $category;
   }
   public function post(Request $request){
//$store_id=$request->store_id;
$user_id=$request->user_id;
$test="checkAdmin($user_id)";
return $test;
   }
}
