<?php

namespace App\Repositories\Services\Order;

use App\Repositories\Services\Shipment\ShipmentServiceContract;
use App\Model\HistoryProcess\HistoryProcess;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderDetailService implements OrderDetailServiceContract
{
    
    public $detailModel;
    private $ShipmentService;
    public function __construct(ShipmentServiceContract $ShipmentService)
    {
        $this->detailModel = new OrderDetail();
        $this->ShipmentService = $ShipmentService;
    }
    /**
    * function check update
    * @param Array
    *       (
    *           [0] => Array
    *               (
    *                   [detail_id] => 1
    *                   [updated_at] => 2020-01-15 15:17:32
    *               )

    *       )
     */
    public function checkUpdate($updated_at)
    {
        $where_detail = [];
        foreach($updated_at as $value){
            array_push($where_detail, $value['detail_id']);
        }
        $query = $this->detailModel;
        $query = $query->selectRaw('id as detail_id, updated_at')->whereIn('id', $where_detail)->get()->toArray();
        $value_check = true;
        foreach ($query as $value) {
            foreach ($updated_at as $value_at) {
                if($value['detail_id'] == $value_at['detail_id'] && $value['updated_at'] != $value_at['updated_at'])
                {
                    $value_check = false;
                }
            }
        }
        return $result = [
            'validate' => $value_check
        ];
    }
    /**
     * function kiểm tra order detail đã có shipments hay chưa
     * @author Dat
     * 20191212
     */
    private function __shipmentExist ($order_id = null)
    {
        if(empty($order_id))
        {
            Log::info('cannot check from empty array');
            return [
                'status' => false,
                'message' => 'details detail empty'
            ];
        }
        try {
            $dataDetail = $this->detailModel;
            $dataDetail = $dataDetail->selectRaw('order_details.id, order_details.shipment_id, shipments.shipment_code')
                                     ->join('shipments', 'shipments.id','=', 'order_details.shipment_id')
                                     ->where('order_details.order_id', $order_id)
                                     ->get()->toArray();
            return $dataDetail;
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());
            return [
                'status'=> false,
                'message' => "CANN'T CONNECT DATABASE"
            ];
        }

    }
    /**
     * private function check exits purchase
     * @author Dat
     * 20191217
     */
    private function __purchaseExits($order_id = null)
    {
        if(empty($order_id))
        {
            Log::info('cannot check from empty array');
            return [
                'status' => false,
                'message' => 'details detail empty'
            ];
        }
        try {
            $dataDetail = $this->detailModel;
            $dataDetail = $dataDetail->selectRaw('order_details.id, order_details.purchase_id, purchases.purchase_code')
                                     ->join('purchases', 'purchases.id','=', 'order_details.purchase_id')
                                     ->where('order_details.order_id', $order_id)
                                     ->get()->toArray();
            return $dataDetail;
        }catch(Exception $exception)
        {
            Log::debug($exception->getMessage());
            return [
                'status'=> false,
                'message' => "CANN'T CONNECT DATABASE"
            ];
        }
    }
    /**
     * function edit order and create shipments
     * @author  Dat
     * 20191212
     */
    public function editOrderDetails($data_detail = null, $order_id = null, $list_order_del = null, $ship_exist = null)
    {
        $current = Carbon::now();
        $shipmentModel = new Shipment();
        $detailModel = new OrderDetail();
        $purchaseModel = new Purchase();
        $historyProcessModel = new HistoryProcess();
        $user = Auth::user();

        $dataCheckDetails  = $this->__shipmentExist($order_id);
        $dataCheckPurchase = $this->__purchaseExits($order_id);
        $data_insert_shipment = [];
        $shipment_id = 0;
        // Chỉnh sửa order details hoặc thêm mới shipment.
        if(!empty($data_detail)){
            foreach($data_detail as $key => $value)
            {
                $value['quantity'] = str_replace(",", "", $value['quantity']);
                $invalid = true;
                // kiểm tra nếu đã có mã đặt hàng thì sẽ update lại đặt hàng
                foreach($dataCheckPurchase as $valuecheck)
                {
                    if($value['id'] == $valuecheck['id'] && $value['purchase_id'] == $valuecheck['purchase_code'])
                    {
                        $value['purchase_update'] = $valuecheck['purchase_id'];
                    }
                }
                // kiểm tra nếu đã có trong bảng shipment
                foreach($dataCheckDetails as $valuecheck)
                {
                    if($value['shipment_id'] == $valuecheck['shipment_code'])
                    {
                        $value['shipment_update'] = $valuecheck['shipment_id'];
                    }
                }
                // kiểm tra và đưa phần tử vào mảng insert shipment
                if(!empty($value['purchase_id']) || !empty($value['shipment_id']))
                {
                    array_push($data_insert_shipment, $value);
                    unset($data_detail[$key]);
                }
            }
            $index = 1;
            $arr_ship = [];
            $arr_shipcode_cre = [];
            foreach($data_insert_shipment as $value)
            {
                $value['quantity'] = str_replace(",", "", $value['quantity']);
                $shipment_code = '';
                $data_shipment = [];
                $data_purchase = [];
                $data_purchase = [
                    'status' =>  $value['purchase_status'],
                    'price_edit' =>  $value['price_edit'],
                    'order_id' =>$value['order_id'],
                    'purchase_code' => $value['purchase_id'],
                    'supplier_id' => $value['supplied_id'],
                    'purchase_quantity' => $value['quantity'],
                    'cost_price' => $value['cost_price'],
                    'total_cost_price' => $value['total_price'],
                    'cost_price_tax' => $value['cost_price_tax'],
                    'total_cost_price_tax' => $value['total_price_tax'],
                    'purchase_date' =>  $value['purchase_date']
                ];
                $insertShipment = $shipmentModel;
                $insertPurchase = $purchaseModel;
                $orderDetail = $detailModel;
                DB::beginTransaction();
                try {
                    if(empty($value['purchase_update']))
                    {
                        $insertPurchase  = $insertPurchase->create($data_purchase);
                        $purchase_id = $insertPurchase->id;
                        $value['purchase_id'] = $purchase_id;
                    }else
                    {
                        $updatePurchase = $insertPurchase;
                        $updatePurchase = $updatePurchase->where('id',$value['purchase_update'])
                                                        ->update($data_purchase);
                        unset($value['purchase_update']);
                        unset($value['purchase_id']);
                        unset($value['purchase_date']);
                    }
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
                    $updateShipment = $shipmentModel;
                    if(empty($value['shipment_update']))
                    {
                        $data_shipment = [
                            'delivery_method' => $value['delivery_method'],
                            'shipment_code' => $shipcode,//$value['shipment_id'],
                            'delivery_way' => $value['delivery_way'],
                            'shipment_customer' => $value['ship_name1'],
                            'shipment_address' => $value['ship_address1'],
                            'shipment_quantity' => $value['quantity'],
                            'shipment_zip' => $value['ship_zip'],
                            'shipment_phone' => $value['ship_phone'],
                            'receive_date' => $value['receive_date'],
                            'receive_time' => $value['receive_time'],
                            'shipment_date' => $value['receive_date'],
                            'shipment_time' => $value['receive_time'],
                            'es_shipment_date' => $value['es_delivery_date_from'],
                            'es_shipment_time' => $value['es_delivery_time_from'],
                            'supplied_id' => $value['supplied_id'],
                            'shipment_fee' => $value['delivery_fee'],
                            'pay_request' => $value['pay_request'],
                            'invoice_id' => $value['invoice_id']
                        ];
                        if(intval($value['shipment_index']) == -1){
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
                        }else{
                            $updateShipment->where('id', $value['shipment_index'])
                            ->update($data_shipment);
                            $ship_id = $value['shipment_index'];
                        }
                        unset($value['invoice_id']);
                    }else
                    {
                        $data_shipment = [
                            'delivery_method' => $value['delivery_method'],
                            'shipment_code' => $shipcode,//$value['shipment_id'],
                            'delivery_way' => $value['delivery_way'],
                            'shipment_customer' => $value['ship_name1'],
                            'shipment_address' => $value['ship_address1'],
                            'shipment_quantity' => $value['quantity'],
                            'shipment_zip' => $value['ship_zip'],
                            'shipment_phone' => $value['ship_phone'],
                            'receive_date' => $value['receive_date'],
                            'receive_time' => $value['receive_time'],
                            'shipment_date' => $value['receive_date'],
                            'shipment_time' => $value['receive_time'],
                            'es_shipment_date' => $value['es_delivery_date_from'],
                            'es_shipment_time' => $value['es_delivery_time_from'],
                            'supplied_id' => $value['supplied_id'],
                            'shipment_fee' => $value['delivery_fee'],
                            'pay_request' => $value['pay_request'],
                            'invoice_id' => $value['invoice_id']
                        ];
                        $data_shipment['updated_by'] = $user->name;
                        $updateShipment->where('id', $value['shipment_update'])
                                        ->update($data_shipment);
                        $ship_id = $value['shipment_update'];
                        unset($value['invoice_id']);
                        unset($value['shipment_update']);
                    }
                    $value['shipment_id'] = $ship_id;
                    unset($value['purchase_status']);
                    unset($value['price_edit']);
                    unset($value['es_delivery_time_from']);
                    unset($value['shipment_index']);
                    $orderDetail->where('id', (int)$value['id'])
                                    ->update($value);
                    DB::commit();
                } catch (Exception $exception) {
                    DB::rollBack();
                    Log::debug($exception->getMessage());
                    return ['status' => false,
                        'message' =>"Not connect to Databases"
                    ];
                }
            }
        }
        //Xóa sản phẩm ở màn hình chỉnh sửa order
        if(!empty($list_order_del)){
            $hisProcess = $historyProcessModel;
            $insert_HP = array();
            $insert_HP['process_user'] = $user->name;
            $insert_HP['process_permission'] = $user->type;
            $insert_HP['process_screen'] = '注文内容編集';
            $str_process_description = '<b>選択中の商品を削除</b>:<br>';
            $check_error = true;
            foreach($list_order_del as $value)
            {
                try
                {
                    $getInfo = $detailModel;
                    $delDetail = $detailModel;
                    $deletePurchase = $purchaseModel;
                    $getInfo_ = $getInfo->selectRaw('order_details.order_code, order_details.product_name, order_details.quantity, purchases.purchase_code, purchases.id as purchase_id')
                                        ->join('purchases', 'purchases.id', 'order_details.purchase_id')->where('order_details.id', (int)$value)->get()->toArray();
                    $str_process_description .= "受注ID: ".$getInfo_[0]['order_code']. "(".$getInfo_[0]['purchase_code'].")、";                    
                    $delDetail->where('id', (int)$value)->delete();//Xóa sản phẩm
                    $deletePurchase->where('id', (int)$getInfo_[0]['purchase_id'])->delete();//Xóa đặt hàng
                } catch (Exception $exception)
                {
                    $check_error = false;
                    Log::debug($exception->getMessage());
                    return [
                        'status' => false,
                        'message' =>"Not connect to Databases"
                    ];
                }
            }
            if($check_error == true){
                $str_process_description = rtrim($str_process_description, '、');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
        }
        return [
            'status' => true,
            'message' => "done"
        ];
    }
    /**
     * function add details
     * @author Dat
     * 20191212
     */
    public function addOrderDetails($data_add_details = null, $order_id = null, $ship_exist = null)
    {
        $current = Carbon::now();
        $shipmentModel = new Shipment();
        $purchaseModel = new Purchase();
        $user = Auth::user();
        $addDetail = $this->detailModel;
        $data_insert_shipment = [];
        $dataCheckDetails  = $this->__shipmentExist($order_id);
        $shipment_id = 0;
        foreach($data_add_details as $key => $add_detail)
        {
            // kiểm tra nếu đã có trong bảng shipment
            foreach($dataCheckDetails as $valuecheck)
            {
                if($add_detail['shipment_id'] == $valuecheck['shipment_code'])
                {
                    $add_detail['shipment_update'] = $valuecheck['shipment_id'];
                }
            }
            if(!empty($add_detail['shipment_id']))
            {
                array_push($data_insert_shipment, $add_detail);
                unset($data_add_details[$key]);
            }
        }
        $index = 1;
        $arr_ship = [];
        $arr_shipcode_cre = [];
        foreach($data_insert_shipment as $value)
        {
            $value['quantity'] = str_replace(",", "", $value['quantity']);
            $data_shipment = [];
            $data_purchase = [];
            $data_purchase = [
                'status' =>  $value['purchase_status'],
                'price_edit' =>  $value['price_edit'],
                'order_id' =>$value['order_id'],
                'purchase_code' => $value['purchase_id'],
                'supplier_id' => $value['supplied_id'],
                'purchase_quantity' => $value['quantity'],
                'cost_price' => round($value['cost_price']),
                'total_cost_price' => round($value['total_price']),
                'cost_price_tax' => round($value['cost_price_tax']),
                'total_cost_price_tax' => round($value['total_price_tax']),
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
                'delivery_method' => $value['delivery_method'],
                'shipment_code' => $shipcode,
                'delivery_way' => $value['delivery_way'],
                'shipment_customer' => $value['ship_name1'],
                'shipment_address' => $value['ship_address1'],
                'supplied_id' => $value['supplied_id'],
                'shipment_zip' => $value['ship_zip'],
                'shipment_phone' => $value['ship_phone'],
                'receive_date' => $value['receive_date'],
                'receive_time' => $value['receive_time'],
                'shipment_fee' => round($value['delivery_fee']),
                'shipment_date' => $value['receive_date'],
                'shipment_time' => $value['receive_time'],
                'es_shipment_date' => $value['es_delivery_date_from'],
                'es_shipment_time' => $value['es_delivery_time_from'],
                'supplied_id' => $value['supplied_id'],
                'pay_request' => $value['pay_request'],
                'invoice_id' => $value['invoice_id']
            ];
            $insertShipment = $shipmentModel;
            $insertPurchase = $purchaseModel;
            $orderDetail = $addDetail;
            DB::beginTransaction();
            try {
                if(!empty($value['shipment_delete']))
                {
                    $updateShipment = $shipmentModel;
                    $updateShipment->where('id', $value['shipment_delete'])
                                    ->update([
                                        'del_flg' => 1,
                                        'deleted_by' => $user->name
                                    ]);
                    unset($value['shipment_delete']);
                }
                if(!empty($value['shipment_update']))
                { 
                    $ship_id = $value['shipment_update'];
                    unset($value['shipment_update']);
                } else
                {
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
                }
                $insertPurchase  = $insertPurchase->create($data_purchase);
                $purchase_id = $insertPurchase->id;
                $value['purchase_id'] = $purchase_id;
                $value['shipment_id'] = $ship_id;
                unset($value['purchase_status']);
                unset($value['price_edit']);
                unset($value['es_delivery_time_from']);
                $orderDetail->create($value);
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                Log::debug($exception->getMessage());
                return ['status' => false,
                    'message' =>"Not connect to Databases"
                ];
            }
        }
        foreach ($data_add_details as $add_detail)
        {
            try {
                $addDetail->insert($add_detail);   
            } catch (Exception $exception)
            {
                Log::debug($exception->getMessage());
                return [
                    'status' => false,
                    'message' => "CANN'T CONNECT DATABASE"
                ];
            }
            }  
        }
        

}