<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
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
        $count=Category::count();

        $results=Category::query()->limit($limit)->offset($offset)->get();
        foreach ($results as $result) {
            if (!empty($result->parent_id)) {
                $result->parent_id=Category::find($result->parent_id)->name;
        }
        }
        return response()->json(['data' => $results, 'total' => $count]);
    }

    public function single($id)
    {

        $model=Category::query()
            ->select('*')
            ->where('id', $id)
            ->get();
        return response()->json(['data'=>$model]);
    }
}
