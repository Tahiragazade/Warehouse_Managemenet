<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'warehouses_transactions';
    protected $fillable=[

        'name',
        'product_id',
        'quantity',
        'destinition_wh_id',
        'from_wh_id',
        'transaction_id',
        'status'
    ];
    /**
     * @var mixed
     */
public function productName(){
   return $this->belongsTo(Product::class,'product_id','id');
}
}
