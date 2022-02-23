<?php

/**
 * @param $organizations
 * @param null $parent
 * @return array
 */

use App\Models\Product;
use App\Models\User;
use App\Models\UserRole;
use App\Models\WarehouseRole;
use App\Models\WarehouseTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
 * Dropdown selector
 */
function GenerateCategorySelectTree($categories, $parent = null) {
    $tree = [];
    foreach ($categories as $category) {
        if($category->parent_id == $parent) {
            $tree[] = array(
                'key' => $category->id,
                'value' => $category->id,
                'title' => $category->name,
                'children' => GenerateCategorySelectTree($categories, $category->id)
            );
        }
    }

    return $tree;
}
/*
Dropdown delete empty children
*/
function clearEmptyChildren(&$tree)
{
    foreach ($tree as $key =>$value )
    {
        if(empty($value['children']))
        {
            unset($tree[$key]['children']);
        }
        else
        {
            clearEmptyChildren($tree[$key]['children']);
        }
    }
}
//Checks product count before transaction if there is enough to transaction
function checkProductCount($from_id,$product_id)
{   $status_in=2;
    $status_out=1;

    $product_in = WarehouseTransaction::query()
        ->select([DB::raw('sum(quantity) as quantity_in')

        ])
        ->where('destination_wh_id',$from_id)
        ->where('product_id',$product_id)
        ->where('status',$status_in)
        ->groupBy('product_id')
        ->get();

    $product_out = WarehouseTransaction::query()
        ->select([DB::raw('sum(quantity) as quantity_out')
        ])
        ->where('from_wh_id',$from_id)
        ->where('product_id',$product_id)
        ->where('status',$status_out)
        ->groupBy('product_id')
        ->get();
    if(count($product_in)==0)
    {
        $count=0;
    }
    elseif(count($product_out)==0)
    {
        $count= $product_in[0]->quantity_in;
    }
    else
    {
        $count = ($product_in[0]->quantity_in - $product_out[0]->quantity_out);
    }
    return $count;
}

//shows report of transaction belongs to store
function storeReport($request)
{
    /*
     * types =>[
     * hamısı -0
     * gedən mallar-1
     * gələn mallar-2
     * anbarda olan mallar -3
     * ]
     */
    $report = [];
    $status_in = 2;
    $status_out = 1;
    $store_id=$request->store_id;
    $type=$request->type;
    if($request->has('from_date')&&$request->from_date!=null){

        $from_date=Carbon::parse($request->from_date);
    }
    elseif(!$request->has('from_date')||$request->from_date==null)
    {
        $from_date=Carbon::yesterday();
    }
    if($request->has('to_date'))
    {

        $to_date=Carbon::parse($request->to_date)->addHours(24)->subMicrosecond(1);
    }
    elseif(!$request->has('to_date')||$request->to_date==null)
    {
        $to_date=Carbon::now();
    }

    $name=$request->product_name;

    $type=$request->type;


    if($store_id=="")
    {
        return 'Anbarı seçməmisiniz';
    }
    $all_products=Product::query()
        ->select(['*'])
        ->where('name','like','%'.$name.'%')
        ->get();
    foreach ($all_products as $products) {
        $product_id = $products->id;
        $product_name = $products->name;



                $product_out = WarehouseTransaction::query()
                    ->select([DB::raw('sum(quantity) as quantity_out')
                    ])
                    ->where('from_wh_id', $store_id)
                    ->whereBetween('created_at',[$from_date,$to_date])
                    ->where('product_id', $product_id)
                    ->where('status', $status_out)
                    ->get();

                $product_in = WarehouseTransaction::query()
                    ->select([DB::raw('sum(quantity) as quantity_in')
                    ])
                    ->where('destination_wh_id', $store_id)
                    ->whereBetween('created_at',[$from_date,$to_date])
                    ->where('product_id', $product_id)
                    ->where('status', $status_in)
                    ->get();

            if (count($product_in) == 0) {
                $count_in = 0;
            } else {
                $count_in = $product_in[0]->quantity_in;
            }
            if ($product_out->count() <= 0) {
                $count_out = 0;
            } else {
                $count_out = $product_out[0]->quantity_out;
            }
            $total_count = $count_in - $count_out;

            if($type==1 && $count_out>0)
            {
                $report[] = array(
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'report' => $count_out

                );
            }
        if($type==2 && $count_in>0)
        {
            $report[] = array(
                'product_id' => $product_id,
                'product_name' => $product_name,
                'report' => $count_in

            );
        }
        if($type==3)
        {
            if($total_count>0)
            {
                $report[] = array(
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'count_in' => $count_in,
                    'count_out' => $count_out,
                    'report' => $total_count

                );
            }
        }
            elseif($type==null||$type==0) {
                if ($count_in > 0) {
                    $report[] = array(
                        'product_id' => $product_id,
                        'product_name' => $product_name,
                        'count_in' => $count_in,
                        'count_out' => $count_out,
                        'report' => $total_count

                    );
                }
            }
    }

    return $report;

}
//First version of checking user before transaction
function checkWarehouseRole($store_id,$user_id)
{
    $role=WarehouseRole::where(['warehouse_id'=>$store_id,'user_id'=>$user_id])->first();
    if(!empty($role))
    {
        return 1;
    }
    else
    {
        return 2;
    }

}
//check if user is admin or not -- 1 is admin -- 2 is not admin
function isAdmin($user_id)
{
    $admin = UserRole::query()
        ->select(['*'])
        ->where('user_id',$user_id)
        ->where('role_id','=',1)
        ->get();
    if(count($admin)>0)
    {
        return 1;
    }
    else{
        return 2;
    }
}
//check if user is Storekeeper or not -- 1 is Storekeeper -- 2 is not Storekeeper
function isStorekeeper($store_id,$user_id)
{
    $admin=isAdmin($user_id);
    $user=UserRole::query()
        ->select(['*'])
        ->where('warehouse_id', $store_id)
        ->where('user_id',$user_id)
        ->where('role_id','=',2)
        ->get();
    if($admin==1 || count($user)>0)
    {
        return 1;
    }
    else{
        return 2;
    }
}
//check user if he/she is the same user that checking the user details. Admin can access everything
function isSameUser($user_id)
{
    $admin=isAdmin($user_id);
    $user=User::query()
        ->select(['*'])
        ->where('id',$user_id)
        ->get();
    if($admin==1 || count($user)>0)
    {
        return 1;
    }
    else{
        return 2;
    }
}
function GenerateCategoryIndex($categories, $parent = null) {
    $tree = [];
    foreach ($categories as $category) {
        if($category->parent_id == $parent) {
            $tree[] = [
                'key' => $category->id,
                'value' => $category->id,
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'children' => GenerateCategoryIndex($categories, $category->id)
            ];
        }
    }

    return $tree;
}
function GenerateDropdownTree($datas) {
    $tree = [];
    foreach ($datas as $data) {

        $tree[] = array(
            'key' => $data->id,
            'value' => $data->id,
            'title' => $data->name
        );

    }

    return $tree;
}
function GenerateDropdownLog($datas) {
    $tree = [];
    foreach ($datas as $data) {

        $tree[] = array(
            'key' => $data->name,
            'value' => $data->name,
            'title' => $data->name
        );

    }

    return $tree;
}

