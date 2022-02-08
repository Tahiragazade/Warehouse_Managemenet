<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=[

        'user_id',
        'role_id',
        'created_by'
    ];
    /**
     * @var mixed
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

}
