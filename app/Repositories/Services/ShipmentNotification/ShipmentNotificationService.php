<?php
// set namspace ShipmentNotificationService
namespace App\Repositories\Services\ShipmentNotification;

use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ShipmentNotificationService implements ShipmentNotificationServiceContract
{
    /**
     * class ShipmentNotificationService implements ShipmentNotificationServiceContract
     * connect model AND controller
     * get data from database
     * @author Chan
     * date 2019/10/07
     */

    protected $Order;
    protected $OrderDetail;
    protected $Purchase;    
    protected $Shipment;    
    
    /**
     * function __construct
     * Object initial
     * @author channl
     * create: 2019/10/15
     * update: 2019/10/15
     */
    public function __construct(){    
        $this->Purchase = new Purchase();
        $this->Order = new Order();
        $this->OrderDetail = new OrderDetail();
        $this->Shipment = new Shipment();
    }

    /**
     * function getRecordImport
     * Description: Hàm lấy danh sách shipbill import
     * @author chan_nl
     * Created: 2020/06/01
     * Updated: 2020/06/01
     */
    public function getRecordImport($query_shipbill){
        $query = $this->Order;
        $query = $query->selectRaw('orders.id as o_id, order_details.id as od_id, purchases.id as pur_id, shipments.id as ship_id,
                                    orders.order_code, purchases.purchase_code, shipments.shipment_code, shipments.delivery_method,
                                    purchases.status, shipments.shipment_date')
                        ->join('order_details', 'order_details.order_id', 'orders.id')
                        ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                        ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                        ->whereRaw($query_shipbill)
                        ->get()->toArray();
        return $query;
    }

    /**
     * function getRecordImport
     * Description: Hàm update thông tin shipment
     * @author chan_nl
     * Created: 2020/06/02
     * Updated: 2020/06/02
     */
    public function updateRecordImport($data){
        try{
            $query_purchase = $this->Purchase;
            $query_order_detail = $this->OrderDetail;
            $query_shipment = $this->Shipment;
            DB::beginTransaction();
            foreach($data as $val){
                if($val['purchase_status'] != ""){                    
                    $update_purchase = $query_purchase->where('id', $val['purchase_id'])->update([
                        'status' => $val['purchase_status']
                    ]);
                }
                if($val['shipment_code'] != ""){                    
                    if($val['delivery_method'] != ""){
                        $update_order_detail = $query_order_detail->where('id', $val['order_detail_id'])->update([
                            'delivery_method' => $val['delivery_method'],
                            'delivery_date' => $val['shipment_date'],
                            'receive_date' => $val['shipment_date']
                        ]);                        
                        $update_shipment = $query_shipment->where('id', $val['shipment_id'])->update([
                            'shipment_code' => $val['shipment_code'],
                            'delivery_method' => $val['delivery_method'],
                            'shipment_date' => $val['shipment_date'],
                        ]);
                    }else {
                        $update_order_detail = $query_order_detail->where('id', $val['order_detail_id'])->update([
                            'delivery_date' => $val['shipment_date'],
                            'receive_date' => $val['shipment_date']
                        ]);                    
                        $update_shipment = $query_shipment->where('id', $val['shipment_id'])->update([
                            'shipment_code' => $val['shipment_code'],
                            'shipment_date' => $val['shipment_date'],
                        ]);
                    }
                }else {                    
                    if($val['delivery_method'] != ""){
                        $update_order_detail = $query_order_detail->where('id', $val['order_detail_id'])->update([
                            'delivery_method' => $val['delivery_method'],
                            'delivery_date' => $val['shipment_date'],
                            'receive_date' => $val['shipment_date']
                        ]);
                        $update_shipment = $query_shipment->where('id', $val['shipment_id'])->update([
                            'delivery_method' => $val['delivery_method'],
                            'shipment_date' => $val['shipment_date'],
                        ]);
                    }else {
                        $update_order_detail = $query_order_detail->where('id', $val['order_detail_id'])->update([
                            'delivery_date' => $val['shipment_date'],
                            'receive_date' => $val['shipment_date']
                        ]);
                        $update_shipment = $query_shipment->where('id', $val['shipment_id'])->update([
                            'shipment_date' => $val['shipment_date'],
                        ]);
                    }
                }
            }
            DB::commit();
            return [
                'status' => true,
                'message' => '送り状番号ファイルを取込みました。'
            ];
        }catch(Exception $e){
            Log::info($e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Query error'
            ];
        }
    }

    /**
     * Function getTotalOrder
     * Thống kê order trong khoảng thời gian date_from - date_to
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @author Chan
     * create: 2019/10/15
     * update: 2019/10/15
     */
    public function getTotalOrder($range, $date_from, $date_to)
    {
        try {
            $query = $this->Order;
            // query bảng order detail: nếu cùng 1 order có nhiều nhà cung cấp thì sum order đó lại
            $sql = "
                sum(case when orders.status = 1 then 1 else 0 end) as status_1,
                sum(case when orders.status = 2 then 1 else 0 end) as status_2,
                sum(case when orders.status = 3 then 1 else 0 end) as status_3,
                sum(case when orders.status = 4 then 1 else 0 end) as status_4,
                sum(case when orders.status = 5 then 1 else 0 end) as status_5,
                sum(case when orders.status = 6 then 1 else 0 end) as status_6,
                sum(case when orders.status = 8 then 1 else 0 end) as status_8,
                sum(case when orders.flag_confirm = 1 then 1 else 0 end) as flag_confirm_1,
                sum(case when orders.flag_confirm = 2 then 1 else 0 end) as flag_confirm_2
            ";
            // query thống kê: nếu sum > 0 (1 order có 2 dòng order detail) thì lấy 1 else lấy 0
            $sub_sql = "
                count(*) as total,
                sum(case when status_1 > 0 then 1 else 0 end ) as status_1,
                sum(case when status_2 > 0 then 1 else 0 end ) as status_2,
                sum(case when status_3 > 0 then 1 else 0 end ) as status_3,
                sum(case when status_4 > 0 then 1 else 0 end ) as status_4,
                sum(case when status_5 > 0 then 1 else 0 end ) as status_5,
                sum(case when status_6 > 0 then 1 else 0 end ) as status_6,
                sum(case when status_8 > 0 then 1 else 0 end ) as status_8,
                sum(case when flag_confirm_1 > 0 then 1 else 0 end ) as flag_confirm_1,
                sum(case when flag_confirm_2 > 0 then 1 else 0 end ) as flag_confirm_2
            ";
            $query = $query->select(
                DB::raw($sub_sql)
            );
            if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order               
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                     ->join('order_details', 'orders.id', 'order_details.order_id')
                                     ->whereBetween(DB::raw('CAST(orders.order_date AS DATE)'), [$date_from, $date_to])
                                     ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                     ->groupBy('orders.id')
                                     ->orderBy('orders.id');
                        },'foo');
            }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import         
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                     ->join('order_details', 'orders.id', 'order_details.order_id')
                                    ->join('imports', 'imports.id', 'orders.import_id')
                                    ->whereBetween(DB::raw('CAST(imports.date_import AS DATE)'), [$date_from, $date_to])
                                    ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                    ->groupBy('orders.id')
                                    ->orderBy('orders.id');
                        },'foo');
            }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCCt         
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                    ->join('order_details', 'orders.id', 'order_details.order_id')
                                    ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                                    ->whereBetween(DB::raw('CAST(orders.purchase_date AS DATE)'), [$date_from, $date_to])
                                    ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                    ->groupBy('orders.id')
                                    ->orderBy('orders.id');
                        },'foo');
            }else if($range == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng  
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                    ->join('order_details', 'orders.id', 'order_details.order_id')
                                    ->whereBetween(DB::raw('CAST(orders.delivery_date AS DATE)'), [$date_from, $date_to])
                                    ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                    ->groupBy('orders.id')
                                    ->orderBy('orders.id');
                        },'foo');
            }else if($range == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng                            
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                     ->join('order_details', 'orders.id', 'order_details.order_id')
                                     ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                                     ->whereBetween(DB::raw('CAST(shipments.es_shipment_date AS DATE)'), [$date_from, $date_to])
                                     ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                     ->groupBy('orders.id')
                                     ->orderBy('orders.id');
                        },'foo');
            }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng                    
                $query = $query->from(function($subquery) use ($date_from, $date_to, $sql){
                    return  $subquery->from('orders')->select('orders.id as o_id', DB::raw($sql))
                                     ->join('order_details', 'orders.id', 'order_details.order_id')
                                     ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                                     ->whereBetween(DB::raw('CAST(shipments.shipment_date AS DATE)'), [$date_from, $date_to])
                                     ->where('orders.status', '<>', '7') // Dat Add check status theo doi xong
                                     ->groupBy('orders.id')
                                     ->orderBy('orders.id');
                        },'foo');
            }
            return $query->get()->toArray();
        }catch (Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * Function getListShipmentBySiteType
     * status = 5: đang xl xuất hàng
     * flag_confirm = 1: data cần xác nhận
     * flag_confirm = 2: data đang bảo lưu
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @author Chan
     * create: 2019/10/15
     * update: 2019/12/11
     */
    public function getListShipmentBySiteType($range, $date_from, $date_to)
    {
        try{
            $query = $this->Order;
            $str_sub_sql = "
                site_type.id, site_type.name,
                sum(case when status_o3_p3 > 0 then 1 else 0 end ) as status_o3_p3,
                sum(case when status_o4_p3 > 0 then 1 else 0 end ) as status_o4_p3,
                sum(case when status_o5_p3 > 0 then 1 else 0 end ) as status_o5_p3,
                sum(case when orther_1 > 0 then 1 else 0 end ) as orther_1,
                sum(case when orther_2 > 0 then 1 else 0 end ) as orther_2,
                sum(case when orther_3 > 0 then 1 else 0 end ) as orther_3
            ";
            $str_sql = "";
            if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }else if($range == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }else if($range == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng
                $str_sql = "
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p3,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p3,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p3,
                            sum(case when purchases.status = 3 AND orders.status = 3 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_1,
                            sum(case when purchases.status = 3 AND orders.status = 4 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_2,
                            sum(case when purchases.status = 3 AND orders.status = 5 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."'
                            AND orders.site_type = 0 then 1 else 0 end) as orther_3";
            }          
            $query = $query->select(DB::raw($str_sub_sql));
            $query = $query->from(function($subquery) use ($range, $str_sql){
                $subquery = $subquery->from('orders')->join('order_details', 'orders.id', 'order_details.order_id') 
                                                     ->join('purchases', 'purchases.id', 'order_details.purchase_id');
                if($range == 1){
                    $subquery = $subquery->join('imports', 'imports.id', 'orders.import_id');
                }else if ($range >= 4){
                    $subquery = $subquery->join('shipments', 'shipments.id', 'order_details.shipment_id');
                }
                return  $subquery->select('order_details.id as od_id', 'orders.site_type as s_id', DB::raw($str_sql))
                                 ->groupBy('od_id', 's_id');
            },'foo');
            $query = $query->rightJoin('site_type', 'site_type.id', 'foo.s_id')
                           ->groupBy('site_type.id')->orderBy('site_type.id', 'desc');           
            return $query->get()->toArray();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * function getListSupplierBySiteType
     * Description: 
     * - Hàm lấy danh sách nhà cung cấp để xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng ở màn hình 6
     * - Lấy danh sách order xuất thông báo gửi hàng
     */
    public function getListSupplierBySiteType($request = null){
        $query = $this->Order;
        try {
            $query = $query->selectRaw('orders.id, orders.order_code, shipments.shipment_code, shipments.shipment_date, shipments.es_shipment_date,
                                        order_details.quantity as shipment_quantity, order_details.product_code,
                                        shipments.delivery_method, order_details.id as detail_id, order_details.supplied_id as supplied_id,
                                        order_details.supplied as supplied, purchases.id as purchase_id, purchases.purchase_code, purchases.status as purchase_status, order_details.delivery_date');
            $query = $query->join('order_details', 'orders.id','order_details.order_id')
                           ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                           ->join('shipments', 'order_details.shipment_id', 'shipments.id');
            if(!empty($request['list_details']))
            {
                if(gettype($request['list_details']) == 'string')
                {
                    $string_list_details = trim($request['list_details'], ',');
                    $array_list_details = explode(',', $string_list_details);
                }else
                {
                    $array_list_details = $request['list_details'];
                }
                $query = $query->whereIn('order_details.id', $array_list_details);
            } else
            {
                $query = $query->where('orders.site_type', $request['site_type']);
                if($request['range'] == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                    $query = $query->whereBetween(DB::raw('CAST(orders.order_date AS DATE)'),[$request['date_from'], $request['date_to']]);
                }else if($request['range'] == 1){//nếu radio = 1 thì thống kê theo ngày import
                    $query = $query->join('imports', 'imports.id', 'orders.import_id')->whereBetween(DB::raw('CAST(imports.date_import AS DATE)'),[$request['date_from'], $request['date_to']]);
                }else if($request['range'] == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC
                    $query = $query->whereBetween(DB::raw('CAST(orders.purchase_date AS DATE)'),[$request['date_from'], $request['date_to']]);
                }else if($request['range'] == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng
                    $query = $query->whereBetween(DB::raw('CAST(orders.delivery_date AS DATE)'),[$request['date_from'], $request['date_to']]);
                }else if($request['range'] == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng
                    $query = $query->whereBetween(DB::raw('CAST(shipments.es_shipment_date AS DATE)'),[$request['date_from'], $request['date_to']]);
                }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng
                    $query = $query->whereBetween(DB::raw('CAST(shipments.shipment_date AS DATE)'),[$request['date_from'], $request['date_to']]);
                }
                $arr_stage = [];
                if ($request['stage1'] != 1 && $request['stage2'] == 2){//Có check bỏ chọn đang bỏa lưu
                    array_push($arr_stage, 3, 4);
                } else if($request['stage1'] == 1 && $request['stage2'] != 2){//Có check bỏ chọn cần xác nhận
                    array_push($arr_stage, 3, 5);
                } else if($request['stage1'] == 1 && $request['stage2'] == 2)  {//Có check bỏ chọn đang bảo lưu và cấn xác nhận
                    array_push($arr_stage, 3);
                }else {
                    array_push($arr_stage, 3, 4, 5);
                }
                if(!empty($arr_stage))
                {
                    $query = $query->where('purchases.status', 3)->whereIn('orders.status', $arr_stage);
                }
            }

            return $query->orderBy(DB::raw('orders.id, order_details.id'), 'asc')->get()->toArray();
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }

    /**
     * function updateStatusAtShipmentNotification
     * Description: 
     * - Cập nhật tình trạng hỗ trợ
     */
    public function updateStatusAtShipmentNotification($arr_purchase){
        $current = Carbon::now()->format('Y-m-d');
        DB::beginTransaction();
        try{
            $this->Purchase->whereIn('id', $arr_purchase)->update(['status' => 4]);
            $list_order = $this->Purchase->join('order_details', 'purchases.id', 'order_details.purchase_id')
                                        ->select('order_details.order_code')
                                        ->groupBy('order_details.order_code')
                                        ->whereIn('order_details.purchase_id', $arr_purchase)
                                        ->pluck('order_details.order_code')->toArray();
            if(!empty($list_order)){
                $this->Order->whereIn('order_code', $list_order)->update(['delivery_date' => $current]);
            }
            DB::commit();
        }catch(Exception $e){            
            DB::rollBack();
        }   
    }
}