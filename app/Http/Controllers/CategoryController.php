<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Log;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:categories'],
            //'parent_id'=>['integer']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new Category();
        $model->name=$request->name;
        $model->parent_id=$request->parent_id;
        $model->save();

        $logs= new Log();
        $logs->table_name='Category';
        $logs->record_id=$model->id;
        $logs->action='create';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }
    public static function getCategoryDropdown()
    {
        $categories=Category::all();
        $category = GenerateCategorySelectTree($categories);
        clearEmptyChildren($category);
        return $category;
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:categories'],

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= Category::find($request->id);
        $model->name=$request->name;
        $model->parent_id=$request->parent_id;
        $model->save();

        $logs= new Log();
        $logs->table_name='Category';
        $logs->record_id=$model->id;
        $logs->action='update';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }
    public function delete($id)
    {
        $products=Product::query()
            ->select(['*'
            ])
            ->where('category_id', $id)
            ->get();
        $category=Category::find($id);
        if(count($products)==0 && !empty($category))
        {
            $category->delete();
            return response()->json(['message'=>$category->name.' has been deleted']);
            $logs= new Log();
            $logs->table_name='Category';
            $logs->record_id=$id;
            $logs->action='delete';
            $logs->created_by=Auth::id();
            $logs->save();
        }
        elseif(count($products)>0)
        {
            return response()->json(['message'=>$category->name.' can not be deleted'],400);
        }
        else
        {
            return response()->json(['message'=>'id number '.$id.' not found'],404);
        }
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
