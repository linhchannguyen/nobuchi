<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportEccube extends Model
{
    protected $fillable = [
        'order_id', 'order_temp_id', 'customer_id', 'message', 'classcategory_name1', 'classcategory_name2', 'product_name',
        'product_id', 'product_class_id', 'product_code', 'quantity', 'price', 'point_rate', 'order_name01', 'order_name02',
        'order_kana01', 'order_kana02', 'order_email', 'order_tel01', 'order_tel02', 'order_tel03', 'order_fax01', 'order_fax02', 
        'order_fax03', 'order_zip01', 'order_zip02', 'order_pref', 'order_addr01', 'order_addr02', 'order_sex', 'order_birth', 'order_job', 
        'subtotal', 'discount', 'deliv_id', 'deliv_fee', 'charge', 'use_point', 'add_point', 'birth_point', 'tax', 'total', 'payment_total',
        'payment_id', 'payment_method', 'note', 'status', 'commit_date', 'payment_date', 'device_type_id', 'del_flg', 'memo01', 'memo02', 
        'memo03', 'memo04', 'memo05', 'memo06', 'memo07', 'memo08', 'memo09', 'memo10', 'order_type_id', 'coupon_id', 'discount_coupon', 
        'created_at', 'updated_at'
    ];
}
