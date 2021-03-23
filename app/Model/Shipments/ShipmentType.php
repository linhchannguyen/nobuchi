<?php

namespace App\Model\Shipments;

use Illuminate\Database\Eloquent\Model;

class ShipmentType extends Model
{
    protected $fillable = [
        'shipment_id', 'shipment_name'
    ];
}
