<?php
// set namspace shipmentservice
namespace App\Repositories\Services\ShipmentType;

use App\Model\Shipments\ShipmentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
class ShipmentTypeService implements ShipmentTypeServiceContract
{
    /**
     * class ShipmentTypeService implements ShipmentTypeserviceContract
     * connect model and controller
     * get data from database
     * @author Chan
     * create: 2019/10/10
     * update: 2019/10/10
     */
    protected $ShipmentType;
    public function __construct(){
        $this->ShipmentType = new ShipmentType();
    }

    //Get all shipment type
    public function getAll(){
        return $this->ShipmentType::select('id','shipment_name')->get();
    }
}