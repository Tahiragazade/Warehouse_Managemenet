<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Log;
use App\Models\Product;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $productQuery = Product::query()
            ->select('products.id as id','products.name as product_name','categories.name as category_name','price','sale_price')
            ->join('categories','products.category_id','=','categories.id');
        if($request->has('product_name'))
        {
            $productQuery->where('products.name','like','%'.$request->get('product_name').'%');
        }
        if($request->has('category_name'))
        {
            $productQuery->where('categories.name','like','%'.$request->get('category_name').'%');
        }
        if($request->has('price'))
        {
            $productQuery->where('products.price','=',$request->get('price'));
        }
        if($request->has('sale_price'))
        {
            $productQuery->where('products.sale_price','=',$request->get('sale_price'));
        }

        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($productQuery->get());
            $products = $productQuery->limit($limit)->offset($offset)->get();
        }
        else
        {
            $count = count($productQuery->get());
            $products=$productQuery->get();
        }
        return response()->json(['data' => $products, 'total' => $count]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required','string','unique:products'],
            'category_id'=>['required','integer'],
            'price'=>['required','integer'],
            'sale_price'=>['required','integer']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $model= new Product();
        $model->name=$request->name;
        $model->category_id=$request->category_id;
        $model->price=$request->price;
        $model->sale_price=$request->sale_price;
        $model->save();

        $logs= new Log();
        $logs->table_name='Product';
        $logs->record_id=$model->id;
        $logs->action='create';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['data'=>$model]);
    }
public function update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'=>['string','unique:products'],
        'category_id'=>['integer'],
        'price'=>['integer'],
        'sale_price'=>['integer']
    ]);

    if ($validator->fails())
    {
        return response()->json([
            'error' => $validator->errors()
        ], 400);
    }
    $model=Product::find($request->id);
    $model->name=$request->name;
    $model->category_id=$request->category_id;
    $model->price=$request->price;
    $model->sale_price=$request->sale_price;
    $model->save();

    $logs= new Log();
    $logs->table_name='Product';
    $logs->record_id=$model->id;
    $logs->action='update';
    $logs->created_by=Auth::id();
    $logs->save();

    return response()->json(['data'=>$model]);
}
public function delete($id)
{
    $transactions=WarehouseTransaction::query()
        ->select(['*'
        ])
        ->where('product_id', $id)
        ->get();
    $product=Product::find($id);
    if(count($transactions)==0 && !empty($product))
    {
        $product->delete();

        $logs= new Log();
        $logs->table_name='Product';
        $logs->record_id=$id;
        $logs->action='delete';
        $logs->created_by=Auth::id();
        $logs->save();

        return response()->json(['message'=>$product->name.' has been deleted']);
    }
    elseif(count($transactions)>0)
    {
        return response()->json(['message'=>$product->name.' can not be deleted'],400);
    }
    else
    {
        return response()->json(['message'=>'id number '.$id.' not found'],404);
    }
}
    public function single(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>['integer']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        $model=Product::query()
            ->select('*')
            ->where('id', $request->id)
            ->Orwhere('name',$request->name)
            ->get();
        return response()->json(['data'=>$model]);
    }
    public function dropdown(){
        $datas=Product::all();
        $dropdown=GenerateDropdownTree($datas);
        return response()->json(['data'=>$dropdown]);

    }
}

