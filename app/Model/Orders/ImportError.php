<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    
    protected $fillable =
    [
        'id',
        'import_id',
        'list_id',
        'created_at',
        'updated_at'
    ];
}
