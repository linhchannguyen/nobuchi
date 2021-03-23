<?php

namespace App\Model\Orders;

use Illuminate\Database\Eloquent\Model;

class ImportRakuten extends Model
{
    //
    protected $fillable = [
        'id', 'order_id', 'order_date', 'product_id', 'quantity', 'price', 'select_date', 'buyer_name1', 'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana',
        'buyer_email', 'buyer_zip1', 'buyer_zip2', 'buyer_pref', 'buyer_city', 'buyer_area', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3', 'buyer_sex', 'buyer_birthday', 
        'recipient_name1', 'recipient_name2', 'recipient_name1_kana', 'recipient_name2_kana', 'ship_zip1', 'ship_zip2', 'ship_pref', 'ship_city', 'ship_area', 'ship_tel1', 
        'ship_tel2', 'ship_tel3', 'ship_option', 'payment_method', 'creadit_type', 'credit_no', 'credit_holder', 'credit_expiration_date', 'credit_split', 'credit_split_note', 
        'deliv_type', 'comment', 'wrapping_paper_type', 'wrapping_robbon_type', 'gift_check', 'total1', 'deliv_fee', 'tax', 'charge', 'total2', 'terminal_type', 'point_use',
        'point_terms_of_use', 'point_value', 'point_status', 'total3', 'acceptance_charge', 'memo', 'created_at', 'updated_at'        
    ];
}
