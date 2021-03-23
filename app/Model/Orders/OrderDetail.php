<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable =
    [
        'id', 'order_id', 'order_code', 'shipment_id', 'purchase_id', 'product_code', 'product_id', 'product_name', 'product_info', 'quantity', 'quantity_set',
        'price_sale', 'price_sale_tax', 'total_price_sale', 'total_price_sale_tax', 'cost_price', 'total_price', 'cost_price_tax', 'total_price_tax', 'tax', 'discount',
        'type', 'site_type', 'supplied_id', 'supplied', 'supplier_zip1', 'supplier_zip2', 'supplier_addr1', 'supplier_addr2', 'supplier_addr3', 'supplier_tel1',
        'supplier_tel2', 'supplier_tel3', 'supplier_code_sagawa', 'supplier_code_kuroneko', 'product_comments', 'product_name_sub', 'product_name_org', 'sku', 'maker_id', 'maker_code',
        'delivery_method', 'delivery_way', 'ship_name1', 'ship_name2', 'ship_name1_kana', 'ship_name2_kana', 'ship_country', 'ship_address1', 'ship_address2', 
        'ship_address3', 'ship_address1_kana', 'ship_zip', 'ship_phone', 'delivery_fee', 'delivery_payment', 'es_delivery_date', 'es_delivery_date_from',
        'es_delivery_date_to', 'delivery_date_from', 'delivery_date_to', 'purchase_date', 'purchase_code', 'delivery_date', 'delivery_time', 'receive_date',
        'receive_time', 'comments', 'wrapping_paper_type', 'wrapping_ribbon_type', 'gift_wrap', 'gift_wrap_kind', 'gift_message', 'message', 'pay_request', 'created_at', 'updated_at'
    ];
    /**
     * get order
     */
    public function order()
    {
        return $this->belongsTo('App/Model/Orders/Order', 'order_id');
    }
}
