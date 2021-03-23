<?php

namespace App\Http\Controllers\Order;

use App\Exports\NotifiedPayExport;
use App\Exports\OrdersExport;
use App\Exports\PackInstructionsExport;
use App\Exports\PurchaseExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Shipments\Shipment;

use App\Repositories\Services\Order\OrderServiceContract;
use App\Repositories\Services\OrderMix\OrderMixServiceContract;
use App\Repositories\Services\Product\ProductServiceContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
// use download file txt
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
// download export excel  notification Rakuten, Yahoo
use App\Exports\NotificationRakutenExport;
use App\Exports\NotificationYahooExport;
use App\Model\HistoryProcess\HistoryProcess;
use App\Repositories\Services\Order\OrderDetailServiceContract;
use App\Repositories\Services\Shipment\ShipmentServiceContract;
use App\Repositories\Services\ShipmentNotification\ShipmentNotificationServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    //
    private $order_service;
    private $product_service;
    private $detail_service;
    private $shipment_service;
    private $shipment_notifi_service;
    protected $historyProcessModel;
    public function __construct(OrderServiceContract $order_service, 
    ProductServiceContract $product_service, OrderDetailServiceContract $detail_service,
    ShipmentServiceContract $shipment_service,
    ShipmentNotificationServiceContract $ShipmentNotification, HistoryProcess $historyProcessModel)
    {

        $this->order_service = $order_service;
        $this->product_service = $product_service;
        $this->detail_service = $detail_service;
        $this->shipment_service = $shipment_service;
        $this->shipment_notifi_service = $ShipmentNotification;
        $this->historyProcessModel = $historyProcessModel;
    }
    /**
     * function index search order
     * @author Dat
     * 2019/10/07
     */
    public function searchIndex(Request $request)
    {
        $param_request = $request->input();
        if(empty($param_request)) // mặc định lấy dữ liệu import ngày hôm nay
        {
            $date = Carbon::now();
            $yesterday = $date->yesterday();
            $today = $date->today();
            // lấy hóa đơn từ ngày hôm qua đến hôm nay 
            $request['date_import_from'] = $yesterday; 
            $request['date_import_to'] =  $today;
        }
        $list_orders = $this->order_service->searchConditions($request); // dữ liệu các hóa đơn
        // $list_total = $this->order_service->getTotalStatus($request);// bảng thống kê hóa đơn
        // $categories_product = $this->product_service->getCategoriesProduct(); // lấy danh sách loại sản phẩm
        // $this->data['list_total'] = $list_total; //
        $this->data['list_orders'] = $list_orders;
        // $this->data['categories_product'] = $categories_product;
        $this->data['title'] = '注文検索'; // xet title cho trang hiện tại
        $this->data['active'] = 1;
        $this->data['purchase_status'] = config('constants.PURCHASE_STATUS');
        return view('orders.search', $this->data);
    }
    /**
     * function search query condition
     * @author Dat
     * 2019/10/09
     */
    function ajax_search_conditions (Request $request) 
    {
        if($request['date_import_from'] > $request['date_import_to']){
            return [
                'status' => 'false'
            ];
        }
        $data = $this->order_service->searchConditions($request);
        return $data;
    }
    /**
     * function update order
     * @author Dat
     * 2019/10/15
     */
    public function ajax_update_order(Request $request)
    {
        $data = $this->order_service->updateOrder($request);
        return $data;
    }
    /**
     * function redirect to edit order
     * @author Dat
     * 2019/10/18
     */
    public function editOrder($orderId = null)
    {   
        $data = $this->order_service->getByOrderId($orderId);
        $support_cus = '';
        $flag_confirm= '';
        $date_order = '';
        $date_import = '';
        $delivery_method = '';
        $shipment_date = '';
        $buyer_zip = '';
        $buyer_tel = '';
        $buyer_email = '';
        $buyer_name = '';
        $buyer_address1 = '';
        $buyer_address2 = '';
        $buyer_address3 = '';
        $detail_order = [];
        if(isset($data['status'])){
            if($data['status'] === false){
                echo "<script type='text/javascript'>alert('".$data['message']."');</script>";
            }
        }else {
            if(count($data)==0)
            {
                echo "<script type='text/javascript'>alert('直接検索に失敗しました。この受注IDは取り込まれていません。');</script>";
            }else {
                $detail_order = $this->order_service->getDetailOrder($data[0]['id']);
                if(count($detail_order) == 0){
                    echo "<script type='text/javascript'>alert('直接検索に失敗しました。この受注IDは取り込まれていません。');</script>";
                }else {
                    $breadcrumbs = [
                        //breabcbums cap 1
                        0 => [
                            0 => '注文検索', // tiêu đề thằng cha
                            1 => 'order/search-order', // url của thằng cha
                        ],
                    ];
                    $this->data['breadcrumbs'] = $breadcrumbs;
                    // dia chi nguoi mua
                    $buyer_address1 = $data[0]['buyer_address_1'];
                    $buyer_address2 = $data[0]['buyer_address_2'];
                    $buyer_address3 = $data[0]['buyer_address_3'];
                    // ten nguoi mua
                    $buyer_name1 = $data[0]['buyer_name1'];
                    $buyer_name2 = $data[0]['buyer_name2'];
                    // email nguoi mua
                    $buyer_email = $data[0]['buyer_email'];
                    // so dien thoai
                    $buyer_tel = $data[0]['buyer_tel1'];
                    if($data[0]['buyer_tel2'] != '')
                    {
                        $buyer_tel .='-' . $data[0]['buyer_tel2'];
                    }
                    if($data[0]['buyer_tel2'] != '')
                    {
                        $buyer_tel .='-' . $data[0]['buyer_tel3'];;
                    }

                    
                    // so buu dien
                    $buyer_zip = $data[0]['buyer_zip1'];
                    if(!empty($data[0]['buyer_zip2']))
                    {
                        $buyer_zip .='-' . $data[0]['buyer_zip2'];
                    } 
                    // status support
                    $support_cus = $data[0]['status'];
                    // flag cofirm
                    $flag_confirm = $data[0]['flag_confirm'];
                    // ngày order
                    $date_order = $data[0]['order_date'];
                    // ngay import
                    $date_import = isset($data[0]['date_import']) ? $data[0]['date_import'] : '' ;
                    // ngay giao hang
                    $shipment_date = $data[0]['delivery_date'];
                    $arr_product_status = [];
                    if(count($detail_order) > 0){
                        foreach($detail_order as $key){
                            $product_status = DB::table('product_statuses')->selectRaw('product_status_id, product_id')->where('product_id', $key['product_id'])->get()->toArray();
                            if(count($product_status) > 0){
                                $str_product_status = '';
                                foreach($product_status as $p_status){
                                    if($p_status->product_status_id == 6){
                                        $str_product_status .= '冷蔵、';
                                    }
                                    if($p_status->product_status_id == 7){
                                        $str_product_status .= '冷凍、';
                                    }
                                }
                                $str_product_status = rtrim($str_product_status, '、');
                                array_push($arr_product_status, $str_product_status);
                            }else {
                                array_push($arr_product_status, '');
                            }
                        }
                    }
                    $this->data['product_status_id'] = $arr_product_status;
                    // $this->data['categories_product'] = $this->product_service->getCategoriesProduct();
                    $this->data['active'] = 1;
                    $this->data['title'] = '注文内容編集';
                    $this->data['order_id'] = $orderId;
                    $this->data['data_order'] = $data;
                    $this->data['support_cus'] = $support_cus;
                    $this->data['flag_confirm'] = $flag_confirm;
                    $this->data['date_order'] = $date_order;
                    $this->data['date_import'] = $date_import;
                    $this->data['shipment_date'] = $shipment_date;
                    $this->data['buyer_zip'] = $buyer_zip;
                    $this->data['buyer_tel'] = $buyer_tel;
                    $this->data['buyer_email'] = $buyer_email;
                    $this->data['buyer_name1'] = $buyer_name1;
                    $this->data['buyer_name2'] = $buyer_name2;
                    $this->data['buyer_address1'] = $buyer_address1;
                    $this->data['buyer_address2'] = $buyer_address2;
                    $this->data['buyer_address3'] = $buyer_address3;
                    $this->data['website_type'] = config('constants.WEB_TYPE')[$data[0]['site_type']];
                    $this->data['purchase_status'] = config('constants.PURCHASE_STATUS');
                    $this->data['detail'] = $detail_order;
                    return view('orders.edit', $this->data);
                }
            }
        }
    }
    /**
     * function ajax_check_shipcode
     * Description: check shipment_code exist at 注文内容編集 & 注文内容新規作成 screen
     * @param
     * arr_shipcode: shipcode array to check
     * @author Dat
     * Created: 2020/06/22
     * Updated: 2021/01/12
     */
    public function ajax_check_shipcode(Request $request){
        $ship_exist = '';
        $result = $this->order_service->checkShipmentExist($request['arr_shipcode']);
        $arr_shipcode = [];
        if(!empty($result)){
            foreach($result as $ship){
                if(!in_array($ship['shipment_code'], $arr_shipcode)){
                    array_push($arr_shipcode, $ship['shipment_code']);
                    $ship_exist .= $ship['shipment_code'].'、';
                }
            }
            $ship_exist = rtrim($ship_exist, '、');
        }
        return $ship_exist;
    }

    /**
     * function edit order with ajax
     * @author Dat
     * 23019/10/22
     */
    public function ajax_edit_order (Request $request, $order_id = null)
    {
        $status_order = config('constants.ORDER_STATUS');
        $status_purchase = config('constants.PURCHASE_STATUS');
        $delivery_method = config('constants.DELIVERY_METHOD');
        $data_post = $request->input('data');
        $data_order  = $data_post['order'];
        $data_old = $this->order_service->getByOrderId($data_order['order_code']);
        $orders_old = $data_old[0];
        $details_old = $this->order_service->getDetailOrder($orders_old['id']);
        $details_new = [];
        $detail_add = [];
        $ship_exist = [];
        if(isset($data_post['detail'])){
            $details_new = $data_post['detail'];
        }
        if(isset($data_post['add_detail'])){
            $detail_add = $data_post['add_detail'];
        }
        if(isset($request['ship_exist'])){
            $ship_exist = $request['ship_exist'];
            $ship_exist = explode('、', $ship_exist);
        }
        //Kiểm tra nếu order không còn sản phẩm nào
        if(!isset($data_post['updated_at']) && !isset($data_post['add_detail'])){
            return [
                'status' => false,
                'message' => '商品リストでSKU、品名、数量を入力してください。'
            ];
        }
        //end

        // check neu da co chinh sua roi thi reload lai trang
        if(isset($data_post['updated_at'])){
            $updated_at = $data_post['updated_at'];
            $date_update =  $this->detail_service->checkUpdate($updated_at);
            if($date_update['validate'] == false)
            {
                return [
                    'status' => false,
                    'message' => 'この受注伝票の情報（金額、送り状番号、納品日など）は変更されました。画面をリロードして最新データで再度ご確認してくださいませ。',
                    'require' => 'reload'
                ];
            }
        }
        // end
        
        //Check log orders
        //Log buyer
        $hisProcess = $this->historyProcessModel;
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '注文内容編集';
        $str_process_description = '<b>受注情報更新:<br>受注ID: '.$orders_old['order_code'].'</b>';
        $status_update = (int)$data_order['status'];
        if($data_order['status'] != $status_update){
            $str_process_description .= '<br>受注ステータス: '.$status_order[$orders_old['status']].' -> '.$status_order[$status_update];
        }
        $pur_date_old = date('Y/m/d', strtotime($orders_old['purchase_date']));
        $pur_date_new = date('Y/m/d', strtotime($data_order['purchase_date']));
        if($pur_date_old != $pur_date_new){
            $str_process_description .= '<br>発注日: '.$pur_date_old.' -> '.$pur_date_new;
        }
        $deli_date_old = date('Y/m/d', strtotime($orders_old['delivery_date']));
        $deli_date_new = date('Y/m/d', strtotime($data_order['delivery_date']));
        if($deli_date_old != $deli_date_new){
            $str_process_description .= '<br>出荷完了日: '.$deli_date_old.' -> '.$deli_date_new;
        }
        $zip_new = $data_order['buyer_zip1'].'-'.$data_order['buyer_zip2'];
        $zip_old = $data_order['buyer_zip1'].'-'.$orders_old['buyer_zip2'];
        if($zip_new != $zip_old){
            $str_process_description .= '<br>注文主〒: '.$zip_old.' -> '.$zip_new;
        }
        $tel_new = $data_order['buyer_tel1'].'-'.$data_order['buyer_tel2'].'-'.$data_order['buyer_tel3'];
        $tel_old = $data_order['buyer_tel1'].'-'.$orders_old['buyer_tel2'].'-'.$orders_old['buyer_tel3'];
        if($tel_new != $tel_old){
            $str_process_description .= '<br>注文主TEL: '.$tel_old.' -> '.$tel_new;
        }
        if($data_order['fax'] != $orders_old['fax']){
            $str_process_description .= '<br>FAX番号: '.$orders_old['fax'].' -> '.$data_order['fax'];
        }
        if($data_order['buyer_email'] != $orders_old['buyer_email']){
            $str_process_description .= '<br>メールアドレス: '.$orders_old['buyer_email'].' -> '.$data_order['buyer_email'];
        }
        if($data_order['buyer_name1'] != $orders_old['buyer_name1'] || $data_order['buyer_name2'] != $orders_old['buyer_name2']){
            $str_process_description .= '<br>注文主名: '.$orders_old['buyer_name1'].$orders_old['buyer_name2'].' -> '.$data_order['buyer_name1'].$data_order['buyer_name2'];
        }
        if($data_order['money_daibiki'] != $orders_old['money_daibiki']){
            $str_process_description .= '<br>代引き金額: '.$orders_old['money_daibiki'].' -> '.$data_order['money_daibiki'];
        }
        if($data_order['buyer_address_1'] != $orders_old['buyer_address_1'] || $data_order['buyer_address_2'] != $orders_old['buyer_address_2'] || $data_order['buyer_address_3'] != $orders_old['buyer_address_3']){
            $str_process_description .= '<br>注文者住所: '.$orders_old['buyer_address_1'].$orders_old['buyer_address_2'].$orders_old['buyer_address_3'].' -> '.$data_order['buyer_address_1'].$data_order['buyer_address_2'].$data_order['buyer_address_3'];
        }
        
        //Log product
        $des_product_del = '';
        foreach($details_old as $val_old) {
            $check_del_product = 0;
            foreach($details_new as $key_detail_old => $val_new){
                if(intval($val_new['id']) == $val_old['id']){
                    if($key_detail_old == 0){
                        $str_process_description .= '<br><b style="color: red;">商品リスト</b>';
                    }
                    $check_del_product++;
                    if($val_new['shipment_id'] != $val_old['shipment_code']){
                        $str_process_description .= '<br>送り状番号('.$val_old['purchase_code'].'): '.$val_old['shipment_code'].' -> '.$val_new['shipment_id'];
                    }
                    if($val_new['product_id'] != intval($val_old['product_id'])){
                        $str_process_description .= '<br>SKU('.$val_old['purchase_code'].'): '.$val_old['product_code'].' -> '.$val_new['product_code'];
                    }
                    if($val_new['quantity'] != $val_old['quantity']){
                        $str_process_description .= '<br>数量('.$val_old['purchase_code'].'): '.$val_old['quantity'].' -> '.$val_new['quantity'];
                    }
                    if($val_new['price_sale_tax'] != $val_old['price_sale_tax']){
                        $str_process_description .= '<br>売価(税込)('.$val_old['purchase_code'].'): '.number_format($val_old['price_sale_tax'], 0, '.', ',').' -> '.number_format($val_new['price_sale_tax'], 0, '.', ',');
                    }
                    if($val_new['cost_price'] != $val_old['cost_price']){
                        $str_process_description .= '<br>原価(税抜)('.$val_old['purchase_code'].'): '.number_format($val_old['cost_price'], 0, '.', ',').' -> '.number_format($val_new['cost_price'], 0, '.', ',');
                    }
                    if($val_new['price_edit'] != $val_old['price_edit']){
                        $str_process_description .= '<br>訂正金額(税抜)('.$val_old['purchase_code'].'): '.number_format($val_old['price_edit'], 0, '.', ',').' -> '.number_format($val_new['price_edit'], 0, '.', ',');
                    }
                    break;
                }
            }
            if($check_del_product == 0){
                $des_product_del .= '(発注番号: '.$val_old['purchase_code'].' - SKU: '.$val_old['product_code'].')、';
            }
        }
        if($des_product_del != ''){
            $des_product_del = rtrim($des_product_del, '、');
            $str_process_description .= '<br>商品削除: '.$des_product_del;
        }
        //Log add product
        if(isset($data_post['add_detail'])){
            $add_details  = $data_post['add_detail'];
            $str_process_description .= '<br><b style="color: red;">商品追加</b><br>';
            foreach($add_details as $val_add){
                $str_process_description .= '発注番号: '.$val_add['purchase_id'].' - SKU: '.$val_add['product_code'].' - 数量: '.$val_add['quantity']
                                            .' - お届け先名: '.$val_add['ship_name1'].' - お届け先TEL: '.$val_add['ship_phone'].' - お届け先住所:'.$val_add['ship_address1']
                                            .$val_add['ship_address2'].$val_add['ship_address3'];
            }
        }

        //Log shipment
        $list_shipment_del = [];
        if(isset($data_post['list_shipment_del'])){
            $list_shipment_del = $data_post['list_shipment_del'];
        }
        $flag_ship_del = 0;
        foreach($details_old as $key_detail_old => $val_old) {
            foreach($details_new as $val_new){
                if(intval($val_new['shipment_index']) == $val_old['shipment_id']){
                    if(!in_array($val_old['shipment_id'], $list_shipment_del)){
                        $str = '';
                        if($val_new['delivery_method'] != $val_old['delivery_method']){
                            $str .= '配送方法: '.$delivery_method[intval($val_old['delivery_method'])].' -> '.$delivery_method[intval($val_new['delivery_method'])].'、';
                        }
                        if(intval($val_new['purchase_status']) != $val_old['purchase_status']){
                            $str .= '発注ステータス: '.$status_purchase[intval($val_old['purchase_status']-1)].' -> '.$status_purchase[intval($val_new['purchase_status']-1)].'、';
                        }
                        if(intval($val_new['purchase_status']) != $val_old['purchase_status']){
                            $str .= '納品方法: '.$status_purchase[intval($val_old['purchase_status']-1)].' -> '.$status_purchase[intval($val_new['purchase_status']-1)].'、';
                        }
                        if($val_new['shipment_id'] != $val_old['shipment_code']){
                            $str .= '送り状番号: '.$val_old['shipment_code'].' -> '.$val_new['shipment_id'].'、';
                        }
                        if($val_new['es_delivery_date_from'] != date('Y/m/d', strtotime($val_old['es_shipment_date']))){
                            $str .= '集荷日時(日): '.date('Y/m/d', strtotime($val_old['es_shipment_date'])).' -> '.$val_new['es_delivery_date_from'].'、';
                        }
                        if($val_new['es_delivery_time_from'] != $val_old['es_shipment_time']){
                            $str .= '集荷日時(時): '.$val_old['es_shipment_time'].' -> '.$val_new['es_delivery_time_from'].'、';
                        }
                        if($val_new['receive_date'] != date('Y/m/d', strtotime($val_old['shipment_date']))){
                            $str .= '配達日時(日): '.date('Y/m/d', strtotime($val_old['shipment_date'])).' -> '.$val_new['receive_date'].'、';
                        }
                        if($val_new['receive_time'] != $val_old['receive_time']){
                            $str .= '配達日時(時): '.$val_old['receive_time'].' -> '.$val_new['receive_time'].'、';
                        }
                        if(intval($val_new['supplied_id']) != $val_old['supplied_id']){
                            $str .= '集荷先: '.$val_old['supplied'].' -> '.$val_new['supplied'].'、';
                        }
                        if($val_new['ship_zip'] != $val_old['ship_zip']){
                            $str .= 'お届け先〒: '.$val_old['ship_zip'].' -> '.$val_new['ship_zip'].'、';
                        }
                        if($val_new['ship_phone'] != $val_old['ship_phone']){
                            $str .= 'お届け先TEL: '.$val_old['ship_phone'].' -> '.$val_new['ship_phone'].'、';
                        }
                        if(number_format($val_new['delivery_fee'], 0, '.', ',') != number_format($val_old['delivery_fee'], 0, '.', ',')){
                            $str .= '送料: '.number_format($val_old['delivery_fee'], 0, '.', ',').' -> '.number_format($val_new['delivery_fee'], 0, '.', ',').'、';
                        }
                        if($val_new['ship_address1'] != $val_old['ship_address1'] || $val_new['ship_address2'] != $val_old['ship_address2'] || $val_new['ship_address3'] != $val_old['ship_address3']){
                            $str .= 'お届け先住所: '.$val_old['ship_address1'].$val_old['ship_address2'].$val_old['ship_address3'].' -> '.$val_new['ship_address1'].$val_new['ship_address2'].$val_new['ship_address3'].'、';
                        }
                        if($str != ''){
                            $flag_ship_del++;
                            if($flag_ship_del == 1){
                                $str_process_description .= '<br><b style="color: red;">お届け先情報</b><br>';
                            }
                            $str = rtrim($str, '、');
                            $str_process_description .= '[[お届け先名: '.$val_old['ship_name1'].' - TEL: '.$val_old['ship_phone'].' - 〒: '.$val_old['ship_zip']
                            .' - 住所: '.$val_old['ship_address1'].$val_old['ship_address2'].$val_old['ship_address3'].']: '.$str.']<br>';
                        }
                    }
                    break;
                }
            }
        }
        // $str_process_description = rtrim($str_process_description, '<br>');
        //Check log orders
        if(isset($data_post['add_detail']))
        {
            $data_add_details  = $data_post['add_detail'];
            $this->detail_service->addOrderDetails($data_add_details,$order_id, $ship_exist);
        }
        $data_detail = [];
        if(isset($data_post['detail'])){
            $data_detail = $data_post['detail'];
        }
        $list_order_del = [];
        if(isset($data_post['list_order_del'])){
            $list_order_del = $data_post['list_order_del'];
        }
        $results = $this->detail_service->editOrderDetails($data_detail, $order_id, $list_order_del, $ship_exist); // chỉnh sửa thêm mới order detail và shipments
        $results = $this->order_service->editOrder($data_order, $order_id); // edit order
        // Chỉnh sửa order details hoặc thêm mới shipment.
        if($results['status']){
            if(isset($data_post['shipments_id'])){
                $des_ship_del = '';
                // Kiểm tra nếu không chọn người nhận khi chỉnh sửa order thì xóa shipment
                $check_del_ship = array_unique($data_post['shipments_id']);
                try
                {
                    $shipmentModel = new Shipment();
                    foreach($details_old as $val_old) {
                        if(!in_array($val_old['shipment_id'], $check_del_ship)){
                            $des_ship_del .= '(お届け先名: '.$val_old['ship_name1'].' - TEL: '.$val_old['ship_phone'].' - 〒: '.$val_old['ship_zip']
                            .' - 住所: '.$val_old['ship_address1'].$val_old['ship_address2'].$val_old['ship_address3'].')、';
                            $deleteShipment = $shipmentModel;
                            $deleteShipment->where('id', (int)$val_old['shipment_id'])->delete();
                        }
                    }
                } catch (Exception $exception){
                    return [
                        'status' => false,
                        'message' =>"Not connect to Databases"
                    ];
                }
                if($des_ship_del != ''){
                    $des_ship_del = rtrim($des_ship_del, '、');
                    $str_process_description .= '<br>お届け先削除: '.$des_ship_del;
                }
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
        }
        return $results;
    }
    /**
     * function create order
     * @author Dat
     * 2019/10/25
     * @url order/create
     */
    public function createOrder()
    {
        $this->data['title'] = '注文内容新規作成';
        $breadcrumbs = [
            //breabcbums cap 1
            0 => [
                0 => '注文検索', // tiêu đề thằng cha
                1 => 'order/search-order', // url của thằng cha
            ],
        ];
        $this->data['active'] = 1;
        $this->data['breadcrumbs'] = $breadcrumbs;
        // $this->data['categories_product'] = $this->product_service->getCategoriesProduct(); // load categories in modal sku
        $order_id = $this->order_service->getLastId();
        $this->data['order_code'] = "RM-C-".$order_id['id'];
        $this->data['purchase_status'] = config('constants.PURCHASE_STATUS');
        return view('orders.create', $this->data);
    }
    /**
     * function create order
     * @author Dat
     * 2019/10/26
     * @url /order/ajax-create-order
     * @param
     * {
     *  order : {}
     *  add_detail: []
     * }
     */
    public function ajax_create_order(Request $request)
    {
        $data_order = [];
        $data_add_details = [];
        $ship_exist = [];
        if(isset($request['ship_exist'])){
            $ship_exist = $request['ship_exist'];
            $ship_exist = explode('、', $ship_exist);
        }
        if(isset($request->input('data')['order']))
        {
            $data_order = $request->input('data')['order'];
        }
        if(isset($request->input('data')['add_detail']))
        {
            $data_add_details = $request->input('data')['add_detail'];
        }
        $data_create = $this->order_service->createOrder($data_order, $data_add_details, $ship_exist);
        return $data_create;
    }
    /**
     * function export excel
     * @author Dat
     * 2019/10/28
     */
    public function ExportPurchase (Request $request)
    {
        $type_export = '';
        return $request->input('order_list');
        $data = $this->order_service->getDataExport();
        return Excel::download(new PurchaseExport($data), 'text.xlsx');
    }
    /**
     * function ExportBillYamoto
     * @author Dat
     * 2019/12/04
     */
    public function ExportBillYamoto() 
    {
    }
    /**
     * function ExportNotificationAmazon
     * @author Dat
     * 2019/12/06
     */
    public function ExportNotificationAmazon(Request $request) 
    {
        $hisProcess = $this->historyProcessModel;
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $file_name = '';
        $date = Carbon::now();
        $dateMonthYear = $date->format('Ymd');
        $file_name = "Amazon_";
        if($request['screen'] == 7){
            if(intval($request['site_type']) == 8){
                $file_name = 'Amazon_liquor_';
            }else if(intval($request['site_type']) == 7){
                $file_name = 'Amazon_wf_';
            }else if(intval($request['site_type']) == 6){
                $file_name = 'Amazon_hf_';
            }else if(intval($request['site_type']) == 5){
                $file_name = 'Amazon_world_';
            }else if(intval($request['site_type']) == 4){
                $file_name = 'Amazon_hirosima_';
            }
            $insert_HP['process_screen'] = '出荷通知';
        }else if($request['screen'] == 3){
            $insert_HP['process_screen'] = '注文検索';
        }
        $file_name = $file_name.$dateMonthYear.'.txt';
        $str_process_description = '<b>ダウンロード</b>: Amazon用出荷通知データ<br>';
        $content = "TemplateType=OrderFulfillment\tVersion=2011.1102\r\n";
        $content .="注文番号	注文商品番号	出荷数	出荷日	配送業者コード	配送業者名	お問い合わせ伝票番号	配送方法	代金引換\r\n";
        $content .="order-id	order-item-id	quantity	ship-date	carrier-code	carrier-name	tracking-number	ship-method	cod-collection-method\r\n";
        $data = [];
        $string_list_details = trim($request->input('list_details'), ',');
        $array_list_details = explode(',', $string_list_details);
        $data = $this->shipment_service->ExportNotificationAmazon($array_list_details);
        $arr_purchase = [];
        
        foreach($data as $value) {
            $str_process_description .= '受注ID: '.$value['order_code'].'('.$value['purchase_code'].')、';
            if(!in_array($value['purchase_id'], $arr_purchase)){
                array_push($arr_purchase, $value['purchase_id']);
            }
            $tracking_number = '';
            switch(intval($value['delivery_method'])){
                case 1:
                case 7:
                case 9:
                    $tracking_number = '佐川';
                    break;
                case 2:
                case 3:
                case 4:
                    $tracking_number = 'ヤマト';
                    break;
                case 5:
                case 6:
                    $tracking_number = '日本郵便';
                    break;
                case 8:
                    $tracking_number = 'その他';
                    break;
            }
            $shipdate = (!empty($value['es_shipment_date'])) ? date('Y/m/d', strtotime($value['es_shipment_date'])) : '';
            $content .= $value['order_code']."			"
                        .$shipdate."	".$value['delivery_providers']."	"
                        .$tracking_number."\t".$value['shipment_code']."			\r\n";	
        }
        $str_process_description = rtrim($str_process_description, '、');   
        if($request['stage3'] == 3){ 
            $str_process_description .= '<br>発注ステータスを出荷済に変更する';
            $this->shipment_notifi_service->updateStatusAtShipmentNotification($arr_purchase);
        }
        $insert_HP['process_description'] = $str_process_description;
        $hisProcess->create($insert_HP);

        $content = mb_convert_encoding($content, "SJIS-win", "auto");
        $headers = [
            'Content-type' => 'application/octet-stream', 
            'Content-Disposition' => sprintf('attachment; filename="%s"', $file_name),
            'Content-Length' => strlen($content)
        ];
        return Response::make($content, 200, $headers);
    }
    /**
     * function copy order
     * @author Dat
     * @param order_code = array()
     */
    public function ajax_copy_order(Request $request)
    {
        $data = $this->order_service->copyOrders($request->input());
        return $data;
    }
    /**
     * functinon delete order 
     * @author Dat
     * @param order_code = array()
     */
    public function ajax_delete_order(Request $request) 
    {
        $data =  $this->order_service->deleteOrders($request->input());
        return $data;
    }
}