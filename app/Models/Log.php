<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Log extends Model
{
    use HasFactory;


    protected $fillable=[

        'table_name',
        'record_id',
        'action',
        'created_by'
    ];
    /**
     * @var mixed
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

}
