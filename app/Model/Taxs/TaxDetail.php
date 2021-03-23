<?php

namespace App\Model\Taxs;

use Illuminate\Database\Eloquent\Model;

class TaxDetail extends Model
{
    protected $fillable = [
        'id', 'tax_class', 'name', 'default_flg', 'apply_date', 'rank', 'del_flg'
    ];
}
