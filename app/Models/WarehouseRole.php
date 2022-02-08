<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseRole extends Model
{
    use HasFactory;

    protected $fillable=[

        'warehouse_id',
        'role_id'
    ];
    /**
     * @var mixed
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

}
