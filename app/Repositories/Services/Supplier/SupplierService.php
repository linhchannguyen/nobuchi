<?php
// set namespace
namespace App\Repositories\Services\Supplier;

use App\Model\Suppliers\Supplier;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierService implements SupplierServiceContract
{
    /**
     * class supplier service
     * @author Dat
     * 2019/10/15
     */
    private $supplier_modal;
    public function __construct(Supplier $supplier_modal)
    {
        $this->supplier_modal = $supplier_modal;
    }

    public function getAll(){
        try{
            return $this->supplier_modal->select(DB::raw('id, name'))->orderBy('id')->get();
        }catch(Exception $exception){            
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    
    /**
     * function search supplier modal
     * @author Dat
     * 2019/10/15
     */
    public function searchSupplierModal($request = null)
    {
        $query =  $this->supplier_modal;
        try{
            // $query = $query->join('delivery_methods', 'suppliers.delivery_method', '=', 'delivery_methods.id');
            if(isset($request['delivery_method']) && count($request['delivery_method'])>0)
            {
                $query = $query->whereIn('shipping_method', $request['delivery_method']);// condition  delivery method in table
            }
            if(isset($request['type']) && $request['type']!= '')
            {
                $query = $query->where('supplier_class', $request['type']);// condition  type in table
            }
            if(isset($request['purchase_method']) && count($request['purchase_method'])>0)
            {
                $query = $query->whereIn('edi_type', $request['purchase_method']);// condition purchase_method
            }
            if(isset($request['date_off']) && count($request['date_off'])>0)
            {
                if(!empty($request['date_off']))
                {
                    foreach ($request['date_off'] as $value) {
                        $query = $query->OrWhere($value, 1);
                    }
                }
            }
            if(isset($request['supplier_name']) && $request['supplier_name']!= '')
            {
                $query = $query->where('name', 'ilike',"%".$request['supplier_name']."%");// condition name supplier
            }
            $query = $query->get()->toArray();
            return $query;
        }catch (Exception $exception){
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
}