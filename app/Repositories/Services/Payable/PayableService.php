<?php
// set namspace PayableService
namespace App\Repositories\Services\Payable;

use App\Model\Orders\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PayableService implements PayableServiceContract
{
    /**
     * class PayableService implements PayableServiceContract
     * connect model and controller
     * get data from database
     * @author Chan
     * date 2019/10/15
    */

    protected $Order;
    protected $OrderDetail;
    protected $Purchase;

    public function __construct(){
        $this->Order = new Order();
    }

    /**
     * function listMoneyOwedToSuppliers()
     * Desciption: Thống kê tiền phải trả cho nhà cung cấp theo niên độ năm
     * @author channl
     * Created: 2019/10/16
     * Updated: 2019/10/16
     */
    public function listMoneyOwedToSuppliers($year, $fee)
    {
        try {
            $query = $this->Order;
            $str = "";
            if($fee == 0){//No fee
                $str = "
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 9 then (purchases.total_cost_price) else null end) as month_9,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 8 then (purchases.total_cost_price) else null end) as month_8,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 7 then (purchases.total_cost_price) else null end) as month_7,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 6 then (purchases.total_cost_price) else null end) as month_6,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 5 then (purchases.total_cost_price) else null end) as month_5,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 4 then (purchases.total_cost_price) else null end) as month_4,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 3 then (purchases.total_cost_price) else null end) as month_3,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 2 then (purchases.total_cost_price) else null end) as month_2,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 1 then (purchases.total_cost_price) else null end) as month_1,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 12 then (purchases.total_cost_price) else null end) as month_12,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 11 then (purchases.total_cost_price) else null end) as month_11,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 10 then (purchases.total_cost_price) else null end) as month_10
                ";
            }else {//Fee
                $str = "
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 9 then purchases.total_cost_price_tax else null end) as month_9,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 8 then purchases.total_cost_price_tax else null end) as month_8,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 7 then purchases.total_cost_price_tax else null end) as month_7,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 6 then purchases.total_cost_price_tax else null end) as month_6,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 5 then purchases.total_cost_price_tax else null end) as month_5,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 4 then purchases.total_cost_price_tax else null end) as month_4,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 3 then purchases.total_cost_price_tax else null end) as month_3,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 2 then purchases.total_cost_price_tax else null end) as month_2,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".$year."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 1 then purchases.total_cost_price_tax else null end) as month_1,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 12 then purchases.total_cost_price_tax else null end) as month_12,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 11 then purchases.total_cost_price_tax else null end) as month_11,
                    sum(case when orders.status in (3,4,5,6,7) and purchases.status = 4 and EXTRACT(YEAR FROM shipments.es_shipment_date) = '".($year-1)."' AND EXTRACT(MONTH FROM shipments.es_shipment_date) = 10 then purchases.total_cost_price_tax else null end) as month_10
                ";
            }
            $query = $query->select('suppliers.id', 'suppliers.name', DB::raw($str))
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('purchases', 'purchases.id', '=', 'order_details.purchase_id')
            ->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
            ->join('suppliers', 'suppliers.id', '=', 'order_details.supplied_id')
            ->whereIn('orders.status', [3,4,5,6,7])->where('purchases.status', 4)
            ->whereBetween('shipments.es_shipment_date', [($year-1)."/10/1", "$year/9/30"])
            ->groupBy('suppliers.id')
            ->orderBy('suppliers.id');
            return $query->get()->toArray();
        }catch(Exception $ex){
            Log::debug($ex->getMessage());
        }
    }
}