<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportAmazonFbaHiroshima extends Model
{
    //
    protected $fillable =[
        'id', 
        'purchase_date', 
        'transaction_id', 
        'transaction_type', 
        'order_id', 
        'sku', 
        'memo', 
        'quantity', 
        'service_name', 
        'fulfillment', 
        'city', 
        'pref', 
        'zip', 
        'item_price', 
        'deliv_fee', 
        'gift_wrap_price', 
        'promotion_discount', 
        'charge',
        'charge_fba', 
        'charge_tran', 
        'etc', 
        'total_price', 
        'amazon_point', 
        'created_at', 
        'updated_at'
    ];
}
