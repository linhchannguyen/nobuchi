<?php
// set namspace Purchaseservice
namespace App\Repositories\Services\Purchase;

use App\Model\Purchases\Purchase;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class PurchaseService implements PurchaseServiceContract
{
    /**
     * class PurchaseService implements PurchaseserviceContract
     * connect model and controller
     * get data from database
     * @author Chan
     * create: 2019/10/25
     * update: 2019/10/25
     */
    protected $Purchase;
    protected $Order;
    protected $OrderDetail;

    /**
     * function __construct
     * Bbject initial
     * @author channl
     * create: 2019/10/25
     * update: 2019/10/25
     */
    public function __construct(){
        $this->Purchase = new Purchase();
        $this->Order = new Order();
        $this->OrderDetail = new OrderDetail();
    }

    /**
     * Function getTotalOrder
     * Thống kê order trong khoảng thời gian date_from - date_to
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @author Chan
     * create: 2019/10/25
     * update: 2019/10/25
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
            }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC         
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
     * Function getListPurchaseBySupplier
     * Thống kê order status: 3, flag_confirm: 1, 2 theo phương thức giao hàng trong khoảng thời gian date_from - date_to
     * status = 3: đang xl đặt hàng
     * flag_confirm = 1: data cần xác nhận
     * flag_confirm = 2: data đang bảo lưu
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @author channl
     * create: 2019/10/25
     * update: 2019/10/25
     */
    public function getListPurchaseBySupplier($range, $date_from, $date_to)
    {
        try{
            $query = $this->Order;
            $str_sql_ = "
                suppliers.id, suppliers.name,
                sum(case when status_o3_p1 > 0 then 1 else 0 end) as status_o3_p1,
                sum(case when status_o4_p1 > 0 then 1 else 0 end) as status_o4_p1,
                sum(case when status_o5_p1 > 0 then 1 else 0 end) as status_o5_p1";
            $str_sql = "";
            if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }else if($range == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }else if($range == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng
                $str_sql = "
                            sum(case when purchases.status = 1 and orders.status = 3 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p1,
                            sum(case when purchases.status = 1 and orders.status = 4 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p1,
                            sum(case when purchases.status = 1 and orders.status = 5 AND CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p1";
            }
            $query = $query->select(DB::raw($str_sql_));
            $query = $query->from(function($subquery) use ($range, $str_sql){
                $subquery = $subquery->from('orders')->join('order_details', 'orders.id', 'order_details.order_id')
                                                     ->join('purchases', 'purchases.id', 'order_details.purchase_id');
                if($range == 1){
                    $subquery = $subquery->join('imports', 'imports.id', 'orders.import_id');
                }else if ($range >= 4){
                    $subquery = $subquery->join('shipments', 'shipments.id', 'order_details.shipment_id');
                }
                return  $subquery->select('order_details.id', 'order_details.supplied_id as s_id', DB::raw($str_sql))
                                 ->groupBy('order_details.id', 'order_details.supplied_id');//Nếu order detail cùng NCC và cùng mã order thì group lại lấy 1 để thống kê
                    },'foo');
            $query = $query->join('suppliers', 'suppliers.id', 'foo.s_id')
                           ->groupBy('suppliers.id')
                           ->orderBy('suppliers.id');
            return $query->get()->toArray();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * Function exportPurchase
     * Gửi giấy đặt hàng
     * status = 3: đang xl đặt hàng
     * flag_confirm = 1: data cần xác nhận
     * flag_confirm = 2: data đang bảo lưu
     * es_delivery_date: ngày tập kết hàng
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @param $supplier_id nhà cung cấp
     * @author channl
     * create: 2019/11/12
     * update: 2019/11/12
     */
    public function exportPurchase($date_from = null, $date_to = null, $supplier_id = null, $range = null, $order_detail_id = [], $stage1 = null, $stage2) {
        try{
            $query = $this->Order;
            $query = $query->select('orders.id', 'orders.money_daibiki', 'orders.comments', 'orders.order_date', 'order_details.id as order_detail_id', 'orders.order_code', 'orders.status', 'orders.flag_confirm',
                                    'order_details.supplied', 'order_details.supplied_id', 'purchases.id as purchase_id', 'purchases.status as purchase_status', 'purchases.purchase_code', 'orders.purchase_date',
                                    'purchases.cost_price', 'purchases.cost_price_tax', 'purchases.total_cost_price as total_price', 'purchases.total_cost_price_tax as total_price_tax',
                                    'order_details.product_name', 'order_details.product_code', 'order_details.quantity_set', 'order_details.product_info', 'order_details.maker_id', 'order_details.maker_code',
                                    'order_details.quantity', 'orders.buyer_zip1', 'orders.buyer_zip2', 'wrapping_paper_type', 'gift_wrap', 'message', 'order_details.shipment_id',
                                    DB::raw('CONCAT(orders.buyer_address_1, orders.buyer_address_2, orders.buyer_address_3) as buyer_add'),
                                    DB::raw('CONCAT(order_details.ship_address1, order_details.ship_address2, order_details.ship_address3) as ship_add'),
                                    DB::raw('CONCAT(orders.buyer_name1, orders.buyer_name2) as buyer_name'), 
                                    DB::raw('COALESCE(shipments.shipment_customer, order_details.ship_name1) as ship_name'),
                                    DB::raw('orders.buyer_tel1, orders.buyer_tel2, orders.buyer_tel3'), 'order_details.ship_phone',
                                    DB::raw('CAST(shipments.es_shipment_date AS DATE) as es_delivery_date'),
                                    DB::raw('CAST(shipments.shipment_date AS DATE)'),
                                    DB::raw('COALESCE(shipments.delivery_way, order_details.delivery_way) as delivery_way'),
                                    DB::raw('COALESCE(shipments.shipment_zip, order_details.ship_zip) as ship_zip'),
                                    'shipments.delivery_method as delivery_method', 'products.note',
                                    'shipments.shipment_time', 'shipments.es_shipment_time',  'shipments.shipment_code', 'shipments.pay_request', 'shipments.shipment_fee', 'order_details.message');
            $query = $query->join('order_details', 'orders.id', 'order_details.order_id')
                           ->join('products', 'products.product_id', 'order_details.product_id')
                           ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                           ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                           ->distinct();
            if(!empty($order_detail_id)){
                $query = $query->whereIn('order_details.id', $order_detail_id);
            }else {
                $query = $query->where('order_details.supplied_id', $supplier_id);
                if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                    $query = $query->whereBetween(DB::raw('CAST(orders.order_date AS DATE)'),[$date_from, $date_to]);
                }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import
                    $query = $query->join('imports', 'imports.id', 'orders.import_id');
                    $query = $query->whereBetween(DB::raw('CAST(imports.date_import AS DATE)'),[$date_from, $date_to]);
                }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC
                    $query = $query->whereBetween(DB::raw('CAST(orders.purchase_date AS DATE)'),[$date_from, $date_to]);
                }else if($range == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng
                    $query = $query->whereBetween(DB::raw('CAST(orders.delivery_date AS DATE)'),[$date_from, $date_to]);
                }else if($range == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng
                    $query = $query->whereBetween(DB::raw('CAST(shipments.es_shipment_date AS DATE)'),[$date_from, $date_to]);
                }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng
                    $query = $query->whereBetween(DB::raw('CAST(shipments.shipment_date AS DATE)'),[$date_from, $date_to]);
                }
                $arr_stage = [];
                if ($stage1 != 1 && $stage2 == 2){//Có check bỏ chọn đang bỏa lưu
                    array_push($arr_stage, 3, 4);
                } else if($stage1 == 1 && $stage2 != 2){//Có check bỏ chọn cần xác nhận
                    array_push($arr_stage, 3, 5);
                } else if($stage1 == 1 && $stage2 == 2)  {//Có check bỏ chọn đang bảo lưu và cấn xác nhận
                    array_push($arr_stage, 3);
                }else {
                    array_push($arr_stage, 3, 4, 5);
                }
                if(!empty($arr_stage))
                {
                    $query = $query->where('purchases.status', 1)->whereIn('orders.status', $arr_stage);
                }
            }
            
            // return $query->orderBy(DB::raw('orders.id, order_details.id'), 'asc')->get();
            return $query->orderBy(DB::raw('orders.purchase_date'), 'asc')->get()->toArray();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }   

    /**
     * function updateStatusAtShipment()
     * Description: Hàm cập nhật trạng thái order khi chọn thay đổi tình trạng hỗ trợ
     * từ đang xử lý đóng gói -> đang xử lý xuất hàng
     * @param order_id id của order
     * Created: 2019/12/17
     * Updated: 2019/12/17
     */
    public function updateStatusAtPurchase($arr_purchase_update = null, $screen = null){
        $current = Carbon::now()->format('Y-m-d');
        DB::beginTransaction();
        try{
            $status = 1;
            if($screen == 5 || $screen == 22){
                $status = 2;
            }else if($screen == 6){
                $status = 3;
            }else if ($screen == 7){
                $status = 4;
            }
            $update_purchase = $this->Purchase->whereIn('id', $arr_purchase_update)->update(['status' => $status]);
            $list_order = $this->Purchase->join('order_details', 'purchases.id', 'order_details.purchase_id')
                                        ->select('order_details.order_code')
                                        ->groupBy('order_details.order_code')
                                        ->whereIn('order_details.purchase_id', $arr_purchase_update)
                                        ->pluck('order_details.order_code')->toArray();
            if(!empty($list_order)){
                if($status == 2){
                    $this->Order->whereIn('order_code', $list_order)->update(['purchase_date' => $current]);
                }else if($status == 4){
                    $this->Order->whereIn('order_code', $list_order)->update(['delivery_date' => $current]);
                }
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
        }        
    }
}