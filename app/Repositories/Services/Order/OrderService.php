<?php
// set namespace
namespace App\Repositories\Services\Order;

use App\Model\HistoryProcess\HistoryProcess;
use App\Model\Imports\Import;
use App\Model\Orders\ImportAmazonFbaHiroshima;
use App\Repositories\Services\Shipment\ShipmentServiceContract;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService implements OrderServiceContract
{
    /**
     * class Order service
     * @author Dat
     * 2019/10/07
     */
    private $order_model;
    private $purchase_model;
    private $his_process_model;
    private $ShipmentService;
    public function __construct(ShipmentServiceContract $ShipmentService)
    {
        $this->order_model = new Order();
        $this->purchase_model = new Purchase();        
        $this->his_process_model = new HistoryProcess();
        $this->modelShipment = new Shipment();
        $this->ShipmentService = $ShipmentService;
    }
    /**
     * function index search
     * @author Dat
     * 
     */
    public function index_search()
    {
        $order_model = new Order();
        return $order_model::get()->toArray();
    }
    /**
     * function get status total order
     * @author Dat
     * 2019/10/10
     */
    public function getTotalStatus ($request = null)
    {
        // status trạng thái order (1: chờ nhận tiền, 2: đang order, 3: đang xử lý đặt hàng, 4: đang xử lý đóng gói, 5: tạo xong bill gửi hàng, 6: thông báo xuất hàng xong, 7 : theo dõi xong, 8: hủy)
        // flag_confirm 0: không có( chưa đánh dấu), 1: cần xác nhận, 2: đang bảo lưu
        try {
            $query = $this->order_model;
            $query=$query->select(DB::raw("
                count(*) as total,
                sum(case when orders.status = '1' then 1 else 0 end ) as o_watting_money,
                sum(case when orders.status= '2' then 1 else 0 end ) as o_proccess,
                sum(case when orders.status='3' then 1 else 0 end ) as o_proccess_purchase,
                sum(case when orders.status='4' then 1 else 0 end ) as o_proccess_wrap,
                sum(case when orders.status='5' then 1 else 0 end ) as o_proccess_ship,
                sum(case when orders.status='6' then 1 else 0 end ) as o_ship_notified,
                sum(case when orders.status='8' then 1 else 0 end ) as o_del,
                sum(case when orders.flag_confirm='1' then 1 else 0 end ) as o_confirm,
                sum(case when orders.flag_confirm='2' then 1 else 0 end ) as o_save" 
            ));
            $query->join('order_details', 'orders.id','order_details.order_id'); // join với bảng order details
            $query = $this->__conditions($request, $query); // kiểm tra điều kiện tìm kiếm
            $query = $query->groupBy('orders.id')->orderBy('orders.id');// group by theo mã hóa đơn
            $tableTotal = $query; // set bảng thông kê theo order code
            $tableTotal = DB::table($tableTotal, 'tableTotal'); // đặt bảng thống kê theo groupby bằng tableTotal
            $tableTotal = $tableTotal->selectRaw("
                count(*) as total,
                sum(case when o_watting_money > 0 then 1 else 0 end) as o_watting_money,
                sum(case when o_proccess > 0 then 1 else 0 end) as o_proccess,
                sum(case when o_proccess_purchase > 0 then 1 else 0 end) as o_proccess_purchase,
                sum(case when o_proccess_wrap > 0 then 1 else 0 end) as o_proccess_wrap,
                sum(case when o_proccess_ship > 0 then 1 else 0 end) as o_proccess_ship,
                sum(case when o_ship_notified > 0 then 1 else 0 end) as o_ship_notified,
                sum(case when o_del > 0 then 1 else 0 end) as o_del,
                sum(case when o_confirm > 0 then 1 else 0 end) as o_confirm,
                sum(case when o_save > 0 then 1 else 0 end) as o_save"
            );
            $results = $tableTotal->get()->toArray(); // kết quả tính khi sum bảng order.
            $list_total = [];
            foreach($results as $total) {
                // push phần tử của từng giá trị của mảng thống kê
                array_push(
                    $list_total, array(
                        'total' => $total->total,
                        'o_watting_money' => $total->o_watting_money,
                        'o_proccess' => $total->o_proccess,
                        'o_proccess_purchase' => $total->o_proccess_purchase,
                        'o_proccess_wrap' => $total->o_proccess_wrap,
                        'o_proccess_ship' => $total->o_proccess_ship,
                        'o_ship_notified' => $total->o_ship_notified,
                        'o_del' => $total->o_del,
                        'o_confirm' => $total->o_confirm,
                        'o_save' => $total->o_save
                    )
                );
    
            }
            return $list_total;
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * function search status
     * get data from database
     * @author Dat
     * 2019/10/07
     */
    public function searchConditions($request = null)
    {   
        $query = $this->order_model;
        try {
            $query = $query->selectRaw('orders.id, orders.site_type, order_details.id as detail_id, orders.order_code, orders.buyer_name1, orders.buyer_name2,
            orders.support_cus, orders.flag_confirm, purchases.status as p_status, orders.status, order_details.product_name,
            shipments.id as shipment_id, shipments.delivery_method as shipment_deli, shipments.shipment_code,
            order_details.product_id, shipments.es_shipment_date, order_details.supplied_id as supplied_id,
            order_details.supplied as supplied_name, purchases.purchase_code, purchases.id as purchase_id');
            $query = $query->join('order_details', 'orders.id','order_details.order_id');// join với bảng order details
            $query = $this->__conditions($request, $query);
            return [
                'status' => true,
                'data' => $query->orderBy(DB::raw('cast(imports.date_import as date)'), 'desc')
                    ->orderBy(DB::raw('cast(orders.order_date as date)'), 'desc')
                    ->orderBy(DB::raw('order_details.order_code'), 'asc')
                    ->orderBy(DB::raw('purchases.purchase_code'), 'asc')->get()->toArray()
                ];
        } catch (Exception $exception) {
            return [
                'status' => false,
                'data' => "Not connect to Databases"
            ];
        }
    }
    /**
     * function update order of search order
     * @author Dat
     * 2019/10/15
     */
    public function updateOrder($request = null)
    {
        $query = $this->order_model;                      
        $hisProcess = $this->his_process_model;       
        $current = Carbon::now(); 
        $delivery_method = config('constants.DELIVERY_METHOD');
        $delivery_way = config('constants.DELIVERY_WAY');
        $order_status = config('constants.ORDER_STATUS');
        $purchase_status = config('constants.PURCHASE_STATUS');
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '注文検索';
        $str_process_description = '';
        DB::beginTransaction();// start transaction database
        try{
            // update status
            if(isset($request['status_support']) && $request['status_support'] != '')
            {
                $str_process_description .= '<b>変更</b>:<br>受注ステータス: -> '.$order_status[$request['status_support']].'<br>';
                 $query->whereIn('order_code', $request['list_orders'])->update(['status' => $request['status_support']]);
            }
            // update flag_cofirm
            if(isset($request['flag_confirm']) && $request['flag_confirm'] != '')
            {
                $str_process_description .= '<b>変更</b>:<br>発注ステータス: -> '.$purchase_status[$request['flag_confirm']-1].'<br>';
                $status = intval($request['flag_confirm']);
                if($status == 2){
                    $this->purchase_model->whereIn('id', $request['list_purchases'])->update(['status' => $status]);
                    $this->order_model->whereIn('order_code', $request['list_orders'])->update(['purchase_date' => $current]);
                }if($status == 4){
                    $this->purchase_model->whereIn('id', $request['list_purchases'])->update(['status' => $status]);
                    $this->order_model->whereIn('order_code', $request['list_orders'])->update(['delivery_date' => $current]);
                }else {
                    $this->purchase_model->whereIn('id', $request['list_purchases'])->update(['status' => $status]);
                }
            }
            // update delivery_way
            if(isset($request['delivery_way']) && $request['delivery_way'] != '')
            {
                $str_process_description .= '<b>変更</b>:<br>納品方法: -> '.$delivery_way[$request['delivery_way']].'<br>';
                // update cách giao hàng
                DB::table('order_details')
                    ->whereIn('order_details.product_id', $request['list_products'])
                    ->whereIn('order_details.order_code', $request['list_orders'])
                    ->update(['order_details.delivery_way' => $request['delivery_way']]);

                $query_way = DB::table('orders')->join('order_details', 'order_details.order_id', 'orders.id')
                ->whereIn('order_details.product_id', $request['list_products'])
                ->whereIn('orders.order_code', $request['list_orders']);
                $update_way = $query_way->pluck('shipment_id');
                // check shipments id of order table is null
                if(count($update_way) < 0)
                {
                     DB::rollBack();
                    return [
                        "status" => true,
                        "message" => "shipment code is null",
                        "order" => $update_way
                    ];
                    Log::error('shipment code is null');
                }else {
                // update delivery method from shipments table
                DB::table('shipments')
                    ->whereIn('id', $update_way)
                    ->update(['shipments.delivery_way' => $request['delivery_way']]);
                }
            }
            // update delivery_method
            if(isset($request['delivery_method']) && $request['delivery_method'] != '')
            {
                $str_process_description .= '<b>変更</b>:<br>配送方法: -> '.$delivery_method[$request['delivery_method']].'<br>';
                
                //Update ship code màn hình tìm kiếm order khi đổi PTGH
                $get_shipment = DB::table('orders')->selectRaw('orders.order_code, orders.site_type, order_details.shipment_id, shipments.delivery_method, shipments.shipment_code')
                ->join('order_details', 'order_details.order_id', 'orders.id')
                ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                ->whereIn('order_details.order_code', $request['list_orders'])->orderBy('orders.order_code')->get()->toArray();
                $arr_update_shipment = [];
                $o_code = '';
                $arr_duplicate = [];
                foreach($get_shipment as $val){
                    if($val->order_code != $o_code)
                    {
                        $arr_collect = [];
                    }
                    array_push($arr_collect, $val);
                    if($val->order_code == $o_code)
                    {
                        array_pop($arr_duplicate);
                    }
                    $o_code = $val->order_code;
                    array_push($arr_duplicate, $arr_collect);
                }
                if(!empty($arr_duplicate)){
                    $delivery_method_update = $request['delivery_method'];
                    $check_shipment_search = [];//Mảng loại bỏ những shipment_id trùng
                    $arr_shipment_search = [];
                    foreach($request['list_shipments'] as $val_search){
                        if(!in_array($val_search['id'], $check_shipment_search)){//Kiểm tra nếu mảng chưa có shipment_id thì push vào
                            array_push($check_shipment_search, $val_search['id']);
                            array_push($arr_shipment_search, $val_search);//Loại bỏ những shipment_id trùng rồi push vào mảng mới
                        }
                    }
                    $times = 0;
                    $number_code = 0;
                    $arr_order = [];
                    $index = 1;
                    foreach($arr_shipment_search as $key_search => $val_search){
                        if($val_search['delivery_method'] != (int)$delivery_method_update){
                            $count_deli = 0;
                            $check_order_code = 0;//Kiểm tra xem có lấy đúng order_code trong cùng 1 web site không (trường hợp có order ở website khác cùng order_code)
                            $shipcode = '';
                            if($delivery_method_update == 7 || $delivery_method_update == 1){
                                $shipcode = $this->ShipmentService->getBillNumber($index++);
                            }else {
                                $shipcode = $delivery_method[$delivery_method_update];
                            }
                            
                            foreach($arr_duplicate as $key => $val_dup){
                                if($val_search['order_code'] == $arr_duplicate[$key][0]->order_code && $val_search['site_type'] == $arr_duplicate[$key][0]->site_type){
                                    $check_order_code++;
                                    foreach($val_dup as $val){
                                        if($val->delivery_method == (int)$delivery_method_update && $val->shipment_id != $val_search['id']){
                                            $count_deli++;
                                        }
                                    }
                                }
                            }
                            if($check_order_code > 0){
                                if(!in_array($val_search['order_code'], $arr_order)){
                                    array_push($arr_order, $val_search['order_code']);
                                    $number_code = 0;
                                }
                                $number_code++;
                                if($delivery_method_update == 7 || $delivery_method_update == 1){
                                    $times++;
                                }else {
                                    $shipcode = $shipcode.($count_deli+$number_code);
                                }
                                $item = [
                                    'id' => $val_search['id'],
                                    'code' => $shipcode
                                ];
                                array_push($arr_update_shipment, $item);
                            }
                        }
                    }
                }
                if(!empty($arr_update_shipment)){
                    foreach($arr_update_shipment as $val){
                        DB::table('shipments')
                            ->where('id', $val['id'])
                            ->update(['shipments.shipment_code' => $val['code']]);
                    }
                }
                //Update ship code màn hình tìm kiếm order khi đổi PTGH

                // update delivery method from order details
                DB::table('order_details')
                    ->whereIn('order_details.product_id', $request['list_products'])
                    ->whereIn('order_details.order_code', $request['list_orders'])
                    ->update(['order_details.delivery_method' => $request['delivery_method']]);

                $query_update = DB::table('order_details')
                ->whereIn('order_details.product_id', $request['list_products'])
                ->whereIn('order_details.order_code', $request['list_orders']);
                $check_bill = $query_update->pluck('shipment_id');
                // check shipments id of order table is null
                if(count($check_bill) < 0)
                {
                     DB::rollBack();
                    return [
                        "status" => true,
                        "message" => "shipment code is null",
                        "order" => $check_bill
                    ];
                    Log::error('shipment code is null');
                // update delivery method from shipments table
                }
                else {
                DB::table('shipments')
                    ->whereIn('id', $check_bill)
                    ->update(['shipments.delivery_method' => $request['delivery_method']]);
                }
            }
            if(count($request['list_orders'])){  
                for($i = 0; $i < count($request['list_orders']); $i++){
                    $str_process_description .= '受注ID: '.$request['list_orders'][$i].'('.$request['list_purchase_codes'][$i].')、';
                }
                $str_process_description = rtrim($str_process_description, '、');   
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            DB::commit(); // commit database update
            return [
                'status' => true,
                'message' => 'DONE' 
            ];
        }catch (Exception $exception)
        {
            DB::rollBack(); // reset database update
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => "CANN'T TO DATABASE"
            ];
        }
    }
    /**
     * function __conditions
     * Description: hàm tìm kiếm order màn hình [3]
     * @author chan_nl
     * Created_at:
     * Updated_at: 2020/09/04
     */
    private function __conditions($request = null, $query)
    {
        try{
            $query = $query->join('purchases', 'order_details.purchase_id', 'purchases.id'); // join với bảng purchases để lấy mã code
            $query = $query->join('shipments', 'order_details.shipment_id', 'shipments.id'); // join với bảng shipment để lấy mã code
            $query = $query->leftJoin('imports', 'orders.import_id', 'imports.id'); // join với bảng purchases để lấy mã code
            if(!empty($request['group']) && !empty($request['category_id']))
            {
                $group = $request['group'];
                $file_group = "group$group"."_id";
                $query = $query->join('products', 'order_details.product_id', 'products.product_id');
                $query = $query->where("$file_group" , $request['category_id']);
            }
            // kiểm tra điều kiện để có thể nối với bảng trạng thái sản phẩm qua id sản phẩm của bảng order detail
            if(isset($request['orther']) && count($request['orther']) > 0)
            {
                // group theo id san pham sau do join voi bang detail để tránh trùng lặp dữ liệu
                $product_statuses = DB::table('product_statuses')->selectRaw('product_id')
                ->whereIn('product_status_id', $request['orther'])->groupBy('product_statuses.product_id');
                $query = $query->joinSub($product_statuses, 'product_statuses', function ($join) {
                    $join->on('order_details.product_id', 'product_statuses.product_id');
                });
            }
            //Kiểm tra ngày import nếu có sẽ join với bảng import
            if(isset($request['date_import_from']) && isset($request['date_import_to']) && $request['date_import_from'] != '' && $request['date_import_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(imports.date_import as date)'), [$request['date_import_from'], $request['date_import_to']]);
                // $query = $query->join('imports', function ($join) use ($request) {
                //     $join->on('orders.import_id', 'imports.id')
                //     ->whereBetween(DB::raw('CAST(imports.date_import as date)'), [$request['date_import_from'], $request['date_import_to']]);
                //     });
            }

            //check search NCC
            switch ($request['searchNCC']) {
                case 'yes':
                    $query->whereNotNull('order_details.supplied_id');
                    break;
                case 'no':
                    $query->where(function($q) {
                        $q->whereNull('order_details.supplied_id')
                            ->orWhereNull('order_details.supplied');
                    });
                    break;
            }

            //Tìm kiếm theo ngày đặt hàng
            if(isset($request['date_purchased_from']) && isset($request['date_purchased_to']) && $request['date_purchased_from'] != '' && $request['date_purchased_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(orders.purchase_date as date)'), [$request['date_purchased_from'], $request['date_purchased_to']]);
            }
            //Tìm kiếm theo ngày dự định giao hàng
            if(isset($request['ship_schedule_from']) && isset($request['ship_schedule_to']) && $request['ship_schedule_from'] != '' && $request['ship_schedule_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(shipments.es_shipment_date as date)'), [$request['ship_schedule_from'], $request['ship_schedule_to']]);
            }
            //Tìm kiếm theo ngày nhận hàng là ngày giao hàng của bảng shipments
            if(isset($request['recive_schedule_from']) && isset($request['recive_schedule_to']) && $request['recive_schedule_from'] != '' && $request['recive_schedule_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(shipments.shipment_date as date)'), [$request['recive_schedule_from'], $request['recive_schedule_to']]);
            }
            //Tìm kiếm theo sku
            if(isset($request['sku']) && $request['sku'] != '')
            {
                $query = $query->where('order_details.sku', $request['sku']);
            }
            //Tìm kiếm theo tên sản phẩm
            if(isset($request['name_product']) && $request['name_product'] != '')
            {
                $query = $query->where('order_details.product_name','like', "%".$request['name_product']."%");
            }
            //Tìm kiếm theo mã đặt hàng
            if(isset($request['purchase_code']) && $request['purchase_code'] != '')
            {
                $query = $query->where('purchases.purchase_code','like', "%".$request['purchase_code']."%");
            }
            //Tìm kiếm theo id sản phẩm
            if(isset($request['product_id']) && $request['product_id'] != '')
            {
                $query = $query->where('order_details.product_id', $request['product_id']);
            }
            //Tìm kiếm theo ngày giao hàng của order
            if(isset($request['delivery_date_from']) && isset($request['delivery_date_to']) && $request['delivery_date_from'] != '' && $request['delivery_date_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(orders.delivery_date as date)'), [$request['delivery_date_from'], $request['delivery_date_to']]);
            }
            //Tìm kiếm theo cách giao hàng 1:giao trực tiếp, 2: nhận hàng, 3: giao tận nơi, 4: mua hàng
            if(isset($request['delivery_way']) && $request['delivery_way'] != '')
            {
                if(gettype($request['delivery_way']) == 'array'){
                    $query = $query->whereIn('order_details.delivery_way', $request['delivery_way']); 
                }else {
                    $query = $query->where('order_details.delivery_way', $request['delivery_way']); 
                }
            }
            //Tìm kiếm theo phương thức giao hàng: chi tiết xem trong file constants
            if(isset($request['delivery_method']) && $request['delivery_method'] != '')
            {
                if(gettype($request['delivery_method']) == 'array'){
                    $query = $query->whereIn('shipments.delivery_method', $request['delivery_method']);
                }else {
                    $query = $query->where('shipments.delivery_method', $request['delivery_method']);
                }
            }
            //Tìm kiếm theo tên nhà cung cấp
            if(isset($request['supplied']) && $request['supplied'] !='')
            {
                $query = $query->where('order_details.supplied','LIKE',"%".$request['supplied']."%");
            }
            if(isset($request['supplied_id']) && $request['supplied_id'] !='')
            {
                $query = $query->where('order_details.supplied_id',$request['supplied_id']);
            }
            //Tìm kiếm theo tên người nhận
            if(isset($request['ship_name']) && $request['ship_name'] != '') // thông tin tên người nhận hàng
            {
                $ship_name = $request['ship_name'];
                $query = $query->where(DB::raw('order_details.ship_name1'), 'like',"%".$ship_name."%");
                // ->orWhere(DB::raw('concat(order_details.ship_name1_kana,order_details.ship_name2_kana)'),'like', "%".$ship_name."%");
            }
            //  kiểm tra điều kiện địa chỉ giao hàng
            if(isset($request['ship_address']) && $request['ship_address'] != '')
            {
                $ship_address = $request['ship_address'];
                $query = $query->where(DB::raw('concat(order_details.ship_address1, order_details.ship_address2, order_details.ship_address3)'),'like',"%".$ship_address."%");
                // ->orWhere('order_details.ship_address1_kana','like',"%". $ship_address."%");//Không dùng tới kana nên không cần điều kiện kana
            }
            //Tìm kiếm số điện thoại người nhận hàng
            if(isset($request['ship_tel']) && $request['ship_tel'] != '')
            {
                $query = $query->where('shipments.shipment_phone','like',"%".$request['ship_tel']."%");
            }
            // end order detail
            //Tìm kiếm theo ngày tạo order
            if(isset($request['date_created_from']) && isset($request['date_created_to']) && $request['date_created_from'] != '' && $request['date_created_to'] != '')
            {
                $query = $query->whereBetween(DB::raw('CAST(orders.order_date as date)'), [$request['date_created_from'], $request['date_created_to']]);
            }
            //Tìm kiếm theo order code
            // if(isset($request['order_id']) && $request['order_id'] != '')
            // {
            //     $order_id = $request['order_id'];
            //     $query = $query->where(function($sub) use($order_id){
            //         return $sub->where('orders.order_id', $order_id);
            //     });
            // }
            //Tìm kiếm theo trạng thái order: chi tiết trạng thái check trong file constants
            if(isset($request['status_support']) && !empty($request['status_support']))
            {
                $o_status = $request['status_support'];
                if(gettype($request['status_support']) == 'array'){
                    $query = $query->whereIn('orders.status', $o_status);
                }else {
                    $query = $query->where('orders.status', $o_status);
                }
            }
            //Tìm kiếm theo trạng thái đơn đặt hàng: chi tiết trạng thái check trong file constants
            if(isset($request['flag_confirm']))
            {
                $pur_status = $request['flag_confirm'];
                if(gettype($request['flag_confirm']) == 'array'){
                    $query = $query->whereIn('purchases.status', $pur_status);
                }else {
                    $query = $query->where('purchases.status', $pur_status);
                }
            }
            //Tìm kiếm theo loại website import
            if(isset($request['site_type']))
            {
                $site_type = $request['site_type'];
                if(gettype($request['site_type']) == 'array'){
                    $query = $query->whereIn('orders.site_type', $site_type);
                }else {
                    $query = $query->where('orders.site_type', $site_type);
                }
            }
            //Tìm kiếm theo tên người nhận
            if(isset($request['buyer_name']) && $request['buyer_name'] != '') // tên người mua
            {
                $buyer_name = $request['buyer_name'];
                $query = $query->where(DB::raw('concat(orders.buyer_name1, orders.buyer_name2)'),'like',"%".$buyer_name."%");
                // ->orWhere(DB::raw('concat(orders.buyer_name1_kana, orders.buyer_name2_kana)'),'like',"%". $buyer_name."%");//Không dùng tới kana nên không cần điều kiện kana
            }
            //Tìm kiếm theo địa chỉ người nhận
            if(isset($request['buyer_address']) && $request['buyer_address'] != '')
            {
                    $buyer_address = $request['buyer_address'];
                    $query = $query->where(DB::raw('concat(orders.buyer_address_1, orders.buyer_address_2, orders.buyer_address_3)'),'like',"%".$buyer_address."%");
                    // ->orWhere(DB::raw('concat(orders.buyer_address_1_kana, orders.buyer_address_2_kana, orders.buyer_address_3_kana)'),'like',"%".$buyer_address."%");//Không dùng tới kana nên không cần điều kiện kana
            }
            //Tìm kiếm theo số điện thoại người nhận
            if(isset($request['buyer_tel']) && $request['buyer_tel'] != '') {
                $buyerTel = $request['buyer_tel'];
                $query->where(function($q) use ($buyerTel) {
                    $q->where('orders.buyer_tel1', 'like', "%$buyerTel%")
                        ->orWhere('orders.buyer_tel2', 'like', "%$buyerTel%")
                        ->orWhere('orders.buyer_tel3', 'like', "%$buyerTel%")
                        ->orWhere(DB::raw('concat(orders.buyer_tel1, orders.buyer_tel2, orders.buyer_tel3)'), 'like', "%$buyerTel%");
                });
            }
            //Tìm kiếm theo điều kiện 顧客対応
            if(isset($request['support_cus']))
            {
                $get_data = $query->get()->toArray();
                if(gettype($request['support_cus']) == 'array'){
                    foreach($request['support_cus'] as $key => $sup){
                        $sup = (int)$sup;
                        if($key == 0){
                            if($sup == 1){
                                $query = $query->where('shipments.delivery_method', 7);
                            }else if($sup == 2){
                                // $query = $query->whereNotNull('shipments.shipment_date');
                                $query = $query->where(function($sub){
                                    return $sub->whereNotNull('shipments.shipment_date')
                                    ->orWhere("shipments.shipment_time", "!=", "0");
                                });
                            }else if($sup == 3){
                                $query = $query->where(function($sub){
                                    return $sub->whereRaw("coalesce(orders.comments, '') != ''")
                                    ->orWhereRaw("coalesce(order_details.message, '') != ''")
                                    ->orWhereRaw("coalesce(order_details.wrapping_paper_type, '') != ''");
                                });
                            }
                            else if($sup == 5){
                                $mtb_zip_island_ec_mix = $this->setDtbMixTable();
                                $mtb_zip_island = $mtb_zip_island_ec_mix->selectRaw('mtb_zip_island.zipcode')
                                                        ->where('mtb_zip_island.island_flag' , 1)
                                                        ->get()
                                                        ->toArray();
                                $zipcode = [];
                                foreach($mtb_zip_island AS $value){
                                    array_push($zipcode, $value->zipcode);
                                }
                                $query = $query->whereIn(DB::raw("REPLACE(\"shipments\".\"shipment_zip\", '-', '')"), $zipcode);
                            }
                            else if($sup == 6){//Tìm kiếm sđt người nhận có nhiều hơn 1 ở tất cả các order
                                $arr_od_id = [];
                                if(!empty($get_data)){
                                    foreach($get_data as $get){
                                        array_push($arr_od_id, $get['detail_id']);
                                    }
                                    $query_sup_cus = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('order_details.order_code, order_details.id, order_details.shipment_id, shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id);
                                    $find_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id)
                                                    ->groupBy('shipments.shipment_phone')
                                                    ->havingRaw('count(*) > ?', [1])->pluck('shipment_phone')->toArray();
                                    $group_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('order_details.order_code, order_details.shipment_id, shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id)
                                                    ->groupBy('shipments.shipment_phone', 'order_details.order_code', 'order_details.shipment_id')
                                                    ->havingRaw('count(*) > ?', [1])->get()->toArray();
                                    $sub_cus_query = $query_sup_cus->whereIn('shipments.shipment_phone', $find_shipphone)
                                                    ->orderBy('order_details.order_code')->get()->toArray();
                                    $arr_odetail_id = [];
                                    if(count($group_shipphone) > 0){
                                        $check_phone = [];
                                        foreach($group_shipphone as $data_group){
                                            $count = 0;
                                            foreach($sub_cus_query as $cus_query){
                                                if($data_group->shipment_phone == $cus_query->shipment_phone && $data_group->shipment_id != $cus_query->shipment_id){
                                                    $count++;
                                                }
                                            }
                                            if($count <= 0){
                                                array_push($check_phone, $data_group);
                                            }
                                        }
                                        if(count($check_phone) > 0){
                                            foreach($sub_cus_query as $cus_query){
                                                foreach($check_phone as $check){
                                                    if($check->order_code != $cus_query->order_code && $check->shipment_phone != $cus_query->shipment_phone && $check->shipment_id != $cus_query->shipment_id){
                                                        array_push($arr_odetail_id, $cus_query->id);
                                                    }
                                                }
                                            }
                                        }else {
                                            foreach($sub_cus_query as $cus_query){
                                                array_push($arr_odetail_id, $cus_query->id);
                                            }
                                        }
                                    }else {
                                        foreach($sub_cus_query as $cus_query){
                                            array_push($arr_odetail_id, $cus_query->id);
                                        }
                                    }
                                    if(!empty($arr_odetail_id)){
                                        $query = $query->whereIn('order_details.id', $arr_odetail_id);
                                    }else {
                                        $query = $query->whereIn('order_details.id', [0]);
                                    }
                                }
                            }
                        }else {
                            if($sup == 1){
                                $query = $query->orWhere(function($sub){
                                    return $sub->where('shipments.delivery_method', 7);
                                });
                            }else if($sup == 2){
                                // $query = $query->orWhere(function($sub){
                                //     return $sub->whereNotNull('shipments.shipment_date');
                                // });
                                $query = $query->orWhere(function($sub){
                                    return $sub->whereNotNull('shipments.shipment_date')
                                    ->orWhere("shipments.shipment_time", "!=", "0");
                                });
                            }else if($sup == 3){
                                $query = $query->orWhere(function($sub){
                                    return $sub->whereRaw("coalesce(orders.comments, '') != ''")
                                    ->orWhereRaw("coalesce(order_details.message, '') != ''")
                                    ->orWhereRaw("coalesce(order_details.wrapping_paper_type, '') != ''");
                                });
                            }
                            else if($sup == 5){
                                $mtb_zip_island_ec_mix = $this->setDtbMixTable();
                                $mtb_zip_island = $mtb_zip_island_ec_mix->selectRaw('mtb_zip_island.zipcode')
                                                        ->orWhere('mtb_zip_island.island_flag' , 1)
                                                        ->get()
                                                        ->toArray();
                                $zipcode = [];
                                foreach($mtb_zip_island AS $value){
                                    array_push($zipcode, $value->zipcode);
                                }
                                $query = $query->orWhere(function($sub) use($zipcode){
                                    return $sub->whereIn(DB::raw("REPLACE(\"shipments\".\"shipment_zip\", '-', '')"), $zipcode);
                                });
                            }
                            else if($sup == 6){
                                $arr_od_id = [];
                                if(!empty($get_data)){
                                    foreach($get_data as $get){
                                        array_push($arr_od_id, $get['detail_id']);
                                    }
                                    $query_sup_cus = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('order_details.order_code, order_details.id, order_details.shipment_id, shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id);
                                    $find_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id)
                                                    ->groupBy('shipments.shipment_phone')
                                                    ->havingRaw('count(*) > ?', [1])->pluck('shipment_phone')->toArray();
                                    $group_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                                    ->selectRaw('order_details.order_code, order_details.shipment_id, shipments.shipment_phone')
                                                    ->whereIn('order_details.id', $arr_od_id)
                                                    ->groupBy('shipments.shipment_phone', 'order_details.order_code', 'order_details.shipment_id')
                                                    ->havingRaw('count(*) > ?', [1])->get()->toArray();
                                    $sub_cus_query = $query_sup_cus->whereIn('shipments.shipment_phone', $find_shipphone)
                                                    ->orderBy('order_details.order_code')->get()->toArray();
                                    $arr_odetail_id = [];
                                    //Push ship vào cùng 1 order
                                    if(count($group_shipphone) > 0){
                                        $check_phone = [];
                                        foreach($group_shipphone as $data_group){
                                            $count = 0;
                                            foreach($sub_cus_query as $cus_query){
                                                if($data_group->shipment_phone == $cus_query->shipment_phone && $data_group->shipment_id != $cus_query->shipment_id){
                                                    $count++;
                                                }
                                            }
                                            if($count <= 0){
                                                array_push($check_phone, $data_group);
                                            }
                                        }
                                        if(count($check_phone) > 0){
                                            foreach($sub_cus_query as $cus_query){
                                                foreach($check_phone as $check){
                                                    if($check->order_code != $cus_query->order_code && $check->shipment_phone != $cus_query->shipment_phone && $check->shipment_id != $cus_query->shipment_id){
                                                        array_push($arr_odetail_id, $cus_query->id);
                                                    }
                                                }
                                            }
                                        }else {
                                            foreach($sub_cus_query as $cus_query){
                                                array_push($arr_odetail_id, $cus_query->id);
                                            }
                                        }
                                    }else {
                                        foreach($sub_cus_query as $cus_query){
                                            array_push($arr_odetail_id, $cus_query->id);
                                        }
                                    }
                                    if(!empty($arr_odetail_id)){
                                        $query = $query->orWhere(function($sub) use($arr_odetail_id){
                                            return $sub->whereIn('order_details.id', $arr_odetail_id);
                                        });
                                    }else {
                                        $query = $query->orWhere(function($sub){
                                            return $sub->whereIn('order_details.id', [0]);
                                        });                                        
                                    }
                                }
                            }                            
                        }
                    }
                }else {
                    $sup = $request['support_cus'];
                    if($sup == 1){
                        $query = $query->where('shipments.delivery_method', 7);
                    }else if($sup == 2){
                        // $query = $query->whereNotNull('shipments.shipment_date');
                        $query = $query->where(function($sub){
                            return $sub->whereNotNull('shipments.shipment_date')
                            ->orWhere("shipments.shipment_time", "!=", "0");
                        });
                    }else if($sup == 3){
                        $query = $query->where(function($sub){
                            return $sub->whereRaw("coalesce(orders.comments, '') != ''")
                            ->orWhereRaw("coalesce(order_details.message, '') != ''")
                            ->orWhereRaw("coalesce(order_details.wrapping_paper_type, '') != ''");
                        });
                    }
                    else if($sup == 5){
                        $mtb_zip_island_ec_mix = $this->setDtbMixTable();
                        $mtb_zip_island = $mtb_zip_island_ec_mix->selectRaw('mtb_zip_island.zipcode')
                                                ->where('mtb_zip_island.island_flag' , 1)
                                                ->get()
                                                ->toArray();
                        $zipcode = [];
                        foreach($mtb_zip_island AS $value){
                            array_push($zipcode, $value->zipcode);
                        }
                        $query = $query->whereIn(DB::raw("REPLACE(\"shipments\".\"shipment_zip\", '-', '')"), $zipcode);
                    }
                    else if($sup == 6){
                        $arr_od_id = [];
                        if(!empty($get_data)){
                            foreach($get_data as $get){
                                array_push($arr_od_id, $get['detail_id']);
                            }
                            $query_sup_cus = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                            ->selectRaw('order_details.order_code, order_details.id, order_details.shipment_id, shipments.shipment_phone')
                                            ->whereIn('order_details.id', $arr_od_id);
                            $find_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                            ->selectRaw('shipments.shipment_phone')
                                            ->whereIn('order_details.id', $arr_od_id)
                                            ->groupBy('shipments.shipment_phone')
                                            ->havingRaw('count(*) > ?', [1])->pluck('shipment_phone')->toArray();
                            $group_shipphone = DB::table('order_details')->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
                                            ->selectRaw('order_details.order_code, order_details.shipment_id, shipments.shipment_phone')
                                            ->whereIn('order_details.id', $arr_od_id)
                                            ->groupBy('shipments.shipment_phone', 'order_details.order_code', 'order_details.shipment_id')
                                            ->havingRaw('count(*) > ?', [1])->get()->toArray();
                            $sub_cus_query = $query_sup_cus->whereIn('shipments.shipment_phone', $find_shipphone)
                                            ->orderBy('order_details.order_code')->get()->toArray();
                            $arr_odetail_id = [];
                            //Push ship vào cùng 1 order
                            if(count($group_shipphone) > 0){
                                $check_phone = [];
                                foreach($group_shipphone as $data_group){
                                    $count = 0;
                                    foreach($sub_cus_query as $cus_query){
                                        if($data_group->shipment_phone == $cus_query->shipment_phone && $data_group->shipment_id != $cus_query->shipment_id){
                                            $count++;
                                        }
                                    }
                                    if($count <= 0){
                                        array_push($check_phone, $data_group);
                                    }
                                }
                                if(count($check_phone) > 0){
                                    foreach($sub_cus_query as $cus_query){
                                        foreach($check_phone as $check){
                                            if($check->order_code != $cus_query->order_code && $check->shipment_phone != $cus_query->shipment_phone && $check->shipment_id != $cus_query->shipment_id){
                                                array_push($arr_odetail_id, $cus_query->id);
                                            }
                                        }
                                    }
                                }else {
                                    foreach($sub_cus_query as $cus_query){
                                        array_push($arr_odetail_id, $cus_query->id);
                                    }
                                }
                            }else {
                                foreach($sub_cus_query as $cus_query){
                                    array_push($arr_odetail_id, $cus_query->id);
                                }
                            }
                            if(!empty($arr_odetail_id)){
                                $query = $query->whereIn('order_details.id', $arr_odetail_id);
                            }else {
                                $query = $query->whereIn('order_details.id', [0]);
                            }
                        }
                    }
                }
            }
            return $query;
        }catch(Exception $e){}
    }
    /**
     * mtb_zip_islandに繋がるか確認し
     * 繋がったらテーブルの設定を返す。
     * 繋がらなかった場合はfalseを返す。
     * @author hamasaki
     * 2020/08/21
     */
    public function setDtbMixTable(){
    	$this->ec_connect = DB::connection('eccube');
    	// mtb_zip_islandの接続チェック
    	$this->mtb_zip_island = 'mtb_zip_island';
    	try
    	{
    		$order_model_ec_mix = $this->ec_connect->table($this->mtb_zip_island);
    	} catch (Exception $exception) {
    		return false;
    	}
    	return $order_model_ec_mix;
    }
    /**
     * function get by orderId
     * @author Dat
     * 2019/10/18
     */
    public function getByOrderId($orderId = null)
    {
        $query = $this->order_model;
        try
        {
            $query = $query->where('orders.order_code', "$orderId");
            $results = $query->get()->toArray();
            if(count($results) > 0)
            {
                if(!empty($results[0]['import_id'])){
                    $date_import = Import::find($results[0]['import_id'])['date_import'];
                    $results[0]['date_import'] = $date_import;
                }else {
                    $results[0]['date_import'] = "";
                }
            }
            return $results;
        } catch(Exception $exception) {
            return [
                "status" => false,
                "message" => 'SQL error.'
            ];
        }
    }
    /**
     * function getDetailOrder
     * @author Dat
     * 2019/10/18
     */
    public function getDetailOrder($id = null)
    {
        $detail_model = new OrderDetail();
        $query = $detail_model;
        try
        {
            // $query = $query->find($id)->order_details('id');
            $query = $query->selectRaw("
            order_details.id, order_details.order_id, order_details.shipment_id, shipments.shipment_code as shipment_code, product_code, product_id, 
            order_details.maker_id, order_details.maker_code, product_name, quantity, price_sale, price_sale_tax, total_price_sale, total_price_sale_tax, 
            order_details.cost_price, order_details.cost_price_tax, order_details.total_price, order_details.total_price_tax, orders.tax,
            discount, orders.site_type, order_details.supplied_id, supplied, sku, ship_name2, ship_name1_kana, ship_name2_kana,
            COALESCE(shipments.shipment_customer, order_details.ship_name1) as ship_name1, 
            shipments.delivery_method as delivery_method, 
            COALESCE(shipments.delivery_way, order_details.delivery_way) as delivery_way,
            COALESCE(shipments.shipment_fee, order_details.delivery_fee) as delivery_fee,
            COALESCE(shipments.shipment_zip, order_details.ship_zip) as ship_zip,
            COALESCE(shipments.shipment_address, order_details.ship_address1) as ship_address1,
            COALESCE(shipments.shipment_phone, order_details.ship_phone) as ship_phone,
            COALESCE(shipments.shipment_payment, order_details.delivery_payment) as delivery_payment,
            shipments.shipment_date as shipment_date,
            COALESCE(shipments.shipment_time, order_details.receive_time) as receive_time,
            orders.purchase_date as purchase_date, purchases.price_edit as price_edit, purchases.status as purchase_status,
            CASE WHEN shipments.pay_request IS NOT NULL THEN shipments.pay_request ELSE order_details.pay_request END as pay_request,
            shipments.shipment_time,ship_country, ship_address1, ship_address2, ship_address3, ship_address1_kana,
            shipments.es_shipment_date, shipments.es_shipment_time, purchases.id as purchase_id, purchases.purchase_code as purchase_code,
            orders.delivery_date, wrapping_paper_type, wrapping_ribbon_type, gift_wrap, gift_wrap_kind, gift_message, message, delivery_time,
            order_details.updated_at")
            ->join('orders', 'orders.id', 'order_details.order_id')
            ->join('purchases', 'order_details.purchase_id', 'purchases.id')
            ->join('shipments', 'order_details.shipment_id', 'shipments.id')
            ->where('order_details.order_id', $id)
            ->orderBy('order_details.shipment_id', 'asc');
            $results = $query->get()->toArray();
            return $results;
        } catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * function edit order
     * @author Dat
     * 2019/10/22
     */
    public function editOrder($data_order, $orderId)
    {
        $query = $this->order_model;
        $data_update_order = [];
        $data_update_order = $data_order;
        DB::beginTransaction();// start transaction database
        try{
            // update info order table
            $query = $query->where('id', $orderId)->where('order_code', $data_update_order['order_code'])
                           ->update($data_update_order);
         DB::commit();
        }catch (Exception $exception) {
            DB::rollBack();
            Log::debug($exception);
            return ['status' => false,
                'message' =>"Not connect to Databases"
            ];
        }
        return 
        [
            'status' => true
        ];
    }
    /**
     * function create order
     * @author Dat
     * 2019/10/26
     * @param data_order,
     * @param data_add_detail
     */
    public function createOrder($data_order = null, $data_add_detail = null, $ship_exist = null)
    {
        $hisProcess = $this->his_process_model;
        $insert_HP = array();
        $insert_HP['process_user'] = Auth::user()->login_id;
        $insert_HP['process_permission'] = Auth::user()->type;
        $insert_HP['process_screen'] = '注文内容新規作成';
        $str_process_description = '<b>新規注文登録</b>: ';
        $current = Carbon::now();
        $shipmentModel = new Shipment();
        $addDetail = new OrderDetail();
        $purchaseModel = new Purchase();
        $user = Auth::user();
        if($data_order == null || $data_add_detail == null)
        {
            $results  = [
                'status' => false,
                'message' => 'data invalid'
            ];
            $log = [
                'data_order' =>$data_order, 
                'data_add_detail' => $data_add_detail
            ];
            Log::debug('data invalid',$log);
            return $results;
        }
        $query_exits = $this->order_model;
        $query_exits = $query_exits->where('orders.order_code', $data_order['order_code'])->get()->toArray();
        if(count($query_exits) > 0)
        {
            return [
                'status' => false,
                'message' => 'order_exits'
            ];
        }
        DB::beginTransaction();
        $createOrder = $this->order_model;
        try {
            $createOrder = $createOrder->create($data_order);// insert into order table
            $arr_ship = [];
            $arr_shipcode_cre = [];
            $index = 1;
            foreach($data_add_detail as $value)
            {
                $value['quantity'] = str_replace(",", "", $value['quantity']);
                $shipment_id_check = 0;
                $data_shipment = [];
                $data_purchase = [];
                $data_purchase = [
                    'status' =>  $value['purchase_status'],
                    'price_edit' =>  $value['price_edit'],
                    'order_id' => $createOrder->id,
                    'purchase_code' => $value['purchase_id'],
                    'supplier_id' => $value['supplied_id'],
                    'purchase_quantity' => $value['quantity'],
                    'cost_price' => $value['cost_price'],
                    'total_cost_price' => $value['total_price'],
                    'cost_price_tax' => $value['cost_price_tax'],
                    'total_cost_price_tax' => $value['total_price_tax'],
                    'purchase_date' => $current
                ];
                $shipcode = '';
                if(!empty($ship_exist)){
                    if(in_array($value['shipment_id'], $ship_exist)){
                        $shipcode = $this->ShipmentService->getBillNumber($index++);
                    }else {
                        $shipcode = $value['shipment_id'];
                    }
                }else {
                    $shipcode = $value['shipment_id'];
                }
                $data_shipment = [
                    'order_id' => $createOrder->id,
                    'delivery_method' => $value['delivery_method'],
                    'shipment_code' => $value['shipment_id'],
                    'delivery_way' => $value['delivery_way'],
                    'shipment_customer' => $value['ship_name1'],
                    'shipment_address' => $value['ship_address1'],
                    'shipment_quantity' => $value['quantity'],
                    'cost_price' => round($value['cost_price']),
                    'total_cost_price' => round($value['total_price']),
                    'cost_price_tax' => round($value['cost_price_tax']),
                    'total_cost_price_tax' => round($value['total_price_tax']),
                    'shipment_zip' => $value['ship_zip'],
                    'shipment_phone' => $value['ship_phone'],
                    'receive_date' => $value['receive_date'],
                    'receive_time' => $value['receive_time'],
                    'shipment_date' => $value['receive_date'],
                    'shipment_time' => $value['receive_time'],
                    'es_shipment_date' => $value['es_delivery_date_from'],
                    'es_shipment_time' => $value['es_delivery_time_from'],
                    'supplied_id' => $value['supplied_id'],
                    'shipment_fee' => round($value['delivery_fee']),
                    'pay_request' => $value['pay_request'],
                    'invoice_id' => $value['invoice_id']
                ];
                $insertPurchase = $purchaseModel;
                $insertShipment = $shipmentModel;
                $orderDetail = $addDetail;
                try {
                    $insertPurchase  = $insertPurchase->create($data_purchase);
                    $purchase_id = $insertPurchase->id;
                    $value['purchase_id'] = $purchase_id;
                    // add shipment
                    if(!in_array($shipcode, $arr_shipcode_cre)){
                        $insertShipment = $insertShipment->create($data_shipment);
                        $ship_id = $insertShipment->id;
                        array_push($arr_shipcode_cre, $shipcode);
                        array_push($arr_ship, ['ship_id' => $ship_id, 'ship_code' => $shipcode]);
                    }else {
                        foreach($arr_ship as $val_ship){
                            if($val_ship['ship_code'] == $shipcode){
                                $ship_id = $val_ship['ship_id'];
                            }
                        }
                    }
                    $value['shipment_id'] = $ship_id;
                    
                    unset($value['purchase_status']);
                    unset($value['price_edit']);
                    $orderDetail = $createOrder->order_details()->create($value);
                } catch (Exception $exception) {
                    DB::rollBack();
                    Log::debug($exception->getMessage());
                    return ['status' => false,
                        'message' =>"Not connect to Databases"
                    ];
                }
            }
            $str_process_description .= '受注ID: '.$data_order['order_code'];
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            DB::commit();
            return [
                'status' => true,
                'data_order' => $createOrder
            ];
        } catch (Exception $exception)
        {
            DB::rollBack();
            Log::debug($exception->getMessage());
            $results  = [
                'status' => false,
                'message' => "Not connect to Databases"
            ];
            return $results;
        }
    }
    /**
     * function get data export
     * @author Dat
     * 2019/10/28
     */
    public function getDataExport ()
    {
        
    }
    /**
     * copy order
     * @author Dat
     */
    public function copyOrders($request) 
    {
        $detail_model = new OrderDetail();
        $query = $this->order_model;
        $update_order = $this->order_model;
        $copies = $this->order_model;
        $insert_detail = $detail_model;
        $number_of_copies = 1;
        $id_parent = 1;
        $data_order_detail = [];
        // return $orders;
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $hisProcess = $this->his_process_model;
            $insert_HP = array();
            $insert_HP['process_user'] = Auth::user()->login_id;
            $insert_HP['process_permission'] = Auth::user()->type;
            $str_process_description = '';
            $orders_code = []; // mảng chứ những mã hóa đơn
            $orders_code = $request;
            if(isset($request['list_orders']))
            {
                $orders_code = $request['list_orders'];
            }
            if(isset($request['screen'])){
                $insert_HP['process_screen'] = '注文検索';
                $str_process_description .= '<b>選択中の注文をコピー</b>:<br>';
            }else {
                $insert_HP['process_screen'] = '注文内容編集';
                $str_process_description .= '<b>注文の複製 </b>:<br>';
            }
            $orders = $query->whereIn('order_code', $orders_code)->get()->toArray(); // lấy dữ liệu của hóa đơn 
            
            foreach( $orders as $value)
            {
                $order_detail = $detail_model;
                $number_of_copies = $value['number_of_copies']+1;// tính lại số lần copies
                $id_parent = $value['id'];
                $order_id = 0;
                $order_code = '';
                $order_code_detail = $value['order_code'];
                $value['order_code'] = $value['order_code']."-$id_parent-C00$number_of_copies";// set mã hóa đơn. qu t[]
                $value['number_of_copies'] = 0; 
                unset($value['id']); // xóa ID khỏi mảng insert bảng order để tránh dulicate
                $order_id = $copies->insertGetId($value); // insert vào bảng order và lấy Id sau khi insert
                $order_code = $value['order_code']; // lấy mã hóa đơn để insert vào bảng detail
                $order_detail = $order_detail->where('order_code', $order_code_detail);
                if(isset($request['list_details']))
                {
                    $order_detail = $order_detail->whereIn('id', $request['list_details']);
                }
                $data_order_detail = $order_detail->get()->toArray(); // lấy danh sách detail hóa đơn
                $str_process_description .= '受注ID: '.$order_code_detail." -> ".$value['order_code']."<br>";
                foreach($data_order_detail as $key_detail => $value_detail)
                {
                    //get shipment
                    $shipmentExist = $this->modelShipment->where('id', $value_detail['shipment_id'])->get()->toArray();
                    if(!empty($shipmentExist)){
                        unset($shipmentExist[0]['id']);
                        $shipmentExist = $this->modelShipment->insertGetId($shipmentExist[0]);
                    }else {
                        DB::rollBack();
                        return [
                            'status' =>false,
                            'message' => 'Shipment deleted'
                        ];
                    }

                    //get purchase
                    $purchaseExist = $this->purchase_model->where('id', $value_detail['purchase_id'])->get()->toArray();
                    if(!empty($purchaseExist)){
                        unset($purchaseExist[0]['id']);
                        $purchaseExist = $this->purchase_model->insertGetId($purchaseExist[0]);
                    }else {
                        DB::rollBack();
                        return [
                            'status' =>false,
                            'message' => 'Purchase deleted'
                        ];
                    }
                    
                    $value_detail['order_id'] = $order_id; // xét id hóa đơn
                    $value_detail['order_code'] = $order_code; // mã hóa đơn 
                    unset($value_detail['id']); // xóa ID khỏi mảng insert chi tiết hóa đơn
                    unset($value_detail['shipment_code']); // bỏ shipment code không copies mã giao hàng để tránh dulicate 1 giao hàng có 2 order
                    $value_detail['shipment_id'] = $shipmentExist;
                    $value_detail['purchase_id'] = $purchaseExist;
                    
                    $insert_detail->insert($value_detail); // insert vào bảng chi tiết hóa đơn
                }
                // update số lần copies của order gốc
                $update_order->whereIn('order_code', $orders_code)
                ->update(['number_of_copies'=> $number_of_copies]);
            }
            if(count($orders) > 0){
                $str_process_description = rtrim($str_process_description, '<br>');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            DB::commit(); // lưu dữ liệu
            return [
                "status" => true,
                "message" => 'success',
                "order_copy" => $order_code
            ];
        } catch(Exception $exception)
        {
            DB::rollBack();
            Log::debug($exception->getMessage());
            return [
                'status' =>false,
                'message' => 'Error conenct database'
            ];
        }
    }
    /**
     * function delete order
     * @author Dat
     */
    public function deleteOrders($request)
    {
        $detail_model = new OrderDetail();
        $order_data = $this->order_model;
        $orders_code = $request; // gắn mảng order code
        if(isset($request['list_orders']))
        {
            $orders_code = $request['list_orders']; // nếu chọn bên tìm kiếm order sẽ có 2 mảng là listorder và list products
        }
        DB::beginTransaction();
        try
        {
            $hisProcess = $this->his_process_model;
            $insert_HP = array();
            $insert_HP['process_user'] = Auth::user()->login_id;
            $insert_HP['process_permission'] = Auth::user()->type;
            $insert_HP['process_screen'] = '注文検索';
            $str_process_description = '<b>選択中の商品を削除</b>:<br>';
            // xóa order từ màn hình chỉnh sửa order
            $order_data = $order_data->whereIn('order_code', $orders_code)->get()->toArray(); // danh sach order se bi xoa
            foreach($order_data as $value)
            {
                $data_delete = $this->order_model;
                $query_order = $detail_model;
                $query_detail = $detail_model;
                $delete_detail = $detail_model;
                $query_order = $query_order->where('order_id', $value['id'])->where('order_code', $value['order_code'])->get()->toArray(); // lấy số detail của order trong bảng detail order
                $query_detail =  $query_detail->where('order_id', $value['id'])->where('order_code', $value['order_code']);
                if(isset($request['list_details']))
                {
                    $query_detail=  $query_detail->whereIn('id', $request['list_details']);
                }
                $description = $query_detail->get()->toArray();
                $arr_purchase_id = [];
                $arr_shipment_id = [];
                $arr_detail_id = [];
                foreach($description as $des){
                    array_push($arr_purchase_id, $des['purchase_id']);
                    array_push($arr_shipment_id, $des['shipment_id']);
                    array_push($arr_detail_id, $des['id']);
                    $str_process_description .= "受注ID: ".$des['order_code']."(".$des['purchase_code'].')、';
                }
                foreach(array_count_values($arr_shipment_id) as $ship_id => $quan){
                    $count_ship = 0;
                    foreach($query_order as $order){
                        if($order['shipment_id'] == $ship_id){
                            $count_ship++;
                        }
                    }
                    if($count_ship == $quan){
                        $del_ship = $this->modelShipment->where('id', $ship_id)->delete();
                    }
                }
                $del_pur = $this->purchase_model->whereIn('id', $arr_purchase_id)->delete();
                $delete_detail = $delete_detail->whereIn('id', $arr_detail_id)->delete(); // xóa các detail id
                Log::info(Auth::user()->name." delete order detail"); // ghi log ai là người xóa.
                if(count($query_order) == count($arr_detail_id)) // nếu số lượng xóa detail của hóa đơn bằng số detail của hóa đơn thì sẽ xóa luôn hóa đơn trong bảng orders
                {
                    // xóa hóa đơn
                    $str_process_description = rtrim($str_process_description, '、');
                    $str_process_description .= "<br><b style='color: red'>削除:</b> 受注ID: ".$value['order_code'];
                    $data_delete = $data_delete->where('order_code', $value['order_code'])->delete();
                    Log::info(Auth::user()->name." delete order "); // ghi log ai là người xóa hóa đơn
                }
            }   
            $str_process_description = rtrim($str_process_description, '、');
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            DB::commit(); // xóa thành công
            return [
                'status' => true,
                'message' => 'Done'
            ];
        }catch(Exception $exception)
        {
            DB::rollBack(); // nếu xảy ra lỗi sẽ rollback lại các câu SQL 
            Log::debug($exception->getMessage()); // ghi log lỗi
            return [
                'status' => false,
                'message' => 'Error conenct database'
            ];
        }
    }
    /**
     * public function get last id of orders table
     * @author Dat
     * 20191214
     */
    public function getLastId()
    {
        $query = $this->order_model;
        try {
            $query = $query->selectRaw('id')->latest()->first()->toArray();
            return $query;
        }catch(Exception $exception)
        {
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message'=> "CANNOT CONECT DATABASE"
            ];
        }
    }

    /**
     * function checkShipmentExist
     * Description: Check shipment_code exists for 1 year
     * @author chan_nl
     * Created: 2020/10/22
     * Updated: 2020/10/22
     */
    public function checkShipmentExist($arr_ship){
        $now = Carbon::now()->format('Y-m-d');
        $last_year = date('Y-m-d', strtotime("-1 year"));
        $query = $this->order_model;
        return $query->select('shipments.shipment_code')
            ->join('order_details', 'order_details.order_id', 'orders.id')
            ->join('shipments', 'shipments.id', 'order_details.shipment_id')
            ->whereIn('shipments.shipment_code', $arr_ship)
            ->whereIn('shipments.delivery_method', [1,7])
            ->where(DB::raw('cast(orders.order_date as date)'), '>=', $last_year)
            ->where(DB::raw('cast(orders.order_date as date)'), '<=', $now)
            ->get()->toArray();
    }
}