<?php

namespace App\Model\Purchases;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    //
    protected $fillable =
    [
        'id', 'purchase_code', 'order_id', 'order_detail_id', 'supplier_id', 'status', 'purchase_quantity',
        'cost_price', 'cost_price_tax', 'total_cost_price', 'total_cost_price_tax', 'price_edit', 'flag_download', 'purchase_date', 'created_at', 'updated_at'
        //  'confirm_date', 'confirm_by', 'flag_confirm_supplier', 'original_cost', 'total',      
    ];
}
