<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportAmazonHiroshima extends Model
{
    //
    protected $fillable = [
        'id', 
        'order_id', 
        'order_items_id', 
        'purchase_date', 
        'payment_date', 
        'buyer_email', 
        'buyer_name', 
        'buyer_phone', 
        'sku', 
        'product_name', 
        'quantity_purchased', 
        'currency', 
        'item_price', 
        'shipping_price', 
        'item_tax', 
        'shipping_tax', 
        'gift_wrap_price', 
        'gift_wrap_tax', 
        'ship_service_level', 
        'recipient_name', 
        'ship_address_1', 
        'ship_address_2', 
        'ship_address_3', 
        'ship_city', 
        'ship_sate', 
        'ship_postal_code', 
        'ship_country', 
        'ship_phone', 
        'created_at', 
        'updated_at'
    ];
}
