<?php
// set namespace
namespace App\Repositories\Services\SupplierHome;

use App\Model\HistoryProcess\HistoryProcess;
use App\Model\Shipments\Shipment;
use App\Model\Purchases\Purchase;
use App\Model\Orders\OrderDetail;
use App\Model\Orders\Order;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SupplierHomeService implements SupplierHomeServiceContract
{
    /**
     * class SupplierHome service
     * @author channl
     * Created: 2019/10/28
     * Updated: 2019/10/29
     */
    protected $Purchase;
    protected $OrderDetail;
    protected $Order;
    protected $Shipment;
    protected $historyProcessModel;
    public function __construct()
    {
        $this->Purchase = new Purchase();
        $this->OrderDetail = new OrderDetail();
        $this->Order = new Order();
        $this->Shipment = new Shipment();
        $this->historyProcessModel = new HistoryProcess();
    }

    /**
     * function getListPurchaseBySupplier
     * Description: thống kê danh sách đơn đặt hàng theo nhà cung cấp login
     * @param
     * supplier_id: id nhà cung cấp
     * year: năm thống kê
     * month: tháng thống kê
     * @author channl
     * Created: 2019/11/07
     * Updated: 2019/11/07
     */
    public function getListPurchaseBySupplier($supplier_id, $year, $month){
        try{
            $query = $this->Order;
            //Sum kết quả theo điều kiện
            $sql = "
                CAST(orders.purchase_date AS DATE) as p_date, purchases.order_id,
                sum(case when purchases.status in (1,2,3,4) and EXTRACT(YEAR FROM orders.purchase_date) = '".$year."' AND EXTRACT(MONTH FROM orders.purchase_date) = '".$month."' and purchases.supplier_id = '".$supplier_id."' then (purchases.total_cost_price) else 0 end) as price,
                sum(case when purchases.status = 1 and EXTRACT(YEAR FROM orders.purchase_date) = '".$year."' AND EXTRACT(MONTH FROM orders.purchase_date) = '".$month."' and purchases.supplier_id = '".$supplier_id."' then 1 else 0 end) as quantity_p1,
                sum(case when purchases.status in (2,3) and EXTRACT(YEAR FROM orders.purchase_date) = '".$year."' AND EXTRACT(MONTH FROM orders.purchase_date) = '".$month."' and purchases.supplier_id = '".$supplier_id."' then 1 else 0 end) as quantity_p2_p3,
                sum(case when purchases.status = 4 and EXTRACT(YEAR FROM orders.purchase_date) = '".$year."' AND EXTRACT(MONTH FROM orders.purchase_date) = '".$month."' and purchases.supplier_id = '".$supplier_id."' then 1 else 0 end) as quantity_p4,
                sum(case when purchases.status = 5 and EXTRACT(YEAR FROM orders.purchase_date) = '".$year."' AND EXTRACT(MONTH FROM orders.purchase_date) = '".$month."' and purchases.supplier_id = '".$supplier_id."' then 1 else 0 end) as quantity_p5
                
            ";
            //Sum các đơn đặt hàng có số lượng download, số lượng đơn đặt hàng đã xác nhận > 0
            $sub_sql = "
                p_date,
                sum(quantity_p1 + quantity_p2_p3 + quantity_p4) as total_order,
                sum(price) as total_price,
                sum(case when quantity_p1 > 0 then quantity_p1 else 0 end) as quantity_p1,
                sum(case when quantity_p2_p3 > 0 then quantity_p2_p3 else 0 end) as quantity_p2_p3,
                sum(case when quantity_p4 > 0 then quantity_p4 else 0 end) as quantity_p4,
                sum(case when quantity_p5 > 0 then quantity_p5 else 0 end) as quantity_p5
            ";
            $query = $query->select(
                DB::raw($sub_sql)
            );
            $query = $query->from(function($subquery) use ($supplier_id, $year, $month, $sql){
                return  $subquery->from('orders')->join('order_details', 'order_details.order_id', '=', 'orders.id')
                                 ->join('purchases', 'purchases.id', '=', 'order_details.purchase_id')
                                 ->select(DB::raw($sql))
                                 ->whereYear('orders.purchase_date', $year)
                                 ->whereMonth('orders.purchase_date', $month)
                                 ->where('purchases.supplier_id', $supplier_id)
                                 ->whereIn('orders.status', [3, 4, 5, 6, 7])
                                 ->whereIn('purchases.status', [1, 2, 3, 4, 5])
                                 ->groupBy('purchases.order_id', 'p_date')
                                 ->orderBy('p_date');
                    },'foo')->groupBy('p_date')->orderBy('p_date');
            $query = $query->get()->toArray();
            return $query;
        }catch(Exception $e){
            Log::debug($e->getMessage());
            return "Not connect to Databases";
        }
    }

    /**
     * function getPurchaseDetailByDate
     * Description: Thống kê chi tiết đơn đặt hàng theo ngày đặt hàng
     * @param
     * supplier_id: id nhà cung cấp
     * date: ngày đặt order
     * date_to: tìm trong khoảng date - date_to
     * @author channl
     * Created: 2019/11/07
     * Updated: 2019/11/07
     */
    public function getPurchaseDetailByDate($supplier_id, $date, $date_to = null, $flag_p_status_1, $flag_p_status_2, $flag_p_status_3){
        try{
            $query = $this->Order;
            $query = $query->selectRaw(
                "order_details.id as o_detail_id,
                 orders.purchase_date as p_created_at, 
                 shipments.es_shipment_date as od_deliv_date,
                orders.order_code as o_order_id, purchases.id as p_id, purchases.status as p_status, purchases.purchase_code as p_code, 
                orders.id as o_id, order_details.tax as od_tax, 
                orders.status,shipments.id as ship_id, shipments.shipment_code, 
                order_details.product_name as od_product_name,
                order_details.supplied_id as sup_id, order_details.supplied as sup_name, 
                COALESCE(shipments.shipment_customer, concat(order_details.ship_name1,'', order_details.ship_name2)) as ship_name1, 
                order_details.ship_name2, order_details.updated_at as o_updated_at,
                order_details.quantity as od_quantity, purchases.total_cost_price as od_total_price, 
                purchases.price_edit as p_price_edit, shipments.delivery_method"
            )
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('purchases', 'purchases.id', '=', 'order_details.purchase_id')
            ->join('shipments', 'shipments.id', '=', 'order_details.shipment_id')
            ->where('purchases.supplier_id', $supplier_id)
            ->whereIn('orders.status', [3, 4, 5, 6, 7]);
            $arr_p_status = [];
            if($flag_p_status_1 == 1 ){
                array_push($arr_p_status, 1, 2, 3);
            }
            if($flag_p_status_2 == 2){
                array_push($arr_p_status, 4);
            }
            if($flag_p_status_3 == 3){
                array_push($arr_p_status, 5);
            }
            if(!empty($arr_p_status)){
                $query = $query->whereIn('purchases.status', $arr_p_status);
            }
            if($date_to != null){
                $query = $query->whereBetween(DB::raw('CAST(orders.purchase_date AS DATE)'), [$date, $date_to]);
            }else {
                $query = $query->where(DB::raw('CAST(orders.purchase_date AS DATE)'), $date);
            }
            return $query->orderBy('orders.purchase_date', 'desc')
                        ->orderBy('order_details.order_code', 'asc')
                        ->orderBy('purchases.purchase_code', 'asc')->paginate(50);
        }catch(Exception $e){
            Log::debug($e->getMessage());
            return "警報";
        }
    }

    /**
     * function updatePurchaseStatus
     * Description: Cập nhật trạng thái đặt hàng thành B4
     * @author chan_nl
     * Created: 2020/05/07
     * Updated: 2020/05/07
     */
    public function updatePurchaseStatus($request){        
        $hisProcess = $this->historyProcessModel;
        $current = Carbon::now()->format('Y-m-d');
        $query_orderdetail = $this->OrderDetail;
        $query_purchase = $this->Purchase;      
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '仕入先様用発注確認画面';
        $str_process_description = '<b>変更</b>:<br>発注ステータス: -> 出荷通知済';
        DB::beginTransaction();// start transaction database
        try{
            $user = auth()->user();
            $arr_shipment_id = $query_orderdetail->select('shipment_id')->whereIn('purchase_id', $request['arr_purchase'])->pluck('shipment_id')->toArray();
            $arr_purchase_id = $query_orderdetail->select('purchases.purchase_code', 'purchases.id', 'order_details.order_code')
                                                ->join('purchases', 'purchases.id', 'order_details.purchase_id')->whereIn('order_details.shipment_id', $arr_shipment_id)->get()->toArray();
            Log::info('User: '.$user->id.'|'.$user->name.' update purchase status:');
            foreach($arr_purchase_id as $key => $value){
                $query_purchase->where('id', $value['id'])->update([
                    'status' => 4,// Cập nhật trạng thái đặt hàng lên B4
                ]);
                $order = $query_purchase->join('order_details', 'purchases.id', 'order_details.purchase_id')
                                            ->select('order_details.order_code')
                                            ->groupBy('order_details.order_code')
                                            ->where('order_details.purchase_id', $value['id'])
                                            ->pluck('order_details.order_code')->toArray();
                $this->Order->where('order_code', $order)->update(['delivery_date' => $current]);
                $str_process_description .= '<br>受注ID: '.$value['order_code'].'('.$value['purchase_code'].')';
            }
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            DB::commit(); // commit database update
        }catch (Exception $exception)
        {
            DB::rollBack(); // reset database update
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => '警報'
            ];
        }
        return [
            'status' => true,
            'message' => '入力内容を保存しました。'
        ];
    }

    /**
     * function updatePurchaseDetail()
     * Desciption: Cập nhật thông tin tiền chi trả cho nhà cung cấp khi click 入力内容を保存
     * @author channl
     * Created: 2019/11/11
     * Updated: 2019/11/11
     */
    public function updatePurchaseDetail($request){   
        $one_on_one = $request['one_on_one'];
        $one_on_many = [];
        $list_remove_ship = $request['list_remove_ship'];
        if($request['one_on_many'] != null){
            $arr_collect = [];
            $ship_id = '';
            $ship_code = '';
            $sort_data = self::sortShipment($request['one_on_many']);
            foreach($sort_data as $val){
                $values = [
                    'order_detail_id' => $val['order_detail_id'],
                    'order_code' => $val['order_code'],
                    'shipment_date' => date('Y/m/d', strtotime($val['shipment_date'])),
                    'ship_id' => $val['ship_id'],
                    'shipment_code' => $val['shipment_code'],
                    'p_id' => $val['p_id'],
                    'total_price' => $val['total_price'],
                    'price_edit' => $val['price_edit'],
                    'od_tax' => $val['od_tax'],
                    'o_price_edit' => $val['o_price_edit'],
                    'o_bill_number' => $val['o_bill_number'],
                    'o_deliv_date' => $val['o_deliv_date'],
                    'p_code' => $val['p_code'],
                    'p_date' => $val['p_date']
                ];
                if($val['ship_id'] != $ship_id || $val['shipment_code'] != $ship_code)
                {
                    $arr_collect = [];
                }
                array_push($arr_collect, $values);
                if($val['ship_id'] == $ship_id && $val['shipment_code'] == $ship_code)
                {
                    array_pop($one_on_many);
                }
                $ship_id = $val['ship_id'];
                $ship_code = $val['shipment_code'];
                array_push($one_on_many, $arr_collect);
            }
        }
        $hisProcess = $this->historyProcessModel;
        $query_orderdetail = $this->OrderDetail;
        $query_purchase = $this->Purchase;
        $query_shipment = $this->Shipment;
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '仕入先様用発注確認画面';
        $str_process_description = '<b>変更</b>:';
        DB::beginTransaction();// start transaction database
        try{            
            $user = auth()->user();
            $flag_change_data = 0;
            foreach($request['data'] as $value){
                $data = explode("|",$value);
                $od_id = $data[0];
                $p_id = $data[1];
                $deliv_date = $data[2];
                $total_price_old = (double)str_replace(',', '',$data[3]);//Tiền mua hàng
                $price_edit = (double)str_replace(',', '',$data[4]);//Tiền đính chính
                $tax = (double)str_replace(',', '',$data[5]);//Thuế
                $o_price_edit = (double)str_replace(',', '',$data[6]);//Tiền đính chính cũ
                $ship_id = $data[7];//id shipment
                $bill_number = $data[8];//Mã bill
                $o_bill_number = $data[9];//Mã bill cũ
                $o_deliv_date = date('Y/m/d', strtotime($data[10]));//Ngày giao hàng cũ
                $p_code = $data[11];//Mã đặt hàng
                $p_date = $data[12];//Ngày đặt hàng
                $o_code = $data[13];//Order code
                $total_price_update = $total_price_old + $price_edit;//Tiền mua hàng sau khi chỉnh sửa
                $total_price_tax_update = $total_price_update + ($total_price_update * $tax);
                // Update order detail
                $flag_one = false;
                $flag_many = false;
                if($one_on_one != null){
                    foreach($one_on_one as $o_o){
                        if($o_o['order_detail_id'] == $od_id){
                            $str_process_description .= '<br>受注ID: '.$o_code.'('.$p_code.') ';
                            if($o_price_edit != $price_edit){
                                $str_process_description .= '[訂正金額: '.$o_price_edit.' -> '.$price_edit.']';
                                $flag_change_data++;
                            }
                            if($o_bill_number != $bill_number){
                                $str_process_description .= '[送り状番号: '.$o_bill_number.' -> '.$bill_number.']';
                                $flag_change_data++;
                            }
                            if($o_deliv_date != $deliv_date){
                                $str_process_description .= '[納品日(出荷日): '.$o_deliv_date.' -> '.$deliv_date.']';
                                $flag_change_data++;
                            }
                            $getShipment = $query_shipment->where('id', $ship_id)->get()->toArray();
                            if(!empty($getShipment)){
                                unset($getShipment[0]['id']);
                                $getShipment[0]['shipment_code'] = $bill_number;
                                $getShipment[0]['es_shipment_date'] = $deliv_date;
                                $shipment_insert = $query_shipment->create($getShipment[0]);// insert shipments table
                                $shipment_id = $shipment_insert->id;
                                $query_orderdetail->where('id', $od_id)->update([
                                    'shipment_id' => $shipment_id,//Cập nhật id shipment
                                    'delivery_date' => $deliv_date,//Cập nhật ngày giao hàng
                                    'total_price' => $total_price_update,//Cập nhật tiền mua hàng
                                    'total_price_tax' => $total_price_tax_update//Cập nhật tiền mua hàng có thuế
                                ]);
                                $query_purchase->where('id', $p_id)->update([
                                    'price_edit' => $price_edit,//$o_price_edit Cập nhật tiền đính chính: đổi logic không cộng dồn tiền đính chính cũ 
                                    'total_cost_price' => $total_price_update,//Cập nhật tiền mua hàng
                                    'total_cost_price_tax' => $total_price_tax_update,//Cập nhật tiền mua hàng có thuế
                                ]);
                            }else {
                                DB::rollBack();
                                return [
                                    'status' => false,
                                    'message' => 'Data change'
                                ];
                            }
                            $flag_one = true;
                            break;
                        }
                    }
                }
                if(!empty($one_on_many)){
                    foreach($one_on_many as $key => $o_m){
                        foreach($o_m as $val_om){
                            if($val_om['order_detail_id'] == $od_id){
                                $flag_many = true;
                                break;
                            }
                        }
                    }
                }
                if(!$flag_one && !$flag_many){
                    $query_orderdetail->where('id', $od_id)->update([
                        'delivery_date' => $deliv_date,//Cập nhật ngày giao hàng
                        'total_price' => $total_price_update,//Cập nhật tiền mua hàng
                        'total_price_tax' => $total_price_tax_update//Cập nhật tiền mua hàng có thuế
                    ]);
                    $query_purchase->where('id', $p_id)->update([
                        'price_edit' => $price_edit,//$o_price_edit Cập nhật tiền đính chính: đổi logic không cộng dồn tiền đính chính cũ 
                        'total_cost_price' => $total_price_update,//Cập nhật tiền mua hàng
                        'total_cost_price_tax' => $total_price_tax_update,//Cập nhật tiền mua hàng có thuế
                    ]);
                    $query_shipment->where('id', $ship_id)->update([
                        'shipment_code' => $bill_number,//Cập mã gửi hàng
                        'es_shipment_date' => $deliv_date//Cập nhật ngày dự định giao hàng
                    ]);
                }
                $str_process_description .= '<br>受注ID: '.$o_code.'('.$p_code.') ';
                if($o_price_edit != $price_edit){
                    $str_process_description .= '[訂正金額: '.$o_price_edit.' -> '.$price_edit.']';
                    $flag_change_data++;
                }
                if($o_bill_number != $bill_number){
                    $str_process_description .= '[送り状番号: '.$o_bill_number.' -> '.$bill_number.']';
                    $flag_change_data++;
                }
                if($o_deliv_date != $deliv_date){
                    $str_process_description .= '[納品日(出荷日): '.$o_deliv_date.' -> '.$deliv_date.']';
                    $flag_change_data++;
                }
            }
            if(!empty($one_on_many)){
                foreach($one_on_many as $key => $o_m){
                    $getShipment = $query_shipment->where('id', $o_m[0]['ship_id'])->get()->toArray();
                    $shipment_id = '';
                    if(!empty($getShipment)){
                        unset($getShipment[0]['id']);
                        $getShipment[0]['shipment_code'] = $o_m[0]['shipment_code'];
                        $getShipment[0]['es_shipment_date'] = $o_m[0]['shipment_date'];//Ngày dự định giao hàng
                        $shipment_insert = $query_shipment->create($getShipment[0]);// insert shipments table
                        $shipment_id = $shipment_insert->id;
                    }else {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Data change'
                        ];
                    }
                    foreach($o_m as $val_om){
                        $od_id = $val_om['order_detail_id'];
                        $p_id = $val_om['p_id'];
                        $deliv_date = $val_om['shipment_date'];
                        $total_price_old = (double)str_replace(',', '',$val_om['total_price']);//Tiền mua hàng
                        $price_edit = (double)str_replace(',', '',$val_om['price_edit']);//Tiền đính chính
                        $tax = (double)str_replace(',', '',$val_om['od_tax']);//Thuế
                        $o_price_edit = (double)str_replace(',', '',$val_om['o_price_edit']);//Tiền đính chính cũ
                        $ship_id = $val_om['ship_id'];//id shipment
                        $bill_number = $val_om['shipment_code'];//Mã bill
                        $o_bill_number = $val_om['o_bill_number'];//Mã bill cũ
                        $o_deliv_date = date('Y/m/d', strtotime($val_om['o_deliv_date']));//Ngày giao hàng cũ
                        $p_code = $val_om['p_code'];//Mã đặt hàng
                        $p_date = $val_om['p_date'];//Ngày đặt hàng
                        $o_code = $val_om['order_code'];//Order code
                        $total_price_update = $total_price_old + $price_edit;//Tiền mua hàng sau khi chỉnh sửa
                        $total_price_tax_update = $total_price_update + ($total_price_update * $tax);
                        $query_orderdetail->where('id', $od_id)->update([
                            'shipment_id' => $shipment_id,//Cập nhật id shipment
                            'delivery_date' => $deliv_date,//Cập nhật ngày giao hàng
                            'total_price' => $total_price_update,//Cập nhật tiền mua hàng
                            'total_price_tax' => $total_price_tax_update//Cập nhật tiền mua hàng có thuế
                        ]);
                        $query_purchase->where('id', $p_id)->update([
                            'price_edit' => $price_edit,//$o_price_edit Cập nhật tiền đính chính: đổi logic không cộng dồn tiền đính chính cũ 
                            'total_cost_price' => $total_price_update,//Cập nhật tiền mua hàng
                            'total_cost_price_tax' => $total_price_tax_update,//Cập nhật tiền mua hàng có thuế
                        ]);
                        $str_process_description .= '<br>受注ID: '.$o_code.'('.$p_code.') ';
                        if($o_price_edit != $price_edit){
                            $str_process_description .= '[訂正金額: '.$o_price_edit.' -> '.$price_edit.']';
                            $flag_change_data++;
                        }
                        if($o_bill_number != $bill_number){
                            $str_process_description .= '[送り状番号: '.$o_bill_number.' -> '.$bill_number.']';
                            $flag_change_data++;
                        }
                        if($o_deliv_date != $deliv_date){
                            $str_process_description .= '[納品日(出荷日): '.$o_deliv_date.' -> '.$deliv_date.']';
                            $flag_change_data++;
                        }
                    }
                }
            }
            if($list_remove_ship != null){
                $query_shipment->whereIn('id', $list_remove_ship)->delete();
            }
            if($flag_change_data > 0){
                $str_process_description = rtrim($str_process_description, '、');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            DB::commit(); // commit database update
        }catch (Exception $exception)
        {
            DB::rollBack(); // reset database update
            Log::debug($exception->getMessage());
            return [
                'status' => false,
                'message' => '警報'
            ];
        }
        return [
            'status' => true,
            'message' => '入力内容を保存しました。'
        ];
    }

    /**
     * function sortShipment
     * Description: sort shipment by shipment_code, ship_id
     * @author chan_nl
     * Created: 2020/10/19
     * Updated: 2020/10/19
     */
    public function sortShipment($arr){
        $array = [];
        $duplicate = [];
        for($i = 0; $i < count($arr); $i++){
            if(!in_array($arr[$i]['shipment_code'], $duplicate)){
                array_push($duplicate, $arr[$i]['shipment_code']);
            }
        }
        foreach($duplicate as $dup){
            for($i = 0; $i < count($arr); $i++){
                if($dup == $arr[$i]['shipment_code']){
                    array_push($array, $arr[$i]);
                }
            }
        }
        return $array;
    }
}