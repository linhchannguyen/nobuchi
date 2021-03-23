<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderMix extends Model
{
    protected $table = 'order_mix';
    protected $primaryKey = 'id';
    protected $filable = [
        'id', 'import_id', 'ship_id', 'order_code', 'order_date', 'buyer_name1',
        'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana', 'buyer_country', 'buyer_address_1',
        'buyer_address_2', 'buyer_address_3', 'buyer_address_1_kana', 'buyer_address_2_kana', 'buyer_address_3_kana',
        'buyer_email', 'buyer_zip1', 'buyer_zip2', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3', 'buyer_sex', 'buyer_birthday', 
        'ship_name1', 'ship_name2', 'ship_name1_kana', 'ship_name2_kana', 'ship_country', 'ship_address1', 'ship_address2', 
        'ship_address3', 'ship_address1_kana', 'ship_zip', 'ship_phone', 'shipment_code', 'product_code', 'product_id',
        'product_name','quantity', 'unit_price', 'price_buy', 'total_price_buy', 'price_sale', 'total_price_sale',
        'cost_price', 'total_price', 'type', 'site_type', 'supplied_id', 'supplied', 'sku', 'gift_wrap_price', 'tax', 
        'charge', 'discount', 'sub_total', 'site_charge', 'order_sub_total', 'order_delivery_fee', 'order_gift_wrap_price', 
        'order_tax', 'order_charge', 'order_discount', 'order_total', 'use_point', 'payment_total', 'order_site_charge', 
        'delivery_way', 'delivery_fee', 'delivery_payment', 'es_delivery_date', 'es_delivery_date_from', 
        'es_delivery_date_to', 'delivery_date_from', 'delivery_date_to', 'delivery_date', 'comments1', 
        'comments2', 'noshi_type', 'noshi_name', 'wrapping_paper_type', 'wrapping_ribbon_type', 'gift_wrap', 'gift_wrap_kind', 
        'gift_message', 'message', 'delivery_method', 'payment_id', 'payment_method', 'credit_type', 'supplier_id', 'supplier_name', 
        'supplier_zip1', 'supplier_zip2', 'supplier_addr1', 'supplier_addr2', 'supplier_addr3', 'supplier_tel1', 'supplier_tel2', 
        'supplier_tel3', 'supplier_code_sagawa', 'supplier_code_kuroneko', 'cargo_schedule_day', 'cargo_schedule_time_from', 
        'cargo_schedule_time_to', 'status', 'support_cus', 'flag_confirm', 'purchase_date', 'product_comments', 'product_name_sub', 
        'product_name_org', 'created_at', 'updated_at'
    ];
}
