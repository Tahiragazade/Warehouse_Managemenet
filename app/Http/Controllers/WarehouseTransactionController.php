<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\WarehouseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WarehouseTransactionController extends Controller
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
        $count=WarehouseTransaction::count();

        $result=WarehouseTransaction::query()->limit($limit)->offset($offset)->get();
        return response()->json(['data' => $result, 'total' => $count]);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>['required','integer'],
            'quantity'=>['required','integer'],
            'destination_wh_id'=>['required','integer'],
            'status'=>['required','integer'],
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

        $quantityChecker=checkProductCount($from_wh_id,$product_id );

        if($request->status==1 ) {
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
                    $model->status = $request->status;
                    $model->save();

                    $logs= new Log();
                    $logs->table_name='WarehouseTransaction';
                    $logs->record_id=$model->id;
                    $logs->action='create';
                    $logs->created_by=Auth::id();
                    $logs->save();

                    return response()->json(['data' => $model]);
                } else {
                    return response()->json(['message' => 'you have only ' . $quantityChecker . ' left']);
                }
            }
            else {
                return response()->json(['message' => 'You dont have Permission to do This'],403);
            }
        }

        return response()->json(['message' => 'Something get wrong'],404);

    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>['required','integer'],
            'quantity'=>['required','integer'],
            'destination_wh_id'=>['required','integer'],
            'status'=>['required','integer'],
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


        if($request->status==2) {
            $checkUser = isStorekeeper($to_wh_id, Auth::id());
            if ($checkUser == 1) {
                $model = new WarehouseTransaction();
                $model->product_id = $request->product_id;
                $model->quantity = $request->quantity;
                $model->destination_wh_id = $request->destination_wh_id;
                $model->transaction_id = $request->transaction_id;
                $model->to_id = Auth::id();
                //$model->from_wh_id = $request->from_wh_id;
                $model->status = $request->status;
                $model->save();

                $transaction = WarehouseTransaction::where('transaction_id', $request->transaction_id)->first();
                $transaction->updated_at = Carbon::now();
                $transaction->save();

                $logs= new Log();
                $logs->table_name='WarehouseTransaction';
                $logs->record_id=$model->id;
                $logs->action='registr';
                $logs->created_by=Auth::id();
                $logs->save();
                return response()->json(['data' => $model]);

            } else {
                return response()->json(['message' => 'You dont have Permission to do This'],403);
            }
        }
        return response()->json(['message' => 'Something get wrong'],404);
    }
    public function registrToWarehouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => ['required', 'string'],
            'status' => ['required', 'integer']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }
        if ($request->status == 2) {

            $transaction = WarehouseTransaction::where('transaction_id', $request->transaction_id)->first();
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
                $model->status = $request->status;
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

        }
        return response()->json(['message' => 'Something get wrong'],404);
    }

    public function checkStore($store_id)
    {
        $report=storeReport($store_id);
        return response()->json(['data' => $report]);

    }
}
