<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Log;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $categoryQuery = Category::query();

        if($request->has('name')) {
            $categoryQuery->where('name', 'like', '%'.$request->get('name').'%');
        }
        if($request->has('parent_id')) {
            $categoryQuery->where('parent_id', '=', $request->get('parent_id'));
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($categoryQuery->get());
            $categories = $categoryQuery->limit($limit)->offset($offset)->get();

        }
        else
        {
            $count = count($categoryQuery->get());
            $categories = $categoryQuery->get();
        }
        $categoryTree=GenerateCategoryIndex($categories);
        clearEmptyChildren($categoryTree);
        foreach ($categoryTree as $category) {
            if (!empty($category->parent_id)) {
                $category->parent_id=Category::find($category->parent_id)->name;
            }
        }

        return response()->json(['data' => $categoryTree, 'total' => $count]);
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
        $model->created_by=Auth::id();
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
            'name'=>['string', Rule::unique('categories')->ignore($request->id)],

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if($request->parent_id==$request->id)
        {
            return response()->json(['message'=>'Əsas kategoriya və Alt kategoriya eyni ola bilməz']);
        }
        $submodel=Category::where(['parent_id'=>$request->id])->count();
        if($submodel>0)
        {
            return response()->json(['message'=>'You can\'t change it because It has a subCategory'],403);
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
            ->count();
        $parent_id=Category::query()
            ->select(['*'
            ])
            ->where('parent_id', $id)
            ->count();
        $category=Category::find($id);
        if($products<=0 && $parent_id<=0 && !empty($category))
        {
            $category->delete();

            $logs= new Log();
            $logs->table_name='Category';
            $logs->record_id=$id;
            $logs->action='delete';
            $logs->created_by=Auth::id();
            $logs->save();
            return response()->json(['message'=>$category->name.' has been deleted']);
        }
        elseif($products>0||$parent_id>0)
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
            ->first();
        return response()->json($model);
    }
    }
