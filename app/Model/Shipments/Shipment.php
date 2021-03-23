<?php

namespace App\Model\Shipments;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'id', 'shipment_code', 'shipment_quantity',  
        'shipment_customer', 'shipment_address', 'shipment_email', 'shipment_fax', 'shipment_phone', 'shipment_at', 'type', 
        'status', 'shipment_date', 'receive_date', 'receive_time', 'shipment_time', 'delivery_method', 'delivery_way', 'shipment_zip', 
        'shipping_pref', 'shipment_fee', 'shipment_payment', 'created_by', 'updated_by', 'del_flg','invoice_id', 'pay_request',
        'deleted_by', 'es_shipment_date', 'es_shipment_time', 'supplied_id', 'created_at', 'updated_at'
        //'order_id', 'cost_price', 'cost_price_tax', 'total_cost_price', 'total_cost_price_tax', 'price_edit', 'confirm_date', 'confirm_by', 'flag_download', 'flag_confirm_supplier',
    ];
}
