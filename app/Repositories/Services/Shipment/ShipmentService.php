<?php
// set namspace shipmentservice
namespace App\Repositories\Services\Shipment;

use App\Model\Shipments\Shipment;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ShipmentService implements ShipmentServiceContract
{
    /**
     * class ShipmentService implements shipmentserviceContract
     * connect model and controller
     * get data from database
     * @author Chan
     * create: 2019/10/03
     * update: 2019/10/08
     */
    protected $Purchase;
    protected $Shipment;
    protected $Order;
    protected $OrderDetail;

    /**
     * function __construct
     * Bbject initial
     * @author channl
     * create: 2019/10/15
     * update: 2019/10/15
     */
    public function __construct(){
        $this->Purchase = new Purchase();
        $this->Shipment = new Shipment();
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
     * create: 2019/10/03
     * update: 2019/10/08
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
     * Function getListShipmentByDeliveryMethod
     * Thống kê order theo phương thức giao hàng trong khoảng thời gian date_from - date_to
     * status = 4: đang xl đóng gói
     * flag_confirm = 1: data cần xác nhận
     * flag_confirm = 2: data đang bảo lưu
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @author Chan
     * create: 2019/10/03
     * update: 2019/10/08
     */
    public function getListShipmentByDeliveryMethod($range, $date_from, $date_to)
    {
        try{
            $query = $this->Order;
            $str_sql_ = "
                delivery_methods.id, delivery_methods.delivery_name,
                sum(case when status_o3_p2 > 0 then 1 else 0 end) as status_o3_p2,
                sum(case when status_o4_p2 > 0 then 1 else 0 end) as status_o4_p2,
                sum(case when status_o5_p2 > 0 then 1 else 0 end) as status_o5_p2";
            $str_sql = "";
            if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(orders.order_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(imports.date_import AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }else if($range == 2){//nếu radio = 2 thì thống kê theo ngày đặt hàng NCC
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(orders.purchase_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }else if($range == 3){//nếu radio = 3 thì thống kê theo ngày xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(orders.delivery_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }else if($range == 4){//nếu radio = 4 thì thống kê theo ngày dự định xuất hàng
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(shipments.es_shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }else{//nếu radio = 5 thì thống kê theo ngày dự định nhận hàng
                $str_sql = "
                            sum(case when purchases.status = 2 and orders.status = 3 and CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o3_p2,
                            sum(case when purchases.status = 2 and orders.status = 4 and CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o4_p2,
                            sum(case when purchases.status = 2 and orders.status = 5 and CAST(shipments.shipment_date AS DATE) BETWEEN '".$date_from."' AND '".$date_to."' then 1 else 0 end) as status_o5_p2";
            }
            $query = $query->select(DB::raw($str_sql_));
            $query = $query->from(function($subquery) use ($range, $str_sql){
                $subquery = $subquery->from('orders')->join('order_details', 'orders.id', 'order_details.order_id')
                                                     ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                                                     ->join('shipments', 'shipments.id', 'order_details.shipment_id');
                if($range == 1){
                    $subquery = $subquery->join('imports', 'imports.id', 'orders.import_id');
                }
                // else if ($range >= 4){
                //     $subquery = $subquery->join('shipments', 'shipments.id', 'order_details.shipment_id');
                // }
                return  $subquery->select('order_details.id', 'shipments.delivery_method as dm_id', DB::raw($str_sql))
                                 ->groupBy('order_details.id', 'shipments.delivery_method');
            },'foo');
            $query = $query->rightJoin('delivery_methods', 'delivery_methods.id', 'foo.dm_id')
                           ->groupBy('delivery_methods.id')->orderBy('delivery_methods.id');
            return $query->get()->toArray();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * Function exportShipment
     * Gửi giấy xuất hàng
     * status = 4: đang xl đóng gói
     * flag_confirm = 1: data cần xác nhận
     * flag_confirm = 2: data đang bảo lưu
     * @param $date_from tìm kiếm từ ngày $date_from
     * @param $date_to tìm kiếm đến ngày $date_to
     * @param $range phạm vi tìm kiếm
     * @param $delivery_method phương thức giao hàng
     * @author channl
     * create: 2019/11/29
     * update: 2019/11/29
     */
    public function exportShipment($list_order_details = [], $date_from = null, $date_to = null, $delivery_method = null, $range = null, $stage1 = null, $stage2){
        try{
            $query = $this->Order;         
            $sub_group1 = '(select groups.name from products join groups on groups.id = products.group1_id where order_details.product_id = products.product_id
                            and order_details.product_code = products.code and products.product_del_flg = 0 and products.product_class_del_flg = 0 and products.status = 1 limit 1) as g_name1';
            $sub_group2 = '(select groups.name from products join groups on groups.id = products.group2_id where order_details.product_id = products.product_id
                            and order_details.product_code = products.code and products.product_del_flg = 0 and products.product_class_del_flg = 0 and products.status = 1 limit 1) as g_name2';
            $sub_group3 = '(select groups.name from products join groups on groups.id = products.group3_id where order_details.product_id = products.product_id
                            and order_details.product_code = products.code and products.product_del_flg = 0 and products.product_class_del_flg = 0 and products.status = 1 limit 1) as g_name3';
            $sub_group4 = '(select groups.name from products join groups on groups.id = products.group4_id where order_details.product_id = products.product_id
                            and order_details.product_code = products.code and products.product_del_flg = 0 and products.product_class_del_flg = 0 and products.status = 1 limit 1) as g_name4';
            $sub_group5 = '(select groups.name from products join groups on groups.id = products.group5_id where order_details.product_id = products.product_id
                            and order_details.product_code = products.code and products.product_del_flg = 0 and products.product_class_del_flg = 0 and products.status = 1 limit 1) as g_name5';
            $query = $query->select('site_type.name', 'order_details.id as detail_id', 'order_details.receive_date', 'order_details.receive_time',
                                    'orders.id as order_id', 'orders.order_code', 'order_details.cost_price', 'order_details.cost_price_tax',
                                    'order_details.total_price', 'order_details.total_price_tax', 'shipments.delivery_method', 'order_details.delivery_way',
                                    'purchases.id as purchase_id', 'purchases.purchase_code as purchase_code', 'purchases.status as purchase_status' ,
                                    DB::raw('CONCAT(orders.buyer_name1, orders.buyer_name2) as buyer'), 'orders.buyer_name1 as export_buyer_name1', 'orders.buyer_name2 as export_buyer_name2',
                                    'suppliers.name as export_supplier_name', 'suppliers.supplier_code_sagawa', 'suppliers.tel01 as sup_tel01', 'suppliers.tel02 as sup_tel02',
                                    'suppliers.tel03 as sup_tel03', 'suppliers.pref as export_add_01', 'suppliers.addr01 as export_add_02', 'suppliers.addr02 as export_add_03',
                                    'suppliers.zip01', 'suppliers.zip02', DB::raw('CONCAT(orders.buyer_address_1, orders.buyer_address_2, orders.buyer_address_3) as buyer_add'), 
                                    'orders.buyer_address_1 as export_buyer_address_1', 'orders.buyer_address_2 as export_buyer_address_2', 'orders.buyer_address_3 as export_buyer_address_3',
                                    'orders.buyer_zip1', 'orders.buyer_zip2', 'orders.buyer_tel1', 'orders.buyer_tel2', 'orders.buyer_tel3', 'imports.date_import', 
                                    DB::raw('order_details.ship_name1 as ship_name'), DB::raw('order_details.ship_name2 as ship_name2'),
                                    DB::raw('CONCAT(order_details.ship_address1, order_details.ship_address2, order_details.ship_address3) as ship_add'), 
                                    'order_details.ship_address1 as export_ship_add1', 'order_details.ship_address2 as export_ship_add2', 'order_details.ship_address3 as export_ship_add3',
                                    'order_details.ship_zip', 'order_details.ship_phone', 'order_details.product_id', 'order_details.product_code', 'order_details.product_name',
                                    'order_details.quantity', DB::raw($sub_group1), DB::raw($sub_group2), DB::raw($sub_group3), DB::raw($sub_group4), DB::raw($sub_group5),
                                    'shipments.shipment_code', 'shipments.shipment_date', 'shipments.es_shipment_date  as export_es_ship_date', 'shipments.es_shipment_time as export_es_ship_time',
                                    'shipments.shipment_time', 'orders.money_daibiki','order_details.delivery_date', 'shipments.shipment_fee', 'shipments.shipment_customer', 'shipments.shipment_phone'
                                );
            $query = $query->join('order_details', 'orders.id', 'order_details.order_id')
                           ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                           ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                           ->join('site_type', 'site_type.id', 'orders.site_type')
                           ->join('suppliers', 'suppliers.id', 'order_details.supplied_id')
                           ->join('products', 'products.product_id', 'order_details.product_id')
                           ->leftJoin('imports', 'imports.id', 'orders.import_id');
            if(!empty($list_order_details))
            {
                $query = $query->whereIn('order_details.id', $list_order_details);
            } else {
                $query = $query->where('shipments.delivery_method', $delivery_method);
                if($range == 0){//nếu radio = 0 thì thống kê theo ngày đặt hàng order
                    $query = $query->whereBetween(DB::raw('CAST(orders.order_date AS DATE)'),[$date_from, $date_to]);
                }else if($range == 1){//nếu radio = 1 thì thống kê theo ngày import
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
                    $query = $query->where('purchases.status', 2)->whereIn('orders.status', $arr_stage);
                }
            }
            return $query->orderBy(DB::raw('orders.id, order_details.product_id'))->get();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }
    /**
     * function getListProductStatus
     * Description: get list product_id in product_statuses table
     * @author chan_nl
     * Created: 2020/11/02
     * Updated: 2020/11/02
     */
    public function getListProductStatus($arr_product_id){
        return DB::table('product_statuses')->whereIn('product_id', $arr_product_id)
                ->whereIn('product_status_id', [6,7])
                ->orderBy('product_id')->get()->toArray();
    }

    /**
     * function đánh số bill tự động
     * @author Dat
     * 2019/12/10
     * quy tắc đánh số bill gửi hàng tự động của [佐川急便 Sagawa] như sau.
     * [phạm vi đánh số]
     * 50133092000*　～　50133141999*   (50.000 số)
     * ※ dấu [*] là Check digit. lấy 11 số đầu chia cho 7 ra phần dư thì hãy set phần dư này vào vị trí thứ 12.
     * ví dụ: trường hợp là số 50133092000* thì 50133092000 - (int(50133092000/7）* 7)＝5.
     * nếu dùng hết 50.000 số (50133092000*　～　50133141999*) thì cho quay lại từ đầu (50133092000*　～　50133141999*).
     */
    public function checkNumber($string){
        for($i = 0; $i < strlen($string); $i++){
            if(is_numeric($string[$i]) == false){
                return false;
            }
        }
        return true;
    }
    public function getBillNumber($times = 0)
    {
        //get shipcode
        $shipCode = '';
        $queryShipCode = $this->Shipment
                    ->whereIn('delivery_method', [1, 7])
                    ->whereNotNull('shipment_code')
                    ->where('shipment_code', 'like', "50133%")
                    ->orderBy('shipment_code', 'desc')
                    ->get()->toArray();
        if(!empty($queryShipCode)){
            foreach($queryShipCode as $val){
                if(self::checkNumber($val['shipment_code'])){
                    $shipCode = $val['shipment_code'];
                    break;
                }
            }
        }
        if($shipCode == ''){
            $shipCode = 50133092000;
        }else {
            $shipCode = substr($shipCode, 0, 11);
        }
        if ($shipCode == 50133141999) {
            $constant_ship = 50133092000;
        } else {
            $constant_ship = $shipCode + $times;
        }

        $shipCode = intval($constant_ship/7);
        $shipCode = $constant_ship - ($shipCode*7);
        $shipCode = $constant_ship.$shipCode;

        return $shipCode;
    }

    /**
     * function updateOrderDetail
     * Description: Update shipment_id khi tạo mới giấy gửi hàng (shipment)
     * Created: 2019/12/17
     * Updated: 2019/12/17
     */
    public function updateOrderDetail($detail_id, $shipment_id){
        DB::beginTransaction();
        try{
            $query = $this->OrderDetail->where('id', $detail_id)->update(['shipment_id' => $shipment_id]);
            DB::commit();
        }catch(Exception $e){            
            DB::rollBack();
            Log::debug($e->getMessage());
        }     
    }

    /**
     * function getListSupplierByDeliveryMethod
     * Description: Hàm lấy danh sách nhà cung cấp để xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng ở màn hình 6
     * Created: 2019/12/10
     * Updated: 2019/12/10
     */
    public function getListSupplierByDeliveryMethod($request = null){
        $query = $this->Order;
        try {
            $query = $query->selectRaw('order_details.id as detail_id, order_details.supplied_id as supplied_id, order_details.supplied as supplied');
            $query = $query->join('order_details', 'orders.id','order_details.order_id')
                           ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                           ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                           ->where('shipments.delivery_method', $request['delivery_method']); // join với bảng details

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
                $query = $query->where('purchases.status', 2)->whereIn('orders.status', $arr_stage);
            }

            return $query->orderBy('order_details.supplied_id', 'asc')->get()->toArray();
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * function xuất thông báo xuất hàng sagawa
     * @author Dat
     * 20191218
     */
    public function ExportNotificationAmazon($data_details =[])
    {
        if(empty($data_details))
        {
            Log::info('cannot download data empty ShipmentService line 365');
            return [
                'status'=> false,
                'message' => 'CANNOT DATA DOWNLOAD'
            ];
        }
        $detailModel = new OrderDetail();
        $query = $detailModel;
        $query = $query->selectRaw("
                    order_details.order_id, order_details.order_code, order_details.product_id, order_details.product_code, order_details.quantity, 'Other' as delivery_providers,
                    shipments.delivery_method, shipments.shipment_fee as shipment_fee, purchases.id as purchase_id, purchases.purchase_code as purchase_code,
                    shipments.shipment_code,
                    COALESCE(date(shipments.shipment_date), date(order_details.delivery_date)) as shipment_date, shipments.es_shipment_date,
                    shipments.delivery_method as delivery_method
                    ")
                    //case when shipments.shipment_code like 'ヤマト運輸%' then ''
                    // when shipments.shipment_code like '日本郵便%' then ''
                    // when shipments.shipment_code like 'その他%' then ''
                    // else shipments.shipment_code END as shipment_code
                    ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                    ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                    ->whereIn('order_details.id', $data_details)
                    ->get()->toArray();
        return $query;
    }

    /**
     * function updateStatusAtShipment()
     * Description: Hàm cập nhật trạng thái order khi chọn thay đổi tình trạng hỗ trợ
     * từ đang xử lý đóng gói -> đang xử lý xuất hàng
     * @param order_id id của order
     * Created: 2019/12/17
     * Updated: 2019/12/17
     */
    public function updateStatusAtShipment($arr_purchase){
        DB::beginTransaction();
        try{
            foreach($arr_purchase as $purchase_id){
                $this->Purchase->where('id', $purchase_id)->update(['status' => 3]);
            }
            DB::commit();
        }catch(Exception $e){            
            DB::rollBack();
            Log::debug($e->getMessage());
        }        
    }
}