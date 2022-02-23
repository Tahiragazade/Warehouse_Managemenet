<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\WarehouseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WarehouseTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $transactionQuery = Db::table('warehouses_transactions as wht')
            ->select('wht.id as id',
                'products.name as product_name',
                'wht.quantity as quantity',
                'to_user.name as user_name',
                'fr_user.name as from_user',
                'fr_wh.name as from_wh_name',
                'dest_wh.name as dest_wh_name',
                'wht.transaction_id as th_id',
                'wht.notes as note',
                'wht.created_at as created_at',
                'wht.updated_at as updated_at'
            )

            ->leftJoin('products','wht.product_id','=','products.id')
            ->leftJoin('users as to_user','wht.to_id','=','to_user.id')
            ->leftJoin('users as fr_user','wht.from_id','=','fr_user.id')
            ->leftJoin('warehouses as dest_wh','wht.destination_wh_id','=','dest_wh.id')
            ->leftJoin('warehouses as fr_wh','wht.from_wh_id','=','fr_wh.id')
            ->orderByDesc('id');

        if($request->has('product_name'))
        {
            $transactionQuery->where('products.name','like','%'.$request->get('product_name').'%');
        }
        if($request->has('quantity'))
        {
            $transactionQuery->where('wht.quantity','like','%'.$request->get('quantity').'%');
        }
        if($request->has('from_wh_name'))
        {
            $transactionQuery->where('fr_wh.name','like','%'.$request->get('from_wh_name').'%');
        }
        if($request->has('dest_wh_name'))
        {
            $transactionQuery->where('dest_wh.name','like','%'.$request->get('dest_wh_name').'%');
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->get('page');
            $limit = $request->get('limit');
            $offset = ($page - 1) * $limit;
            $count = count($transactionQuery->get());
            $roles = $transactionQuery->limit($limit)->offset($offset)->get();
        }
        else{
            $count = count($transactionQuery->get());
            $roles = $transactionQuery->get();

        }

        return response()->json(['data' => $roles, 'total' => $count]);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>['required','integer'],
            'quantity'=>['required','integer'],
            'destination_wh_id'=>['required','integer'],
            'note'=>['string']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $from_wh_id=$request->from_wh_id;
        $quantity=$request->quantity;
        $product_id=$request->product_id;
        $to_wh_id=$request->destination_wh_id;
        if($from_wh_id==$to_wh_id)
        {
            return response()->json(['message' => 'Eyni anbardan mal göndərə bilməzsiniz'],409);
        }
        $quantityChecker=checkProductCount($from_wh_id,$product_id );

        $status=1;
            $checkUser=isStorekeeper($from_wh_id,Auth::id());
            if($checkUser==1) {

                if ($quantityChecker - $quantity >= 0) {
                    $model = new WarehouseTransaction();
                    $model->product_id = $request->product_id;
                    $model->quantity = $request->quantity;
                    $model->destination_wh_id = $request->destination_wh_id;
                    $model->transaction_id = $request->transaction_id;
                    $model->from_wh_id = $request->from_wh_id;
                    $model->from_id=Auth::id();

                    $model->notes=$request->note;
                    $model->status = $status;
                    $model->save();

                    $logs= new Log();
                    $logs->table_name='WarehouseTransaction';
                    $logs->record_id=$model->id;
                    $logs->action='create';
                    $logs->created_by=Auth::id();
                    $logs->save();

                    return response()->json(['data' => $model]);
                } else {
                    return response()->json(['message' => 'you have only ' . $quantityChecker . ' left'],404);
                }
            }
            else {
                return response()->json(['message' => 'You dont have Permission to do This'],403);
            }


        return response()->json(['message' => 'Something get wrong'],404);

    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>['required','integer'],
            'quantity'=>['required','integer'],
            'destination_wh_id'=>['required','integer'],
            'note'=>['string']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $from_wh_id=$request->from_wh_id;
        $quantity=$request->quantity;
        $product_id=$request->product_id;
        $to_wh_id=$request->destination_wh_id;


        $status=2;
            $checkUser = isStorekeeper($to_wh_id, Auth::id());
            if ($checkUser == 1) {
                $model = new WarehouseTransaction();
                $model->product_id = $request->product_id;
                $model->quantity = $request->quantity;
                $model->destination_wh_id = $request->destination_wh_id;
                $model->transaction_id = $request->transaction_id;
                $model->to_id = Auth::id();
                //$model->from_wh_id = $request->from_wh_id;
                $model->status =$status;
                $model->notes=$request->note;
                $model->save();

                $transaction = WarehouseTransaction::where('transaction_id', $request->transaction_id)->first();
                $transaction->updated_at = Carbon::now();
                $transaction->save();

                $logs= new Log();
                $logs->table_name='WarehouseTransaction';
                $logs->record_id=$model->id;
                $logs->action='store';
                $logs->created_by=Auth::id();
                $logs->save();
                return response()->json(['data' => $model]);

            } else {
                return response()->json(['message' => 'You dont have Permission to do This'],403);
            }

       // return response()->json(['message' => 'Something get wrong'],404);
    }
    public function registrToWarehouse($id, Request $request)
    {

//        $validator = Validator::make($request->all(), [
//            'transaction_id' => ['required', 'string']
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                'error' => $validator->errors()
//            ], 400);
//        }
        $transaction_id=$id;
        $status=2;
        $checkTransaction=WarehouseTransaction::query()
            ->select(['transaction_id','status'])
            ->where('transaction_id','=',$transaction_id)
            ->get();
        foreach ($checkTransaction as $chTR)
        {
            if($chTR->status==2)
            {
                return response()->json(['message' => 'Bu Transactionu siz artıq qəbul etmisiniz.']);
            }
        }
            $transaction = WarehouseTransaction::where('transaction_id', $transaction_id)->first();
            $store_id = $transaction->destination_wh_id;
            $checkUser = isStorekeeper($store_id, Auth::id());
            if ($checkUser == 1) {

                $model = new WarehouseTransaction();
                if ($request->quantity != $transaction->quantity) {
                    $validator = Validator::make($request->all(), [
                        'note' => ['required', 'string'],
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'error' => $validator->errors()
                        ], 400);
                    }
                    $model->notes = $request->note;
                    $model->quantity = $request->quantity;
                } else {
                    $model->notes = $transaction->notes;
                    $model->quantity = $transaction->quantity;
                }
                $model->product_id = $transaction->product_id;
                $model->destination_wh_id = $transaction->destination_wh_id;
                $model->transaction_id = $transaction->transaction_id;
                $model->from_wh_id = $transaction->from_wh_id;
                $model->to_id=Auth::id();
                $model->status = $status;
                $transaction->updated_at = Carbon::now();
                $transaction->save();
                $model->save();

                $logs= new Log();
                $logs->table_name='WarehouseTransaction';
                $logs->record_id=$model->id;
                $logs->action='registr';
                $logs->created_by=Auth::id();
                $logs->save();

                return response()->json(['data' => $model]);

            }
            else {
                return response()->json(['message' => $checkUser.'You dont have Permission to do This']);
            }


        //return response()->json(['message' => 'Something get wrong'],404);
    }

    public function checkStore(Request $request)
    {
        $report=storeReport($request);
        $total=count($report);
        if($request->has('page')&&$request->page!=null && $request->has('limit') && $request->limit!=null) {
            $page = $request->page;

            $limit = $request->limit;

            $total_pages = ceil($total / $limit);
            if ($page > $total_pages) {
                $page = $total_pages;
            }
            $offset = ($page - 1) * $limit;

            $data = array_slice($report, $offset, $limit);

            return response()->json(['data' => $data, 'total' => $total]);
        }
        else
        {
            return response()->json(['data' => $report, 'total' => $total]);
        }
    }

    public function typeDropdown()
    {
        $type=[];
        $types=[
            0=>'Hamısı',
            1=>'Gedən Mallar',
            2=>'Gələn Mallar',
            3=>'Anbarda Olan Mallar'
            ];
        foreach ($types as $key=>$value)
        {
            $type[] = array(
                'key' => $key,
                'value' => $key,
                'title' => $value
            );
        }
        return response()->json(['data'=>$type]);
    }
}
