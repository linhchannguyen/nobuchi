<?php

namespace App\Model\Orders;

use App\Model\Imports\Import;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable =
    [
        'id', 'site_type', 'import_id', 'purchase_id', 'ship_id', 'order_code', 'order_date', 'buyer_name1', 'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana',
        'buyer_country', 'buyer_address_1', 'buyer_address_2', 'buyer_address_3', 'buyer_address_1_kana', 'buyer_address_2_kana', 'buyer_address_3_kana',
        'buyer_email', 'buyer_zip1', 'buyer_zip2', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3', 'buyer_sex', 'buyer_birthday', 'tax', 'fax', 'charge', 'sub_total',
        'price_untax',  'order_delivery_fee', 'order_gift_wrap_price', 'order_discount', 'order_total', 'use_point',
        'payment_total', 'order_site_charge', 'comments', 'noshi_type', 'noshi_name', 'payment_id', 'payment_method', 'credit_type', 'supplier_id', 'supplier_name', 
        'supplier_zip1', 'supplier_zip2', 'supplier_addr1', 'supplier_addr2', 'supplier_addr3', 'supplier_tel1', 'supplier_tel2', 'supplier_tel3', 'supplier_code_sagawa',
        'supplier_code_kuroneko', 'cargo_schedule_day', 'cargo_schedule_time_from', 'cargo_schedule_time_to', 'status', 'support_cus', 'flag_confirm', 'purchase_date',
        'delivery_date', 'comment', 'money_daibiki', 'quantity_service', 'price_service', 'total_service', 'number_of_copies', 'created_at', 'updated_at'
        // 'id', 'site_type', 'import_id' ,'order_code','order_date','buyer_name1','buyer_name2','buyer_name1_kana','buyer_name2_kana','buyer_country','buyer_address_1',
        // 'buyer_address_2','buyer_address_3','buyer_address_1_kana','buyer_address_2_kana','buyer_address_3_kana','buyer_email','buyer_zip1','buyer_zip2','buyer_tel1',
        // 'buyer_tel2', 'buyer_tel3', 'buyer_sex', 'buyer_birthday', 'tax', 'fax', 'charge', 'sub_total', 'price_untax', 'order_delivery_fee', 'order_gift_wrap_price', 
        // 'order_discount', 'order_total', 'use_point', 'payment_total', 'order_site_charge', 'comments', 'noshi_type', 'noshi_name', 'payment_id', 'payment_method', 
        // 'credit_type', 'cargo_schedule_day', 'cargo_schedule_time_from', 'cargo_schedule_time_to', 'status' , 'support_cus', 'flag_confirm', 'purchase_date',
        //  'delivery_date', 'money_daibiki', 'quantity_service', 'price_service', 'total_service', 'number_of_copies'
    ];
    /**
     * get detail order 
     */
    public function order_details ()
    {
        return $this->hasMany('App\Model\Orders\OrderDetail');
    }
    /**
     * relationship import
     */
    public function imports ()
    {
        return $this->belongsTo('App\Model\Imports\Import', 'import_id');
    }
}
