<?php

namespace App\Model\Imports;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    //
    protected $fillable =
    [
        'id',
        'type',
        'date_import',
        'website',
        'number_order', 
        'number_success',
        'number_error',
        'number_duplicate',
        'created_at',
        'updated_at'
    ];
    /**
     * belongto order
     */
    public function order()
    {
        return $this->hasOne('App\Model\Orders\Order');
    }
}
