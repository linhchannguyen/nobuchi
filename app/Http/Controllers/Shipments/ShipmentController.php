<?php

namespace App\Http\Controllers\Shipments;

use App\Exports\ShipmentBillExport;
use App\Exports\ShipmenSagawaIIExport;
use App\Exports\ExportCSV;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Repositories\Services\Shipment\ShipmentServiceContract;
use App\Repositories\Services\ShipmentNotification\ShipmentNotificationServiceContract;
use App\Model\HistoryProcess\HistoryProcess;
use App\Exports\ShipmentExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentController extends Controller
{
    /**
     * class home controller
     * class controll the data and show data in view
     * @author Chan
     * date 2019/10/03
     */
    private $ShipmentService;
    private $ShipmentNotification;
    private $ExportCSV;
    protected $historyProcessModel;
    public function __construct(ShipmentServiceContract $ShipmentService, ShipmentNotificationServiceContract $ShipmentNotification, HistoryProcess $historyProcessModel)
    {
        $this->ExportCSV = new ExportCSV();
        $this->ShipmentService = $ShipmentService;
        $this->ShipmentNotification = $ShipmentNotification;
        $this->historyProcessModel = $historyProcessModel;
    }
    /**
     * function index
     * @author Chan
     * date 2019/10/03
     */
    public function index()
    {
        $this->data['title'] = '送り状出力';
        $this->data['active'] = 4;
        return view('shipments.index', $this->data);
    }

    /**
     * function ajax_search_shipment
     * Description: lấy thông tin để thống kê tình trạng hỗ trợ và cách phân phối
     * @author: channl
     * Created: 2019/10/03
     * Updated: 2019/10/14
     */
    public function ajax_search_shipment(Request $request){
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        // $this->data['data_table1'] = $this->ShipmentService->getTotalOrder($range, $date_from, $date_to); 
        $this->data['data_table2'] = $this->ShipmentService->getListShipmentByDeliveryMethod($range, $date_from, $date_to);
        $result = array (
            'data_2' => $this->data['data_table2'],
        );
        return Response::json($result);
    }
    
    /**
     * function ajax_export_shipment
     * Description: export shipment
     * @author: channl
     * Created: 2019/10/27
     * Updated: 2019/10/27
     */
    public function ajax_export_shipment(Request $request){
        $hisProcess = $this->historyProcessModel;
        $collection = null;
        $arr_range = [
            0 => '受注日',
            1 => '取込日',
            2 => '発注日',
            3 => '出荷完了日',
            4 => '集荷日時',
            5 => '配達日時'
        ];
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        // mảng order detail id từ màn hình tìm kiếm order
        $list_order_details = [];
        if(!empty($request->get('arr_detail')))
        {
            $list_order_details = $request->get('arr_detail');
        }
        $delivery_method = (int)$request->get('delivery_method');//phạm vi tìm kiếm
        $stage1 = $request->get('stage1') != null ? $request->get('stage1') : null;//stage: bỏ loại đang bảo lưu
        $stage2 = $request->get('stage2') != null ? $request->get('stage2') : null;//stage: bỏ loại xuất hàng của công ty
        $stage3 = $request->get('stage3') != null ? $request->get('stage3') : null;//stage: đổi tình trạng hỗ trợ thành đang xử lý xuất hàng.
        $request_search = [
            'range' => $arr_range[$range],
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        $data = $this->ShipmentService->exportShipment($list_order_details, $date_from, $date_to, $delivery_method, $range, $stage1, $stage2);
        $arr_collect = [];
        $purchase_list = [];
        $flag_bill = '';
        // $arr_shipment = [];
        if(count($data) > 0){
            $insert_HP = array();
            $insert_HP['process_user'] = auth()->user()->login_id;
            $insert_HP['process_permission'] = auth()->user()->type;
            $flag_download = $request->get('flag_download_bill');
            if(!empty($flag_download)){
                $flag_bill = $flag_download;
            }
            if($request->get('screen') == 6){
                $insert_HP['process_screen'] = '送り状出力';
            }else {
                $insert_HP['process_screen'] = '注文検索';
            }
            $act = '';
            if($request->has('act')){
                $act = $request->get('act');
            }else {
                if(in_array($delivery_method, [2,3,4])){
                    $act = 'ヤマト用送り状データ';
                }else {
                    $act = 'ゆうパック用送り状データ';
                }
            }
            $str_process_description = '<b>ダウンロード</b>: '.$act.'<br>';
            //Push product_id vào mảng để lấy danh sách product_status
            $arr_product_statuses = [];
            $list_product_statuses = [];
            foreach($data as $val){
                if(!in_array($val['product_id'], $arr_product_statuses)){
                    array_push($arr_product_statuses, $val['product_id']);
                }
            }
            if(!empty($arr_product_statuses)){
                $list_product_statuses = $this->ShipmentService->getListProductStatus($arr_product_statuses);
            }
            foreach($data as $val){
                $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';
                //Kiểm tra nếu order trùng thì đưa vào arr
                if(!in_array($val['purchase_id'], $purchase_list))
                {
                    array_push($purchase_list, $val['purchase_id']);//push những order id không có trong mảng vào orders_list
                }
                switch ($val['delivery_method']) {
                    case 1:
                    case 2:
                    case 5:
                    case 7:
                    case 8:
                    case 9:
                        $val['delivery_method'] = '0';
                        break;
                    case 3:
                        $val['delivery_method'] = 7;
                        break;
                    case 4:
                        $val['delivery_method'] = 8;
                        break;
                    case 6:
                        $val['delivery_method'] = 9;
                        break;
                }
                $flag = 0;//Cờ kiểm tra nếu không có product_status_id thì 
                if(!empty($list_product_statuses)){
                    foreach($list_product_statuses as $prod_status){//Kiểm tra nếu có trong bảng product_statuses thì thay đổi giá trị product_status
                        if(intval($prod_status->product_id) == intval($val['product_id'])){
                            $flag++;
                            if((($delivery_method == 5 || $delivery_method == 6 ) && $flag_bill == '')  || ($flag_bill != '' && $flag_bill == 'yupack')) {
                                //End kiểm tra nếu order trùng thì đưa vào arr
                                switch ($val['shipment_time']) {
                                    case '午前中':
                                        $val['shipment_time'] = '51';
                                        break;
                                    case '12時～14時':
                                        $val['shipment_time'] = '52';
                                        break;
                                    case '14時～16時':
                                        $val['shipment_time'] = '53';
                                        break;
                                    case '16時～18時':
                                        $val['shipment_time'] = '54';
                                        break;
                                    case '18時～20時':
                                        $val['shipment_time'] = '55';
                                        break;
                                    case '19時以降':
                                        $val['shipment_time'] = '56';
                                        break;
                                    default:
                                        $val['shipment_time'] = '';
                                        break;
                                }
                                if($prod_status->product_status_id == 6 || $prod_status->product_status_id == "6"){
                                    $prod_status->product_status_id = '1';
                                }else if($prod_status->product_status_id == 7 || $prod_status->product_status_id == "7"){
                                    $prod_status->product_status_id = '2';
                                }
                                // Xuất excel giấy gửi hàng
                                $shipment = [
                                    'name' => $val['name'],
                                    'order_code' => $val['order_code'],
                                    'buyer' => $val['buyer'],
                                    'buyer_add' => $val['buyer_add'],
                                    'buyer_zip' => $val['buyer_zip1'].$val['buyer_zip2'],
                                    'buyer_phone' => $val['buyer_tel1'],
                                    'date_import' => $val['date_import'],
                                    'ship_name' => $val['ship_name'],
                                    'ship_add' => $val['ship_add'],
                                    'ship_zip' => $val['ship_zip'],
                                    'ship_phone' => $val['ship_phone'],
                                    'product_code' => $val['product_code'],
                                    'product_name' => $val['product_name'],
                                    'quantity' => $val['quantity'],
                                    'g_name1' => $val['g_name1'],
                                    'g_name2' => $val['g_name2'],
                                    'g_name3' => $val['g_name3'],
                                    'g_name4' => $val['g_name4'],
                                    'g_name5' => $val['g_name5'],
                                    'purchase_code' => $val['purchase_code'],
                                    'export_es_ship_date' => isset($val['export_es_ship_date']) ? Carbon::parse($val['export_es_ship_date'])->format('Y/m/d') : null,
                                    'shipment_date' => isset($val['shipment_date']) ? Carbon::parse($val['shipment_date'])->format('Y/m/d') : null,
                                    'shipment_time' => $val['shipment_time'],
                                    'productStatus' => $prod_status->product_status_id,
                                    'delivery_method' => $val['delivery_method'],
                                ];
                            }else {
                                //End kiểm tra nếu order trùng thì đưa vào arr
                                switch ($val['shipment_time']) {
                                    case '午前中':
                                        $val['shipment_time'] = '0812';
                                        break;
                                    case '12時～14時':
                                        $val['shipment_time'] = '午前中';
                                        break;
                                    case '14時～16時':
                                        $val['shipment_time'] = '1416';
                                        break;
                                    case '16時～18時':
                                        $val['shipment_time'] = '1618';
                                        break;
                                    case '18時～20時':
                                        $val['shipment_time'] = '1820';
                                        break;
                                    case '19時以降':
                                        $val['shipment_time'] = '1921';
                                        break;
                                    default:
                                        $val['shipment_time'] = '';
                                        break;
                                }
                                if($prod_status->product_status_id == 6 || $prod_status->product_status_id == "6"){
                                    $prod_status->product_status_id = '2';
                                }else if($prod_status->product_status_id == 7 || $prod_status->product_status_id == "7"){
                                    $prod_status->product_status_id = '1';
                                }
                                // Xuất excel giấy gửi hàng
                                $shipment = [
                                    'name' => $val['name'],
                                    'order_code' => $val['order_code'],
                                    'buyer' => $val['buyer'],
                                    'export_buyer_address_1' => $val['export_buyer_address_1'],
                                    'export_buyer_address_2' => $val['export_buyer_address_2'],
                                    'export_buyer_address_3' => $val['export_buyer_address_3'],
                                    'buyer_zip' => $val['buyer_zip1'].$val['buyer_zip2'],
                                    'buyer_phone' => $val['buyer_tel1'],
                                    'date_import' => $val['date_import'],
                                    'ship_name' => $val['ship_name'],
                                    'export_ship_add1' => $val['export_ship_add1'],
                                    'export_ship_add2' => $val['export_ship_add2'],
                                    'export_ship_add3' => $val['export_ship_add3'],
                                    'ship_zip' => $val['ship_zip'],
                                    'ship_phone' => $val['ship_phone'],
                                    'product_code' => $val['product_code'],
                                    'product_name' => $val['product_name'],
                                    'quantity' => $val['quantity'],
                                    'g_name1' => $val['g_name1'],
                                    'g_name2' => $val['g_name2'],
                                    'g_name3' => $val['g_name3'],
                                    'g_name4' => $val['g_name4'],
                                    'g_name5' => $val['g_name5'],
                                    'purchase_code' => $val['purchase_code'],
                                    'export_es_ship_date' => isset($val['export_es_ship_date']) ? Carbon::parse($val['export_es_ship_date'])->format('Y/m/d') : null,
                                    'shipment_date' => isset($val['shipment_date']) ? Carbon::parse($val['shipment_date'])->format('Y/m/d') : null,
                                    'shipment_time' => $val['shipment_time'],
                                    'productStatus' => $prod_status->product_status_id,
                                    'delivery_method' => $val['delivery_method'],
                                ];
                            }
                            array_push($arr_collect, $shipment);
                            // End xuất excel giấy gửi hàng
                        }
                    }
                }
                if($flag == 0){
                    if ((($delivery_method == 5 || $delivery_method == 6 ) && $flag_bill == '')  || ($flag_bill != '' && $flag_bill == 'yupack')) {
                        //End kiểm tra nếu order trùng thì đưa vào arr
                        switch ($val['shipment_time']) {
                            case '午前中':
                                $val['shipment_time'] = '51';
                                break;
                            case '12時～14時':
                                $val['shipment_time'] = '52';
                                break;
                            case '14時～16時':
                                $val['shipment_time'] = '53';
                                break;
                            case '16時～18時':
                                $val['shipment_time'] = '54';
                                break;
                            case '18時～20時':
                                $val['shipment_time'] = '55';
                                break;
                            case '19時以降':
                                $val['shipment_time'] = '56';
                                break;
                            default:
                                $val['shipment_time'] = '';
                                break;
                        }
                        // Xuất excel giấy gửi hàng
                        $shipment = [
                            'name' => $val['name'],
                            'order_code' => $val['order_code'],
                            'buyer' => $val['buyer'],
                            'buyer_add' => $val['buyer_add'],
                            'buyer_zip' => $val['buyer_zip1'].$val['buyer_zip2'],
                            'buyer_phone' => $val['buyer_tel1'],
                            'date_import' => $val['date_import'],
                            'ship_name' => $val['ship_name'],
                            'ship_add' => $val['ship_add'],
                            'ship_zip' => $val['ship_zip'],
                            'ship_phone' => $val['ship_phone'],
                            'product_code' => $val['product_code'],
                            'product_name' => $val['product_name'],
                            'quantity' => $val['quantity'],
                            'g_name1' => $val['g_name1'],
                            'g_name2' => $val['g_name2'],
                            'g_name3' => $val['g_name3'],
                            'g_name4' => $val['g_name4'],
                            'g_name5' => $val['g_name5'],
                            'purchase_code' => $val['purchase_code'],
                            'export_es_ship_date' => isset($val['export_es_ship_date']) ? Carbon::parse($val['export_es_ship_date'])->format('Y/m/d') : null,
                            'shipment_date' => isset($val['shipment_date']) ? Carbon::parse($val['shipment_date'])->format('Y/m/d') : null,
                            'shipment_time' => $val['shipment_time'],
                            'productStatus' => "",
                            'delivery_method' => $val['delivery_method'],
                        ];
                    } else {
                        //End kiểm tra nếu order trùng thì đưa vào arr
                        switch ($val['shipment_time']) {
                            case '午前中':
                                $val['shipment_time'] = '0812';
                                break;
                            case '12時～14時':
                                $val['shipment_time'] = '午前中';
                                break;
                            case '14時～16時':
                                $val['shipment_time'] = '1416';
                                break;
                            case '16時～18時':
                                $val['shipment_time'] = '1618';
                                break;
                            case '18時～20時':
                                $val['shipment_time'] = '1820';
                                break;
                            case '19時以降':
                                $val['shipment_time'] = '1921';
                                break;
                            default:
                                $val['shipment_time'] = '';
                                break;
                        }
                        // Xuất excel giấy gửi hàng
                        $shipment = [
                            'name' => $val['name'],
                            'order_code' => $val['order_code'],
                            'buyer' => $val['buyer'],
                            'export_buyer_address_1' => $val['export_buyer_address_1'],
                            'export_buyer_address_2' => $val['export_buyer_address_2'],
                            'export_buyer_address_3' => $val['export_buyer_address_3'],
                            'buyer_zip' => $val['buyer_zip1'].$val['buyer_zip2'],
                            'buyer_phone' => $val['buyer_tel1'],
                            'date_import' => $val['date_import'],
                            'ship_name' => $val['ship_name'],
                            'export_ship_add1' => $val['export_ship_add1'],
                            'export_ship_add2' => $val['export_ship_add2'],
                            'export_ship_add3' => $val['export_ship_add3'],
                            'ship_zip' => $val['ship_zip'],
                            'ship_phone' => $val['ship_phone'],
                            'product_code' => $val['product_code'],
                            'product_name' => $val['product_name'],
                            'quantity' => $val['quantity'],
                            'g_name1' => $val['g_name1'],
                            'g_name2' => $val['g_name2'],
                            'g_name3' => $val['g_name3'],
                            'g_name4' => $val['g_name4'],
                            'g_name5' => $val['g_name5'],
                            'purchase_code' => $val['purchase_code'],
                            'export_es_ship_date' => isset($val['export_es_ship_date']) ? Carbon::parse($val['export_es_ship_date'])->format('Y/m/d') : null,
                            'shipment_date' => isset($val['shipment_date']) ? Carbon::parse($val['shipment_date'])->format('Y/m/d') : null,
                            'shipment_time' => $val['shipment_time'],
                            'productStatus' => "",
                            'delivery_method' => $val['delivery_method'],
                        ];
                    }
                    array_push($arr_collect, $shipment);
                    // End xuất excel giấy gửi hàng
                }
            }
            $str_process_description = rtrim($str_process_description, '、');
            // Có chọn check đổi tình trạng hỗ trợ và không phải download chi tiết đóng gói, đặt hàng thì mới cập nhật tình trạng
            if($stage3 == 3){
                $str_process_description .= '<br>発注ステータスを送り状作成済に変更する';  
                $this->ShipmentService->updateStatusAtShipment($purchase_list);
            }
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);

            $collection = collect($arr_collect);
        }else {
            $collection = collect($arr_collect);            
        }
        // // End thêm mới giấy gửi hàng (shipment)
        return Excel::download(new ShipmentExport($collection, $request_search, $delivery_method, $flag_bill), 'text.xlsx');
    }
    /**
     * function ajax_export_shipment_II
     * Description: export shipment by delivery_method = 9 (Sagawa (bí mật Ⅱ))
     * @author: channl
     * Created: 2020/10/12
     * Updated: 2020/10/12
     */
    public function ajax_export_shipment_II(Request $request){
        $hisProcess = $this->historyProcessModel;
        $collection = null;
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        // mảng order detail id từ màn hình tìm kiếm order
        $list_order_details = [];
        if(!empty($request->get('list_details')))
        {
            $list_order_details = $request->get('list_details');
            $list_order_details = rtrim($list_order_details, ',');
            $list_order_details = explode(',', $list_order_details);
        }
        $delivery_method = (int)$request->get('delivery_method');//phạm vi tìm kiếm
        $stage1 = $request->get('stage1') != null ? $request->get('stage1') : null;//stage: bỏ loại đang bảo lưu
        $stage2 = $request->get('stage2') != null ? $request->get('stage2') : null;//stage: bỏ loại xuất hàng của công ty
        $stage3 = $request->get('stage3') != null ? $request->get('stage3') : null;//stage: đổi tình trạng hỗ trợ thành đang xử lý xuất hàng.
        $data = $this->ShipmentService->exportShipment($list_order_details, $date_from, $date_to, $delivery_method, $range, $stage1, $stage2);
        $arr_collect = [];
        $purchase_list = [];
        $arr_shipment = [];
        if(count($data) > 0){
            $insert_HP = array();
            $insert_HP['process_user'] = auth()->user()->login_id;
            $insert_HP['process_permission'] = auth()->user()->type;
            $insert_HP['process_screen'] = ($request->get('screen') == 3) ? '注文検索': '送り状出力';
            $str_process_description = '<b>ダウンロード</b>: 出荷通知データ<br>';
            //Push product_id vào mảng để lấy danh sách product_status
            $arr_product_statuses = [];
            $list_product_statuses = [];
            foreach($data as $val){
                if(!in_array($val['product_id'], $arr_product_statuses)){
                    array_push($arr_product_statuses, $val['product_id']);
                }
            }
            if(!empty($arr_product_statuses)){
                $list_product_statuses = $this->ShipmentService->getListProductStatus($arr_product_statuses);
            }
            foreach($data as $val){
                $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';
                //Kiểm tra nếu order trùng thì đưa vào arr
                if(!in_array($val['purchase_id'], $purchase_list))
                {
                    array_push($purchase_list, $val['purchase_id']);//push những order id không có trong mảng vào orders_list
                }
                switch ($val['shipment_time']) {
                    case '午前中':
                        $val['shipment_time'] = '01';
                        break;
                    case '12時～14時':
                        $val['shipment_time'] = '12';
                        break;
                    case '14時～16時':
                        $val['shipment_time'] = '14';
                        break;
                    case '16時～18時':
                        $val['shipment_time'] = '16';
                        break;
                    case '18時～20時': case '19時以降':
                        $val['shipment_time'] = '04';
                        break;
                    default:
                        $val['shipment_time'] = '';
                        break;
                }
                $product_name_3 = '';
                $product_name_4 = '';
                if($val['product_name'] != null || $val['product_name'] != ''){
                    $val['product_name'] = mb_convert_kana($val['product_name'], 'KVRN');
                    $str_product_name = self::convertString($val['product_name']);
                    if(mb_strlen($str_product_name) < 16){
                        $product_name_3 = $str_product_name;
                    }else {
                        $product_name_3 = mb_substr($str_product_name, 0, 16);
                    }
                    if(mb_strlen($str_product_name) > 16){
                        if(mb_strlen($str_product_name) >= 32){
                            $product_name_4 = mb_substr($str_product_name, 16, 16);
                        }else {
                            $product_name_4 = mb_substr($str_product_name, 16, 16);
                        }
                    }
                }
                $seal_1 = '';
                $seal_2 = '';
                $seal_3 = '';
                if($val['delivery_date'] != null || $val['delivery_date'] != ''){
                    $seal_1 = '005';
                }
                if($val['shipment_time'] != null || $val['shipment_time'] != ''){
                    $seal_1 = '007';
                }
                if(($val['delivery_date'] != null || $val['delivery_date'] != '') && ($val['shipment_time'] != null || $val['shipment_time'] != '')){
                    $seal_1 = '007';
                }
                if($val['money_daibiki'] != null && $val['money_daibiki'] != '' && (int)$val['money_daibiki'] != 0){
                    $seal_3 = '008';
                }
                $flag = 0;//Cờ kiểm tra nếu không có product_status_id thì 
                if(!empty($list_product_statuses)){
                    foreach($list_product_statuses as $prod_status){//Kiểm tra nếu có trong bảng product_statuses thì thay đổi giá trị product_status
                        if(intval($prod_status->product_id) == intval($val['product_id'])){
                            $flag++;
                            if($prod_status->product_status_id == 6 || $prod_status->product_status_id == "6"){
                                $prod_status->product_status_id = '002';
                                $seal_2 = '001';
                            }else if($prod_status->product_status_id == 7 || $prod_status->product_status_id == "7"){
                                $prod_status->product_status_id = '003';
                                $seal_2 = '002';
                            }
                            $shipment = [
                                'ship_add' => mb_convert_kana(str_replace("\n", "", $val['ship_add']), 'KVRN'),
                                'ship_name' => mb_convert_kana(str_replace("\n", "", $val['ship_name']), 'KVRN'),
                                'ship_phone' => mb_convert_kana($val['ship_phone'], 'kvrn'),
                                'ship_zip' => mb_convert_kana($val['ship_zip'], 'kvrn'),
                                'buyer_tel1' => mb_convert_kana($val['buyer_tel1'], 'kvrn'),
                                'buyer_zip' => mb_convert_kana($val['buyer_zip1'].$val['buyer_zip2'], 'kvrn'),
                                'buyer_add' => mb_convert_kana(str_replace("\n", "", $val['buyer_add']), 'KVRN'),
                                'buyer_name' => mb_convert_kana(str_replace("\n", "", $val['export_buyer_name1'].$val['export_buyer_name2']), 'KVRN'),
                                'order_code' => mb_convert_kana($val['order_code'], 'kvrn'),
                                'purchase_code' => mb_convert_kana($val['purchase_code'], 'kvrn'),
                                'product_name' => $product_name_3,
                                'product_name4' => $product_name_4,
                                'product_name5' => '',
                                'productStatus' => mb_convert_kana($prod_status->product_status_id, 'kvrn'),
                                'delivery_date' => isset($val['delivery_date']) ? mb_convert_kana(date('Ymd', strtotime($val['delivery_date'])), 'kvrn') : null,
                                'shipment_time' => mb_convert_kana($val['shipment_time'], 'kvrn'),
                                'money_daibiki' => (empty($val['money_daibiki']) ? '0' : mb_convert_kana($val['money_daibiki'],'kvrn')),
                                'seal_1' => $seal_1,
                                'seal_2' => $seal_2,
                                'seal_3' => $seal_3,
                            ];
                            array_push($arr_collect, $shipment);
                            // End xuất excel giấy gửi hàng
                        }
                    }
                }
                if($flag == 0){
                    $shipment = [
                        'ship_add' => mb_convert_kana(str_replace("\n", "", $val['ship_add']), 'KVRN'),
                        'ship_name' => mb_convert_kana(str_replace("\n", "", $val['ship_name']), 'KVRN'),
                        'ship_phone' => mb_convert_kana($val['ship_phone'], 'kvrn'),
                        'ship_zip' => mb_convert_kana($val['ship_zip'], 'kvrn'),
                        'buyer_tel1' => mb_convert_kana($val['buyer_tel1'], 'kvrn'),
                        'buyer_zip' => mb_convert_kana($val['buyer_zip1'].$val['buyer_zip2'], 'kvrn'),
                        'buyer_add' => mb_convert_kana(str_replace("\n", "", $val['buyer_add']), 'KVRN'),
                        'buyer_name' => mb_convert_kana(str_replace("\n", "", $val['export_buyer_name1'].$val['export_buyer_name2']), 'KVRN'),
                        'order_code' => mb_convert_kana($val['order_code'], 'kvrn'),
                        'purchase_code' => mb_convert_kana($val['purchase_code'], 'kvrn'),
                        'product_name' => $product_name_3,
                        'product_name4' => $product_name_4,
                        'product_name5' => '',
                        'productStatus' => '001',
                        'delivery_date' => isset($val['delivery_date']) ? mb_convert_kana(date('Ymd', strtotime($val['delivery_date'])), 'kvrn') : null,
                        'shipment_time' => mb_convert_kana($val['shipment_time'], 'kvrn'),
                        'money_daibiki' => (empty($val['money_daibiki']) ? '0' : mb_convert_kana($val['money_daibiki'],'kvrn')),
                        'seal_1' => $seal_1,
                        'seal_2' => $seal_2,
                        'seal_3' => $seal_3,
                    ];
                    array_push($arr_collect, $shipment);
                    // End xuất excel giấy gửi hàng
                }
            }

            $str_process_description = rtrim($str_process_description, '、');
            // Có chọn check đổi tình trạng hỗ trợ và không phải download chi tiết đóng gói, đặt hàng thì mới cập nhật tình trạng
            if($stage3 == 3){
                $str_process_description .= '<br>発注ステータスを送り状作成済に変更する';  
                $this->ShipmentService->updateStatusAtShipment($purchase_list);
            }
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            $collection = $arr_collect;
        }else {
            $collection = $arr_collect;
        }
        $file_name = $request->get('file_name');
        $col_title = array("お届け先住所１", "お届け先名称１", "お届け先電話番号", "お届け先郵便番号", "ご依頼主電話番号", "ご依頼主郵便番号", "ご依頼主住所１", "ご依頼主名称１", "品名１", "品名２", "品名３", "品名４", "品名５", "便種（商品）", "配達日", "配達指定時間帯", "代引金額", "指定シール1", "指定シール2", "指定シール3");
        $col_value = array('ship_add', 'ship_name', 'ship_phone', 'ship_zip', 'buyer_tel1', 'buyer_zip', 'buyer_add', 'buyer_name', 'order_code', 'purchase_code', 'product_name', 'product_name4', 'product_name5', 'productStatus', 'delivery_date', 'shipment_time', 'money_daibiki', 'seal_1', 'seal_2', 'seal_3');
        $this->ExportCSV->Export($file_name, $col_title, $col_value, $collection);
    }

    /**
     * function ajax_export_shipment_bill
     * Description: Export shipment bill at screen shipment [6]
     * @author chan_nl
     * Created: 2020/05/15
     * Updated: 2020/05/15
     */
    public function ajax_export_shipment_bill(Request $request){
        $hisProcess = $this->historyProcessModel;
        $collection = null;
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        $stage1 = $request->get('stage1') != null ? $request->get('stage1') : null;//stage1: Bỏ loại cần xác nhận
        $stage2 = $request->get('stage2') != null ? $request->get('stage2') : null;//stage2: Bỏ loại đang bảo lưu
        $stage3 = $request->get('stage3') != null ? $request->get('stage3') : null;//stage3: đổi trạng thái đặt hàng thành B3.
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $str_process_description = '<b>ダウンロード</b>: 送り状番号ファイル<br>';
        if($request->has('delivery_method')){//Kiểm tra nếu là xuất bill gửi hàng màn hình shipment
            $insert_HP['process_screen'] = '送り状出力';
            $delivery_method = (int)$request->get('delivery_method');//phạm vi tìm kiếm
            $data = $this->ShipmentService->exportShipment(null, $date_from, $date_to, $delivery_method, $range, $stage1, $stage2);
            $arr_collect = [];
            $purchase_list = [];
            if(count($data) > 0){
                foreach($data as $val){    
                    $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';  
                    if(!in_array($val['purchase_id'], $purchase_list))
                    {
                        array_push($purchase_list, $val['purchase_id']);//push những order id không có trong mảng vào orders_list
                    }
                    // Xuất csv bill gửi hàng
                    $shipdate = '';
                    if(!empty($val['delivery_date'])){
                        $shipdate = str_replace('/', '', date('Y/m/d', strtotime($val['delivery_date'])));
                    }
                    $shipment = [
                        'order_code' => $val['order_code'],
                        'purchase_code' => $val['purchase_code'],
                        'shipment_code' => $val['shipment_code']."",
                        'delivery_method' => $val['delivery_method'],
                        'purchase_status' => $val['purchase_status'],
                        'delivery_date' => $shipdate
                    ];
                    array_push($arr_collect, $shipment);
                    // End xuất csv bill gửi hàng
                }
                // Có chọn check đổi tình trạng hỗ trợ thì mới cập nhật tình trạng
                $str_process_description = rtrim($str_process_description, '、');
                if($stage3 == 3){
                    $str_process_description .= '<br>発注ステータスを送り状作成済に変更する';  
                    $this->ShipmentService->updateStatusAtShipment($purchase_list);
                }
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);

                // $collection = collect($arr_collect);
                $collection = $arr_collect;
            }else {
                // $collection = collect($arr_collect);
                $collection = $arr_collect;
            }
        }else if($request->has('site_type')){//Kiểm tra nếu là xuất bill gửi hàng màn hình shipment notification
            $insert_HP['process_screen'] = '出荷通知';
            $arr_collect = [];
            $purchase_list = [];
            $data = $this->ShipmentNotification->getListSupplierBySiteType($request);
            if(count($data) > 0){
                foreach($data as $val){
                    $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';  
                    //Kiểm tra nếu order trùng thì đưa vào arr
                    if(!in_array($val['purchase_id'], $purchase_list))
                    {
                        array_push($purchase_list, $val['purchase_id']);//push những purchase id không có trong mảng vào purchase_list
                    }
                    // Xuất csv bill gửi hàng
                    $shipdate = '';
                    if(!empty($val['delivery_date'])){
                        $shipdate = str_replace('/', '', date('Y/m/d', strtotime($val['delivery_date'])));
                    }
                    $shipment = [
                        'order_code' => $val['order_code'],
                        'purchase_code' => $val['purchase_code'],
                        'shipment_code' => '="' . $val['shipment_code'] . '"',
                        'delivery_method' => $val['delivery_method'],
                        'purchase_status' => $val['purchase_status'],
                        'delivery_date' => $shipdate
                    ];
                    array_push($arr_collect, $shipment);
                }
        
                $str_process_description = rtrim($str_process_description, '、');
                //Kiểm tra nếu có check chọn đổi tình trạng hỗ trợ thành xử lý xuất hàng xong thì cập nhật tình trạng hỗ trợ
                if($request['stage3'] == 3){
                    $str_process_description .= '<br>発注ステータスを送り状作成済に変更する';  
                    $this->ShipmentNotification->updateStatusAtShipmentNotification($purchase_list);
                }
                //End kiểm tra nếu có check chọn đổi tình trạng hỗ trợ thành xử lý xuất hàng xong thì cập nhật tình trạng hỗ trợ
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
                
                // $collection = collect($arr_collect);
                $collection = $arr_collect;
            }else {
                // $collection = collect($arr_collect);
                $collection = $arr_collect;
            }
        }
        $col_title = array("受注ID", "発注ID","送り状番号", "配送方法", "発注ステータス", "出荷日");
        $col_value = array('order_code', 'purchase_code', 'shipment_code', 'delivery_method', 'purchase_status', 'delivery_date');
        $this->ExportCSV->Export($request->get('file_name'), $col_title, $col_value, $collection);
        // return Excel::download(new ShipmentBillExport($collection), $request->get('file_name').'.csv');
    }

    /**
     * function đánh số bill gửi hàng
     * @author Dat
     * 2019/12/10
     */
    public function ajax_get_shipment_code(Request $request)
    {
        $times = 0;
        $times = $request->input('times');
        $shipCode = $this->ShipmentService->getBillNumber($times);
        return [
            'ship_code' => $shipCode
        ];
    }

    /**
     * function ajax_get_list_supplier
     * Description: Hàm lấy danh sách nhà cung cấp để xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng ở màn hình 6
     * @param:
     * range: điều kiện tìm kiếm theo ngày
     * date_from: từ ngày
     * date_to: đến ngày
     * delivery_method: phương thức giao hàng
     * @author channl
     * Created: 2019/12/10
     * Updated: 2019/12/10
     */
    public function ajax_get_list_supplier(Request $request){
        $result = [];
        $data = $this->ShipmentService->getListSupplierByDeliveryMethod($request);
        if(count($data) > 0){
            $result = [
                'status' => true,
                'data' => $data
            ];
        }else {            
            $result = [
                'status' => false,
            ];
        }
        return Response::json($result);
    }
    /**
     * function download giấy chỉ dẫn đóng gói xuất hàng sagawa
     * @author Dat
     * 20191223
     */
    public function ajax_export_sagawa_shipment(Request $request)
    {  
        $hisProcess = $this->historyProcessModel;
        $date = Carbon::now();
        $dateMonthYear = $date->format('Ymd');
        $file_name = "sagawa_system_".$dateMonthYear.".txt";
        $file_name = mb_convert_encoding($file_name, 'SJIS-win');
        $headers = [
            'Content-type' => 'text/csv', 
            'Content-Disposition' => sprintf('attachment; filename="%s"', $file_name),
        ];
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        if($request['screen'] == 6){
            $insert_HP['process_screen'] = '送り状出力';
        }else if($request['screen'] == 3){
            $insert_HP['process_screen'] = '注文検索';
        }
        $str_process_description = '<b>ダウンロード</b>: 佐川用送り状データ<br>';
        $array_list_details = [];
        if(!empty($request->input('list_details')))
        {
            $string_list_details = trim($request->input('list_details'), ',');
            $array_list_details = explode(',', $string_list_details);    
        }
        
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        $delivery_method = (int)$request->get('delivery_method');//phạm vi tìm kiếm
        $stage1 = $request->get('stage1') != null ? $request->get('stage1') : null;//stage: bỏ loại đang bảo lưu
        $stage2 = $request->get('stage2') != null ? $request->get('stage2') : null;//stage: bỏ loại xuất hàng của công ty
        $stage3 = $request->get('stage3') != null ? $request->get('stage3') : null;//stage: đổi tình trạng hỗ trợ thành đang xử lý xuất hàng.

        $data = $this->ShipmentService->exportShipment($array_list_details, $date_from, $date_to, $delivery_method, $range, $stage1, $stage2);
        $contents = '';
        $purchase_list = [];
        foreach($data as $value)
        {     
            $str_process_description .= '受注ID: '.$value['order_code'].'('.$value['purchase_code'].')、';       
            $list_product_statuses = $this->ShipmentService->getListProductStatus([$value['product_id']]);
            if(empty($list_product_statuses)){
                //Kiểm tra nếu order trùng thì đưa vào arr
                if(!in_array($value['purchase_id'], $purchase_list))
                {
                    array_push($purchase_list, $value['purchase_id']);//push những order id không có trong mảng vào orders_list
                }                
                // End kiểm tra nếu order trùng thì đưa vào arr
                //Trường 1: mã shipment
                $value->shipment_code = str_replace('-', '', $value->shipment_code);
                if(mb_strlen($value->shipment_code) < 12){
                    $ship_code = mb_convert_kana($value->shipment_code, 'kvrn');//Chuyển về kiểu 1 bytes
                    $str = '';
                    for($i = 0; $i < (12 - mb_strlen($ship_code)); $i++){
                        $str .= ' ';
                    }
                    $contents .= $ship_code.$str;
                }else {                
                    $value->shipment_code = mb_convert_kana($value->shipment_code, 'kvrn');//Chuyển về kiểu 1 bytes
                    $contents .= mb_substr($value->shipment_code, 0, 12);
                }
                //Trường 2 - 8
                $contents .= '11201010               ';
                //Trường 9
                if($value->shipment_date != null){
                    $contents .= str_replace('-', '', date('Y-m-d', strtotime($value->shipment_date)));
                }else {
                    $contents .= '00000000';
                }
                //Trường 10
                if($value->shipment_time != null && $value->shipment_time != "0"){
                    $shipment_time = explode("～", $value->shipment_time);
                    if(count($shipment_time) == 2){
                        $contents .= mb_substr($shipment_time[0], 0, 2).'00';//mb_substr($shipment_time[1], 0, 2);
                    }
                    if(count($shipment_time) == 1){
                        if(mb_strlen($shipment_time[0]) == 3){
                            $contents .= '0900';
                        }else {
                            $contents .= '1900';
                        }
                    }
                }else {
                    $contents .= '0000';
                }
                //Trường 11 - 14
                $contents .= '3690001';
                //Trường 15
                $contents .= "00000000000000000000000000000000";
                //Trường 16
                if($value->money_daibiki != null){
                    if($value->money_daibiki < 10){
                        $contents .= '0000000'.$value->money_daibiki;
                    }else if($value->money_daibiki < 100){
                        $contents .= '000000'.$value->money_daibiki;
                    }else if($value->money_daibiki < 1000){
                        $contents .= '00000'.$value->money_daibiki;
                    }else if($value->money_daibiki < 10000){
                        $contents .= '0000'.$value->money_daibiki;
                    }else if($value->money_daibiki < 100000){
                        $contents .= '000'.$value->money_daibiki;
                    }else if($value->money_daibiki < 1000000){
                        $contents .= '00'.$value->money_daibiki;
                    }else if($value->money_daibiki < 10000000){
                        $contents .= '0'.$value->money_daibiki;
                    }else if($value->money_daibiki < 100000000){
                        $contents .= ''.$value->money_daibiki;
                    }else {      
                        $money_daibiki = ''.$value->money_daibiki;
                        $contents .= mb_substr($money_daibiki, 0, 8);
                    }
                }else {
                    $contents .= '00000000';
                }
                //Trường 17
                $contents .= '00000000';
                //Trường 18 - 19
                if($value->export_es_ship_time != null){
                    $es_ship_time = explode("-", $value->export_es_ship_time);
                    if(count($es_ship_time) == 2){
                        if(is_numeric($es_ship_time[0]) && is_numeric($es_ship_time[1])){
                            if(strlen($es_ship_time[0]) == 2){
                                $contents .= $es_ship_time[0];
                            }else{
                                $contents .= '0'.$es_ship_time[0];
                            }
                            if(strlen($es_ship_time[1]) == 2){
                                $contents .= $es_ship_time[1];
                            }else{
                                $contents .= '0'.$es_ship_time[1];
                            }
                        }else {
                            $contents .= '0000';
                        }
                    }else {
                        $contents .= '0000';
                    }
                }else {
                    $contents .= '0000';
                }
                //Trường 20
                if($value->export_es_ship_date != null){
                    $contents .= str_replace('-', '', date('Y-m-d', strtotime($value->export_es_ship_date)));
                }else {
                    $contents .= '00000000';
                }
                //Trường 21 - 36
                $contents .= '100000000000000000002247456900000011000000000 0';
                //Trường 37 - 38
                if($value->supplier_code_sagawa != ''){
                    $code_sagawa = explode('-', $value->supplier_code_sagawa);
                    if(count($code_sagawa) == 2){
                        if(mb_strlen($code_sagawa[0]) < 8){
                            $str = '';
                            for($i = 0; $i < (8 - mb_strlen($code_sagawa[0])); $i++){
                                $str .= '-';
                            }
                            $contents .= $code_sagawa[0].$str;
                        }else {
                            $contents .= mb_substr($code_sagawa[0], 0, 8);
                        }
                        if(mb_strlen($code_sagawa[1]) < 3){
                            $str = '';
                            for($i = 0; $i < (3 - mb_strlen($code_sagawa[1])); $i++){
                                $str .= '-';
                            }
                            $contents .= $code_sagawa[1].$str;
                        }else {
                            $contents .= mb_substr($code_sagawa[1], 0, 3);
                        }
                    }else {
                        $contents .= '00000000000';                    
                    }
                }else {
                    $contents .= '00000000000';
                }
                //Trường 39
                if($value->export_supplier_name != null){
                    $value->export_supplier_name = mb_convert_kana($value->export_supplier_name, 'KVRN');
                    $str_buyer_name1 = self::convertString($value->export_supplier_name);
                    $str_buyer_name1 = str_replace("\n", "", $str_buyer_name1);
                    if(mb_strlen($str_buyer_name1) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_name1)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_name1.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_name1, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 40
                $contents .= '　　　　　　　　　　　　　　　　';
                //Trường 41
                $contents .= '　　　　　　　　　　　　　　　　';
                //Trường 42           
                if($value->export_add_01 != null){     
                    $value->export_add_01 = mb_convert_kana($value->export_add_01, 'KVRN');
                    $str_buyer_add1 = self::convertString($value->export_add_01);   
                    $str_buyer_add1 = str_replace("\n", "", $str_buyer_add1);
                    if(mb_strlen($str_buyer_add1) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_add1)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_add1.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_add1, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 43    
                if($value->export_add_02 != null){     
                    $value->export_add_02 = mb_convert_kana($value->export_add_02, 'KVRN');
                    $str_buyer_add2 = self::convertString($value->export_add_02);   
                    $str_buyer_add2 = str_replace("\n", "", $str_buyer_add2);  
                    if(mb_strlen($str_buyer_add2) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_add2)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_add2.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_add2, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 44
                if($value->export_add_03 != null){
                    $value->export_add_03 = mb_convert_kana($value->export_add_03, 'KVRN');
                    $str_buyer_add3 = self::convertString($value->export_add_03);  
                    $str_buyer_add3 = str_replace("\n", "", $str_buyer_add3);  
                    if(mb_strlen($str_buyer_add3) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_add3)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_add3.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_add3, 0, 16);
                    }
                }else {                
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 45
                if($value->sup_tel01 == '' && $value->sup_tel02 == '' && $value->sup_tel03 == ''){
                    $contents .= '               ';
                }else {
                    $sup_tel = $value->sup_tel01.'-'.$value->sup_tel02.'-'.$value->sup_tel03;
                    if(mb_strlen($sup_tel) < 16){
                        $str = '';
                        for($i = 0; $i < (15 - mb_strlen($sup_tel)); $i++){
                            $str .= ' ';
                        }
                        $contents .= $sup_tel.$str;
                    }else {
                        $contents .= mb_substr($sup_tel, 0, 15);
                    }
                }
                //Trường 46 - 51
                $contents .= '0000000000000000000000000';
                //Trường 52
                if($value->ship_name != null){
                    $value->ship_name = mb_convert_kana($value->ship_name, 'KVRN');
                    $str_ship_name = self::convertString($value->ship_name);  
                    $str_ship_name = str_replace("\n", "", $str_ship_name);  
                    if(mb_strlen($str_ship_name) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_ship_name)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_ship_name.$str;
                    }else {
                        $contents .= mb_substr($str_ship_name, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 53
                if($value->ship_name2 != null){
                    $value->ship_name2 = mb_convert_kana($value->ship_name2, 'KVRN');
                    $str_ship_name2 = self::convertString($value->ship_name2);  
                    $str_ship_name2 = str_replace("\n", "", $str_ship_name2);  
                    if(mb_strlen($str_ship_name2) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_ship_name2)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_ship_name2.$str;
                    }else {
                        $contents .= mb_substr($str_ship_name2, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 54
                $contents .= '　　　　　　　　　　　　　　　　';
                //Trường 55
                if($value->export_ship_add1 != null){
                    $value->export_ship_add1 = mb_convert_kana($value->export_ship_add1, 'KVRN');
                    $str_ship_add1 = self::convertString($value->export_ship_add1);  
                    $str_ship_add1 = str_replace("\n", "", $str_ship_add1);  
                    if(mb_strlen($str_ship_add1) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_ship_add1)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_ship_add1.$str;
                    }else {
                        $contents .= mb_substr($str_ship_add1, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 56
                if($value->export_ship_add2 != null){
                    $value->export_ship_add2 = mb_convert_kana($value->export_ship_add2, 'KVRN');
                    $str_ship_add2 = self::convertString($value->export_ship_add2);  
                    $str_ship_add2 = str_replace("\n", "", $str_ship_add2);  
                    if(mb_strlen($str_ship_add2) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_ship_add2)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_ship_add2.$str;
                    }else {
                        $contents .= mb_substr($str_ship_add2, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 57
                if($value->export_ship_add3 != null){
                    $value->export_ship_add3 = mb_convert_kana($value->export_ship_add3, 'KVRN');
                    $str_ship_add3 = self::convertString($value->export_ship_add3);  
                    $str_ship_add3 = str_replace("\n", "", $str_ship_add3);  
                    if(mb_strlen($str_ship_add3) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_ship_add3)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_ship_add3.$str;
                    }else {
                        $contents .= mb_substr($str_ship_add3, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 58
                if($value->shipment_phone != null){
                    $length = mb_strlen($value->shipment_phone);
                    $str_phone = $value->shipment_phone;
                    if($length >= 15){
                        $str_phone = mb_substr($value->shipment_phone, 0, 15);
                    }else {
                        for($i = 0; $i < (15 - $length); $i++){
                            $str_phone .= " ";
                        }                    
                    }
                    $contents .= $str_phone;
                }else {
                    $contents .= '000000000000000';
                }
                //Trường 59 - 64
                $contents .= '0    0000000000000001';
                //Trường 65 - 66
                if($value->product_name != null){
                    $value->product_name = mb_convert_kana($value->product_name, 'KVRN');
                    $str_product_name = self::convertString($value->product_name);
                    if(mb_strlen($str_product_name) < 17){
                        $str = '';
                        for($i = 0; $i < (17 - mb_strlen($str_product_name)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_product_name.$str;
                    }else {
                        $contents .= mb_substr($str_product_name, 0, 17);
                    }
                    if(mb_strlen($str_product_name) > 17){
                        if(mb_strlen($str_product_name) >= 34){
                            $contents .= mb_substr($str_product_name, 17, 17);
                        }else {
                            $str = '';
                            for($i = 0; $i < (34 - mb_strlen($str_product_name)); $i++){
                                $str .= '　';
                            }
                            $contents .= mb_substr($str_product_name, 17, 17).$str;
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　　';                    
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                }
                //Trường 66
                // $contents .= '　　　　　　　　　　　　　　　　　';
                //Trường 67 - 69
                $contents .= '　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                //Trường 70 - 72
                $contents .= 'ひろしまグルメショップ　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                //Trường 73 - 75
                $contents .= '広島県　　　　　　　　　　　　　広島市西区草津港１丁目８－１　　広島市中央卸売市場関連棟２３８番';
                //Trường 76 - 78
                $supplier_zip = $value->zip01.$value->zip02;
                if(strlen($supplier_zip) >= 7){
                    $supplier_zip = mb_substr($supplier_zip, 0, 7);
                }else {
                    $str_zip = '';
                    for($i = 0; $i < (7 - strlen($supplier_zip)); $i++){
                        $str_zip .= ' ';
                    }
                    $supplier_zip = $supplier_zip.$str_zip;
                }
                $contents .= '082-276-7500   082-276-7500   '.$supplier_zip;
                //Trường 79 - 80
                $contents .= '             ';
                //Trường 81            
                if($value->export_buyer_name1 != null){  
                    $value->export_buyer_name1 = mb_convert_kana($value->export_buyer_name1, 'KVRN');
                    $str_buyer_name1 = self::convertString($value->export_buyer_name1);      
                    $str_buyer_name1 = str_replace("\n", "", $str_buyer_name1);  
                    if(mb_strlen($str_buyer_name1) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_name1)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_name1.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_name1, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 82
                if($value->export_buyer_name2 != null){      
                    $value->export_buyer_name2 = mb_convert_kana($value->export_buyer_name2, 'KVRN');
                    $str_buyer_name2 = self::convertString($value->export_buyer_name2);     
                    $str_buyer_name2 = str_replace("\n", "", $str_buyer_name2);  
                    if(mb_strlen($str_buyer_name2) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_name2)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_name2.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_name2, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 83
                $contents .= '　　　　　　　　　　　　　　　　';
                //Trường 84 - 86
                if($value->export_buyer_address_1 != null){    
                    $value->export_buyer_address_1 = mb_convert_kana($value->export_buyer_address_1, 'KVRN');
                    $str_buyer_address_1 = self::convertString($value->export_buyer_address_1);    
                    $str_buyer_address_1 = str_replace("\n", "", $str_buyer_address_1);  
                    if(mb_strlen($str_buyer_address_1) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_address_1)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_address_1.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_address_1, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                if($value->export_buyer_address_2 != null){      
                    $value->export_buyer_address_2 = mb_convert_kana($value->export_buyer_address_2, 'KVRN');
                    $str_buyer_address_2 = self::convertString($value->export_buyer_address_2);    
                    $str_buyer_address_2 = str_replace("\n", "", $str_buyer_address_2);  
                    if(mb_strlen($str_buyer_address_2) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_address_2)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_address_2.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_address_2, 0, 16);
                    }
                }else {
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                if($value->export_buyer_address_3 != null){
                    $value->export_buyer_address_3 = mb_convert_kana($value->export_buyer_address_3, 'KVRN');
                    $str_buyer_address_3 = self::convertString($value->export_buyer_address_3);
                    $str_buyer_address_3 = str_replace("\n", "", $str_buyer_address_3);  
                    if(mb_strlen($str_buyer_address_3) < 16){
                        $str = '';
                        for($i = 0; $i < (16 - mb_strlen($str_buyer_address_3)); $i++){
                            $str .= '　';
                        }
                        $contents .= $str_buyer_address_3.$str;
                    }else {
                        $contents .= mb_substr($str_buyer_address_3, 0, 16);
                    }
                }else {                
                    $contents .= '　　　　　　　　　　　　　　　　';
                }
                //Trường 87            
                $buyer_tel = $value->buyer_tel1.$value->buyer_tel2.$value->buyer_tel3;
                if($buyer_tel != ''){
                    if(mb_strlen($buyer_tel) < 16){
                        $str = '';
                        for($i = 0; $i < (15 - mb_strlen($buyer_tel)); $i++){
                            $str .= ' ';
                        }
                        $contents .= $buyer_tel.$str;
                    }else {
                        $contents .= mb_substr($buyer_tel, 0, 15);
                    }
                }else {
                    $contents .= '               ';
                }
                //Trường 88
                if($value->ship_zip != null){
                    $contents .= str_replace('-', '', $value->ship_zip);
                }else {
                    $contents .= '0000000';
                }
                //Trường 89
                $contents .= '             000000000';
                $contents .= "\r\n";
            }else {
                foreach($list_product_statuses as $pro_status){
                    if($pro_status->product_status_id == 6){
                        $pro_status->product_status_id = '7';
                    }else {
                        $pro_status->product_status_id = 'A';
                    }
                    //Kiểm tra nếu order trùng thì đưa vào arr
                    if(!in_array($value['purchase_id'], $purchase_list))
                    {
                        array_push($purchase_list, $value['purchase_id']);//push những order id không có trong mảng vào orders_list
                    }                
                    // End kiểm tra nếu order trùng thì đưa vào arr
                    //Trường 1: mã shipment
                    $value->shipment_code = str_replace('-', '', $value->shipment_code);
                    if(mb_strlen($value->shipment_code) < 12){
                        $ship_code = mb_convert_kana($value->shipment_code, 'kvrn');//Chuyển về kiểu 1 bytes
                        $str = '';
                        for($i = 0; $i < (12 - mb_strlen($ship_code)); $i++){
                            $str .= ' ';
                        }
                        $contents .= $ship_code.$str;
                    }else {                
                        $value->shipment_code = mb_convert_kana($value->shipment_code, 'kvrn');//Chuyển về kiểu 1 bytes
                        $contents .= mb_substr($value->shipment_code, 0, 12);
                    }
                    //Trường 2 - 8
                    $contents .= "1120101$pro_status->product_status_id               ";
                    //Trường 9
                    if($value->shipment_date != null){
                        $contents .= str_replace('-', '', date('Y-m-d', strtotime($value->shipment_date)));
                    }else {
                        $contents .= '00000000';
                    }
                    //Trường 10
                    if($value->shipment_time != null && $value->shipment_time != "0"){
                        $shipment_time = explode("～", $value->shipment_time);
                        if(count($shipment_time) == 2){
                            $contents .= mb_substr($shipment_time[0], 0, 2).'00';//mb_substr($shipment_time[1], 0, 2);
                        }
                        if(count($shipment_time) == 1){
                            if(mb_strlen($shipment_time[0]) == 3){
                                $contents .= '0900';
                            }else {
                                $contents .= '1900';
                            }
                        }
                    }else {
                        $contents .= '0000';
                    }
                    //Trường 11 - 14
                    $contents .= '3690001';
                    //Trường 15
                    $contents .= "00000000000000000000000000000000";
                    //Trường 16
                    if($value->money_daibiki != null){
                        if($value->money_daibiki < 10){
                            $contents .= '0000000'.$value->money_daibiki;
                        }else if($value->money_daibiki < 100){
                            $contents .= '000000'.$value->money_daibiki;
                        }else if($value->money_daibiki < 1000){
                            $contents .= '00000'.$value->money_daibiki;
                        }else if($value->money_daibiki < 10000){
                            $contents .= '0000'.$value->money_daibiki;
                        }else if($value->money_daibiki < 100000){
                            $contents .= '000'.$value->money_daibiki;
                        }else if($value->money_daibiki < 1000000){
                            $contents .= '00'.$value->money_daibiki;
                        }else if($value->money_daibiki < 10000000){
                            $contents .= '0'.$value->money_daibiki;
                        }else if($value->money_daibiki < 100000000){
                            $contents .= ''.$value->money_daibiki;
                        }else {      
                            $money_daibiki = ''.$value->money_daibiki;
                            $contents .= mb_substr($money_daibiki, 0, 8);
                        }
                    }else {
                        $contents .= '00000000';
                    }
                    //Trường 17
                    $contents .= '00000000';
                    //Trường 18 - 19
                    if($value->export_es_ship_time != null){
                        $es_ship_time = explode("-", $value->export_es_ship_time);
                        if(count($es_ship_time) == 2){
                            if(is_numeric($es_ship_time[0]) && is_numeric($es_ship_time[1])){
                                if(strlen($es_ship_time[0]) == 2){
                                    $contents .= $es_ship_time[0];
                                }else{
                                    $contents .= '0'.$es_ship_time[0];
                                }
                                if(strlen($es_ship_time[1]) == 2){
                                    $contents .= $es_ship_time[1];
                                }else{
                                    $contents .= '0'.$es_ship_time[1];
                                }
                            }else {
                                $contents .= '0000';
                            }
                        }else {
                            $contents .= '0000';
                        }
                    }else {
                        $contents .= '0000';
                    }
                    //Trường 20
                    if($value->export_es_ship_date != null){
                        $contents .= str_replace('-', '', date('Y-m-d', strtotime($value->export_es_ship_date)));
                    }else {
                        $contents .= '00000000';
                    }
                    //Trường 21 - 36
                    $contents .= '100000000000000000002247456900000011000000000 0';
                    //Trường 37 - 38
                    if($value->supplier_code_sagawa != ''){
                        $code_sagawa = explode('-', $value->supplier_code_sagawa);
                        if(count($code_sagawa) == 2){
                            if(mb_strlen($code_sagawa[0]) < 8){
                                $str = '';
                                for($i = 0; $i < (8 - mb_strlen($code_sagawa[0])); $i++){
                                    $str .= '-';
                                }
                                $contents .= $code_sagawa[0].$str;
                            }else {
                                $contents .= mb_substr($code_sagawa[0], 0, 8);
                            }
                            if(mb_strlen($code_sagawa[1]) < 3){
                                $str = '';
                                for($i = 0; $i < (3 - mb_strlen($code_sagawa[1])); $i++){
                                    $str .= '-';
                                }
                                $contents .= $code_sagawa[1].$str;
                            }else {
                                $contents .= mb_substr($code_sagawa[1], 0, 3);
                            }
                        }else {
                            $contents .= '00000000000';                    
                        }
                    }else {
                        $contents .= '00000000000';
                    }
                    //Trường 39
                    if($value->export_supplier_name != null){
                        $value->export_supplier_name = mb_convert_kana($value->export_supplier_name, 'KVRN');
                        $str_buyer_name1 = self::convertString($value->export_supplier_name);
                        $str_buyer_name1 = str_replace("\n", "", $str_buyer_name1);
                        if(mb_strlen($str_buyer_name1) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_name1)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_name1.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_name1, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 40
                    $contents .= '　　　　　　　　　　　　　　　　';
                    //Trường 41
                    $contents .= '　　　　　　　　　　　　　　　　';
                    //Trường 42           
                    if($value->export_add_01 != null){     
                        $value->export_add_01 = mb_convert_kana($value->export_add_01, 'KVRN');
                        $str_buyer_add1 = self::convertString($value->export_add_01);   
                        $str_buyer_add1 = str_replace("\n", "", $str_buyer_add1);
                        if(mb_strlen($str_buyer_add1) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_add1)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_add1.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_add1, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 43    
                    if($value->export_add_02 != null){     
                        $value->export_add_02 = mb_convert_kana($value->export_add_02, 'KVRN');
                        $str_buyer_add2 = self::convertString($value->export_add_02);   
                        $str_buyer_add2 = str_replace("\n", "", $str_buyer_add2);  
                        if(mb_strlen($str_buyer_add2) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_add2)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_add2.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_add2, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 44
                    if($value->export_add_03 != null){
                        $value->export_add_03 = mb_convert_kana($value->export_add_03, 'KVRN');
                        $str_buyer_add3 = self::convertString($value->export_add_03);  
                        $str_buyer_add3 = str_replace("\n", "", $str_buyer_add3);  
                        if(mb_strlen($str_buyer_add3) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_add3)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_add3.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_add3, 0, 16);
                        }
                    }else {                
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 45
                    if($value->sup_tel01 == '' && $value->sup_tel02 == '' && $value->sup_tel03 == ''){
                        $contents .= '               ';
                    }else {
                        $sup_tel = $value->sup_tel01.'-'.$value->sup_tel02.'-'.$value->sup_tel03;
                        if(mb_strlen($sup_tel) < 16){
                            $str = '';
                            for($i = 0; $i < (15 - mb_strlen($sup_tel)); $i++){
                                $str .= ' ';
                            }
                            $contents .= $sup_tel.$str;
                        }else {
                            $contents .= mb_substr($sup_tel, 0, 15);
                        }
                    }
                    //Trường 46 - 51
                    $contents .= '0000000000000000000000000';
                    //Trường 52
                    if($value->ship_name != null){
                        $value->ship_name = mb_convert_kana($value->ship_name, 'KVRN');
                        $str_ship_name = self::convertString($value->ship_name);  
                        $str_ship_name = str_replace("\n", "", $str_ship_name);  
                        if(mb_strlen($str_ship_name) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_ship_name)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_ship_name.$str;
                        }else {
                            $contents .= mb_substr($str_ship_name, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 53
                    if($value->ship_name2 != null){
                        $value->ship_name2 = mb_convert_kana($value->ship_name2, 'KVRN');
                        $str_ship_name2 = self::convertString($value->ship_name2);  
                        $str_ship_name2 = str_replace("\n", "", $str_ship_name2);  
                        if(mb_strlen($str_ship_name2) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_ship_name2)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_ship_name2.$str;
                        }else {
                            $contents .= mb_substr($str_ship_name2, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 54
                    $contents .= '　　　　　　　　　　　　　　　　';
                    //Trường 55
                    if($value->export_ship_add1 != null){
                        $value->export_ship_add1 = mb_convert_kana($value->export_ship_add1, 'KVRN');
                        $str_ship_add1 = self::convertString($value->export_ship_add1);  
                        $str_ship_add1 = str_replace("\n", "", $str_ship_add1);  
                        if(mb_strlen($str_ship_add1) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_ship_add1)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_ship_add1.$str;
                        }else {
                            $contents .= mb_substr($str_ship_add1, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 56
                    if($value->export_ship_add2 != null){
                        $value->export_ship_add2 = mb_convert_kana($value->export_ship_add2, 'KVRN');
                        $str_ship_add2 = self::convertString($value->export_ship_add2);  
                        $str_ship_add2 = str_replace("\n", "", $str_ship_add2);  
                        if(mb_strlen($str_ship_add2) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_ship_add2)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_ship_add2.$str;
                        }else {
                            $contents .= mb_substr($str_ship_add2, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 57
                    if($value->export_ship_add3 != null){
                        $value->export_ship_add3 = mb_convert_kana($value->export_ship_add3, 'KVRN');
                        $str_ship_add3 = self::convertString($value->export_ship_add3);  
                        $str_ship_add3 = str_replace("\n", "", $str_ship_add3);  
                        if(mb_strlen($str_ship_add3) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_ship_add3)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_ship_add3.$str;
                        }else {
                            $contents .= mb_substr($str_ship_add3, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 58
                    if($value->shipment_phone != null){
                        $length = mb_strlen($value->shipment_phone);
                        $str_phone = $value->shipment_phone;
                        if($length >= 15){
                            $str_phone = mb_substr($value->shipment_phone, 0, 15);
                        }else {
                            for($i = 0; $i < (15 - $length); $i++){
                                $str_phone .= " ";
                            }                    
                        }
                        $contents .= $str_phone;
                    }else {
                        $contents .= '000000000000000';
                    }
                    //Trường 59 - 64
                    $contents .= '0    0000000000000001';
                    //Trường 65 - 66
                    if($value->product_name != null){
                        $value->product_name = mb_convert_kana($value->product_name, 'KVRN');
                        $str_product_name = self::convertString($value->product_name);
                        if(mb_strlen($str_product_name) < 17){
                            $str = '';
                            for($i = 0; $i < (17 - mb_strlen($str_product_name)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_product_name.$str;
                        }else {
                            $contents .= mb_substr($str_product_name, 0, 17);
                        }
                        if(mb_strlen($str_product_name) > 17){
                            if(mb_strlen($str_product_name) >= 34){
                                $contents .= mb_substr($str_product_name, 17, 17);
                            }else {
                                $str = '';
                                for($i = 0; $i < (34 - mb_strlen($str_product_name)); $i++){
                                    $str .= '　';
                                }
                                $contents .= mb_substr($str_product_name, 17, 17).$str;
                            }
                        }else {
                            $contents .= '　　　　　　　　　　　　　　　　　';                    
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                    }
                    //Trường 66
                    // $contents .= '　　　　　　　　　　　　　　　　　';
                    //Trường 67 - 69
                    $contents .= '　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                    //Trường 70 - 72
                    $contents .= 'ひろしまグルメショップ　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　';
                    //Trường 73 - 75
                    $contents .= '広島県　　　　　　　　　　　　　広島市西区草津港１丁目８－１　　広島市中央卸売市場関連棟２３８番';
                    //Trường 76 - 78
                    $supplier_zip = $value->zip01.$value->zip02;
                    if(strlen($supplier_zip) >= 7){
                        $supplier_zip = mb_substr($supplier_zip, 0, 7);
                    }else {
                        $str_zip = '';
                        for($i = 0; $i < (7 - strlen($supplier_zip)); $i++){
                            $str_zip .= ' ';
                        }
                        $supplier_zip = $supplier_zip.$str_zip;
                    }
                    $contents .= '082-276-7500   082-276-7500   '.$supplier_zip;
                    //Trường 79 - 80
                    $contents .= '             ';
                    //Trường 81            
                    if($value->export_buyer_name1 != null){  
                        $value->export_buyer_name1 = mb_convert_kana($value->export_buyer_name1, 'KVRN');
                        $str_buyer_name1 = self::convertString($value->export_buyer_name1);      
                        $str_buyer_name1 = str_replace("\n", "", $str_buyer_name1);  
                        if(mb_strlen($str_buyer_name1) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_name1)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_name1.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_name1, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 82
                    if($value->export_buyer_name2 != null){      
                        $value->export_buyer_name2 = mb_convert_kana($value->export_buyer_name2, 'KVRN');
                        $str_buyer_name2 = self::convertString($value->export_buyer_name2);     
                        $str_buyer_name2 = str_replace("\n", "", $str_buyer_name2);  
                        if(mb_strlen($str_buyer_name2) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_name2)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_name2.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_name2, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 83
                    $contents .= '　　　　　　　　　　　　　　　　';
                    //Trường 84 - 86
                    if($value->export_buyer_address_1 != null){    
                        $value->export_buyer_address_1 = mb_convert_kana($value->export_buyer_address_1, 'KVRN');
                        $str_buyer_address_1 = self::convertString($value->export_buyer_address_1);    
                        $str_buyer_address_1 = str_replace("\n", "", $str_buyer_address_1);  
                        if(mb_strlen($str_buyer_address_1) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_address_1)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_address_1.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_address_1, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    if($value->export_buyer_address_2 != null){      
                        $value->export_buyer_address_2 = mb_convert_kana($value->export_buyer_address_2, 'KVRN');
                        $str_buyer_address_2 = self::convertString($value->export_buyer_address_2);    
                        $str_buyer_address_2 = str_replace("\n", "", $str_buyer_address_2);  
                        if(mb_strlen($str_buyer_address_2) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_address_2)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_address_2.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_address_2, 0, 16);
                        }
                    }else {
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    if($value->export_buyer_address_3 != null){
                        $value->export_buyer_address_3 = mb_convert_kana($value->export_buyer_address_3, 'KVRN');
                        $str_buyer_address_3 = self::convertString($value->export_buyer_address_3);
                        $str_buyer_address_3 = str_replace("\n", "", $str_buyer_address_3);  
                        if(mb_strlen($str_buyer_address_3) < 16){
                            $str = '';
                            for($i = 0; $i < (16 - mb_strlen($str_buyer_address_3)); $i++){
                                $str .= '　';
                            }
                            $contents .= $str_buyer_address_3.$str;
                        }else {
                            $contents .= mb_substr($str_buyer_address_3, 0, 16);
                        }
                    }else {                
                        $contents .= '　　　　　　　　　　　　　　　　';
                    }
                    //Trường 87            
                    $buyer_tel = $value->buyer_tel1.$value->buyer_tel2.$value->buyer_tel3;
                    if($buyer_tel != ''){
                        if(mb_strlen($buyer_tel) < 16){
                            $str = '';
                            for($i = 0; $i < (15 - mb_strlen($buyer_tel)); $i++){
                                $str .= ' ';
                            }
                            $contents .= $buyer_tel.$str;
                        }else {
                            $contents .= mb_substr($buyer_tel, 0, 15);
                        }
                    }else {
                        $contents .= '               ';
                    }
                    //Trường 88
                    if($value->ship_zip != null){
                        $contents .= str_replace('-', '', $value->ship_zip);
                    }else {
                        $contents .= '0000000';
                    }
                    //Trường 89
                    $contents .= '             000000000';
                    $contents .= "\r\n";
                }
            }
        }
        $contents = rtrim($contents, "\r\n");
        $str_process_description = rtrim($str_process_description, '、');
        if($stage3 == 3 && count($purchase_list) > 0){
            $str_process_description .= '<br>発注ステータスを送り状作成済に変更する';
            $this->ShipmentService->updateStatusAtShipment($purchase_list);
        }
        $insert_HP['process_description'] = $str_process_description;
        if(count($data) > 0){
            $hisProcess->create($insert_HP);
        }
        return Response::make(mb_convert_encoding($contents, 'SJIS-win'), 200, $headers);
    }
    //Convert kí tự đặc biệt 1 bytes thành 2 bytes
    public function convertString($string){
        $arr_char = [
            '~', '^', '@', '/', '', ':', ';', '*', '_', "'", '"', "|", '!',
            '?', '$', "\\", '&', '#', '%', '(', ')', '}', '{', '[', ']',
            '<', '>', '.', ',', '+', '-', '=', 'f', 'i', 'j', 'l', ' '
        ];
        $arr_char_convert = [
            "～", "＾", "＠", "／", "＼", "：", "；", "＊", "＿", "’", "”", "｜", "！",
            "？", "＄", "￥", "＆", "＃", "％", "（", "）", "｛", "｝", "［", "］",
            "＜", "＞", "．", "，", "＋", "－", "＝", "ｆ", "ｉ", "ｊ", "ｌ", "　"
        ];
        for($i = 0; $i < count($arr_char); $i++){
            if (strpos($string, $arr_char[$i]) !== false){
                $string = str_replace($arr_char[$i], $arr_char_convert[$i], $string);
            }
        }
        return $string;
    }
}