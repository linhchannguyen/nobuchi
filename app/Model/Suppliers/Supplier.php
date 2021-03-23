<?php

namespace App\Model\Suppliers;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    protected $fillable = [
        'supplier_id', 'name', 'zip01', 'zip02', 'pref', 'addr01', 'addr02', 'email', 'tel01', 'tel02', 'tel03', 'fax01', 
        'fax02', 'fax03', 'staff', 'supplier_code_sagawa', 'rank', 'creator_id', 'create_date', 'update_date', 'del_flg', 'supplier_code_kuroneko', 
        'cargo_schedule_day', 'cargo_schedule_time_from', 'cargo_schedule_time_to', 
        // 'edi_type', 'holiday_sun', 'holiday_mon',
        // 'holiday_tue', 'holiday_wed', 'holiday_thu', 'holiday_fri', 'holiday_sat', 'supplier_class', 'shipping_method'
    ];
}
