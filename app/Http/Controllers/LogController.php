<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $logQuery = Log::query()
            ->select('logs.table_name as table','record_id as record','action','users.name')
            ->join('users','logs.created_by','=','users.id');
        if($request->has('table_name'))
        {
            $logQuery->where('logs.table_name','like','%'.$request->get('table_name').'%');
        }
        if($request->has('user_name'))
        {
            $logQuery->where('users.name','like','%'.$request->get('user_name').'%');
        }
        if($request->has('action'))
        {
            $logQuery->where('logs.action','like','%'.$request->get('action').'%');
        }
        if($request->has('record_id'))
        {
            $logQuery->where('logs.record_id','=',$request->get('record_id'));
        }
        if($request->has('limit')&&$request->has('page')) {
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $count = count($logQuery->get());
            $logs = $logQuery->limit($limit)->offset($offset)->get();
        }
        else{
            $count = count($logQuery->get());
            $logs = $logQuery->get();
        }

        return response()->json(['data' => $logs, 'total' => $count]);
    }

    public function dropdown()
    {
        $logs=Log::query()
            ->select('table_name as name')
            ->distinct()
            ->get();
        $log=GenerateDropdownLog($logs);

        return response()->json($log);
    }
    public function deleted(Request $request)
    {
        $model = $request->name;
        $models = 'App\Models\\' . $model;
        $datas = $models::query()
            ->select(['id','name','deleted_at'])
            ->onlyTrashed()
            ->get();
        foreach ($datas as $data)
        {
            $data->model = $model.'/'.$data->id;
        }
        return response()->json(['data'=>$datas]);

    }
}
