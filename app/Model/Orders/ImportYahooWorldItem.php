<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportYahooWorldItem extends Model
{
    //
    protected $fillable =
    [
        'id', 
        'order_id', 
        'line_id', 
        'quantity', 
        'product_code', 
        'product_id', 
        'description', 
        'option_name', 
        'option_value', 
        'unit_price', 
        'unit_get_point', 
        'line_sub_total', 
        'line_get_point', 
        'created_at', 
        'updated_at'
    ];
}
