<?php

/**
 * @param $organizations
 * @param null $parent
 * @return array
 */

use App\Models\User;
use App\Models\UserRole;
use App\Models\WarehouseRole;
use App\Models\WarehouseTransaction;
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
function storeReport($store_id)
{
    $report = [];
    $status_in = 2;
    $status_out = 1;

    $product_id = WarehouseTransaction::query()
        ->select(['product_id'
        ])
        ->where('from_wh_id', $store_id)
        ->OrWhere('destination_wh_id', $store_id)
        ->distinct()
        ->get();
    foreach ($product_id as $product) {
        $product_id = $product->product_id;
        $product_name = $product->productName->name;
        $product_out = WarehouseTransaction::query()
            ->select([DB::raw('sum(quantity) as quantity_out')
            ])
            ->where('from_wh_id', $store_id)
            ->where('product_id', $product_id)
            ->where('status', $status_out)
            ->get();

        $product_in = WarehouseTransaction::query()
            ->select([DB::raw('sum(quantity) as quantity_in')
            ])
            ->where('destination_wh_id', $store_id)
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
        $report[] = array(
            'product_id' => $product_id,
            'product_name' => $product_name,
            'count_in' => $count_in,
            'count_out' => $count_out,
            'report' => $total_count

        );
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
