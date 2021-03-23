<?php

namespace App\Model\Taxs;

use Illuminate\Database\Eloquent\Model;

class TaxClass extends Model
{
    protected $fillable = [
        'id', 'tax_class', 'apply_date', 'tax_rate', 'tax_rule', 'mark', 'memo', 'create_date', 'update_date'
    ];
}
