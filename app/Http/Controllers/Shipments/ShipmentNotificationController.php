<?php

namespace App\Http\Controllers\Shipments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Repositories\Services\ShipmentNotification\ShipmentNotificationServiceContract;
use App\Exports\ShipmentNotificationExport;
use App\Exports\ExportCSV;
use App\Model\HistoryProcess\HistoryProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentNotificationController extends Controller
{
    /**
     * class home controller
     * class controll the data and show data in view
     * @author Chan
     * date 2019/10/07
     */
    private $ShipmentNotification;
    private $ExportCSV;
    protected $historyProcessModel;
    public function __construct(ShipmentNotificationServiceContract $ShipmentNotification, HistoryProcess $historyProcessModel)
    {
        $this->ExportCSV = new ExportCSV();
        $this->ShipmentNotification = $ShipmentNotification;
        $this->historyProcessModel = $historyProcessModel;
    }
    /**
     * function index
     * @author Chan
     * date 2019/10/07
     */
    public function index()
    {
        $this->data['title'] = '出荷通知';
        $this->data['active'] = 5;
        return view('shipment_notifications.index', $this->data);
    }

    /**
     * function ImportShipmentBill
     * Description: import ma bill
     * @author chan_nl
     * Created: 2020/06/01
     * Updated: 2020/06/01
     */
    public function ImportShipmentBill(Request $request){        
        $row = 0;
        $delivery_method = ["1", "2", "3", "4", "5", "6", "7", "8"];
        $purchase_status = ["1", "2", "3", "4", "5"];
        $sheetTitle = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");
        $arr_title = array("受注ID", "発注ID", "送り状番号", "配送方法", "発注ステータス", "出荷日");
        $sheetData = array();
        if($request->file('result_file') == null){
            return [
                'status' => false,
                'message' => 'csvファイルを選択してから取り込んでください。'
            ];
        }
        $name = $_FILES["result_file"]["name"];
        $tmp_link = $_FILES["result_file"]["tmp_name"];
        $file_extension = @strtolower(end(explode('.',$name)));
        $check_extension = array("csv");
        if(in_array($file_extension,$check_extension)=== false){
            return [
                'status' => false,
                'message' => 'CSVと言う形式しかのファイルが取込めません。再確認してください。'
            ];
        }
        $buffer = file_get_contents($tmp_link);
        $encode = mb_detect_encoding(str_replace(array("\0", "\xFE","\xFF"), "", $buffer), mb_list_encodings());
        if($encode == false) {
            $encode = "UTF-8";
        }
        if (($handle = fopen($tmp_link, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                if($row > 0) {
                    $arr_data = array();
                    for ($c = 0; $c < $num; $c++) {
                        if(isset($sheetTitle[$c])) {
                            $data[$c] = isset($data[$c]) ? $data[$c] : "";
                            $data[$c] = str_replace('"',"",$data[$c]);
                            $data[$c] = str_replace("'","",$data[$c]);
                            $data[$c] = str_replace("=","",$data[$c]);
                            $arr_data[$sheetTitle[$c]] = mb_convert_encoding($data[$c], "UTF-8", "$encode");
                        }
                    }
                    if(count($arr_data) > 0) {
                        array_push($sheetData, $arr_data);
                    }
                }
            }
            fclose($handle);
        }
        if(count($sheetData) > 0){
            //Kiểm tra row tiêu đề trống thì báo lỗi
            $check_title_null = false;
            foreach($sheetData[0] as $val){
                $val = str_replace("\xEF\xBB\xBF", '', $val);
                if(!in_array($val, $arr_title)){
                    $check_title_null = true;
                    break;
                }
            }
            if($check_title_null == true){
                return [
                    'status' => false,
                    'message' => 'タイトル行がありません。取込めません。再確認してください。'
                ];
            }else {
                $check_row_error = [];
                $query_shipbill = '';
                foreach($sheetData as $key => $value){
                    if($key > 0) {
                        $check_row = true;
                        if($value["A"] == "" || $value["B"] == ""){
                            $check_row = false;
                        }
                        if(isset($value["C"])){                        
                            if(preg_match('/\s/', $value["C"])){
                                $check_row = false;
                            }
                        }
                        if(isset($value["D"])){
                            if(!in_array($value["D"], $delivery_method) && $value["D"] != ""){
                                $check_row = false;
                            }
                        }
                        if(isset($value["E"])){
                            if(!in_array($value["E"],$purchase_status) && $value["E"] != ""){
                                $check_row = false;
                            }
                        }
                        if(isset($value["F"])){
                            if(mb_strlen($value["F"]) != 8){
                                $check_row = false;
                            }else {
                                $year = mb_substr($value["F"], 0, 4);
                                $month = mb_substr($value["F"], 4, 2);
                                $day = mb_substr($value["F"], 6, 2);
                                if(!checkdate($month, $day, $year)){
                                    $check_row = false;
                                }
                            }
                        }
                        if($check_row == false){//Kiểm tra row nào sai thì đưa vào list error
                            array_push($check_row_error, ($key+1));
                        }else {
                            $query_shipbill .= "(orders.order_code = '".$value["A"]."' and purchases.purchase_code = '".$value["B"]."') or ";
                        }
                    }
                }
                if(count($check_row_error) < (count($sheetData) - 1)){//Kiểm tra số lượng dòng data lỗi ít hơn tổng số data thì update
                    $query_shipbill = rtrim($query_shipbill, " or ");
                    $data = $this->ShipmentNotification->getRecordImport($query_shipbill);
                    $arr_data_update = [];
                    if(count($data) > 0){
                        foreach($sheetData as $sheetData_key => $sheetData_val){
                            $check_order_exist = false;
                            foreach($data as $data_key => $data_val){
                                if($sheetData_val["A"] == $data_val['order_code'] && $sheetData_val["B"] == $data_val['purchase_code']){
                                    $year = mb_substr($sheetData_val["F"], 0, 4);
                                    $month = mb_substr($sheetData_val["F"], 4, 2);
                                    $day = mb_substr($sheetData_val["F"], 6, 2);
                                    $data_update = [];
                                    $data_update['order_detail_id'] = $data_val['od_id'];
                                    $data_update['purchase_id'] = $data_val['pur_id'];
                                    $data_update['shipment_id'] = $data_val['ship_id'];
                                    $data_update['shipment_code'] = $sheetData_val["C"];
                                    $data_update['delivery_method'] = $sheetData_val["D"];
                                    $data_update['purchase_status'] = $sheetData_val["E"];
                                    $data_update['shipment_date'] = $year.'-'.$month.'-'.$day;
                                    array_push($arr_data_update, $data_update);
                                    $check_order_exist = true;
                                }
                            }
                            if($sheetData_key > 0 && $check_order_exist == false){//Kiểm tra order không có trong db thì báo lỗi row
                                if(!in_array(($sheetData_key + 1), $check_row_error)){
                                    array_push($check_row_error, ($sheetData_key + 1));
                                }
                            }
                        }
                        if(count($arr_data_update) > 0){
                            $update = $this->ShipmentNotification->updateRecordImport($arr_data_update);
                            if($update['status'] == true){
                                return [
                                    'status' => true,
                                    'success' => $update['message'],
                                    'list_error' => $check_row_error
                                    ];
                            }else {
                                return [
                                    'status' => false,
                                    'message' => $update['message']
                                ];
                            }
                        }
                    }else {
                        return [
                            'status' => false,
                            'message' => 'データがありません。取込めません。ファイルを再確認してください。11'
                        ];
                    }
                }else {//Số lượng dòng data lỗi bằng số lượng data thì báo lỗi
                    return [
                        'status' => false,
                        'message' => '取込めません。データを全て再確認してください。'
                    ];
                }
            }
        }else {
            return [
                'status' => false,
                'message' => 'データがありません。取込めません。ファイルを再確認してください。'
            ];
        }
    }

    /**
     * function ajax_search_shipment_notifi
     * Description: lấy thông tin để thống kê tình trạng hỗ trợ và cách phân phối
     * @author: channl
     * Created: 2019/10/03
     * Updated: 2019/10/14
     */
    public function ajax_search_shipment_notifi(Request $request){
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        // $this->data['data_table1'] = $this->ShipmentNotification->getTotalOrder($range, $date_from, $date_to); 
        $this->data['data_table2'] = $this->ShipmentNotification->getListShipmentBySiteType($range, $date_from, $date_to);
        $result = array (
            'data_2' => $this->data['data_table2']
        );
        return Response::json($result);
    }

    /**
     * function ajax_get_list_supplier_by_site_type
     * Description: Hàm lấy danh sách nhà cung cấp để xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng ở màn hình 7
     * @param:
     * range: điều kiện tìm kiếm theo ngày
     * date_from: từ ngày
     * date_to: đến ngày
     * site_type: loại website
     * @author channl
     * Created: 2019/12/11
     * Updated: 2019/12/11
     */
    public function ajax_get_list_supplier_by_site_type(Request $request){
        $result = [];
        $data = $this->ShipmentNotification->getListSupplierBySiteType($request);
 
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
     * function ajax_export_shipment_notification
     * Description: Xuất file
     * - Nếu là amazon: filename.txt
     * - Nếu là rakuten: filename.csv
     * - Nếu là yahoo: filename.xlsx
     * @author chan_nl
     * Created: 2019/12/18
     * Updated: 2019/12/18
     */
    public function ajax_export_shipment_notification(Request $request){ 
        $hisProcess = $this->historyProcessModel;
        $collection = null;       
        $delivery_method = config('constants.DELIVERY_METHOD');
        $arr_collect = [];
        $data = $this->ShipmentNotification->getListSupplierBySiteType($request);
        $sagawa = [0 => 1, 1 => 7, 2 => 9];
        $yamato = [0 => 2, 1 => 3, 2 => 4];
        $post_office = [0 => 5, 1 => 6];
        $purchase_list = [];
        if(count($data) > 0){
            $insert_HP = array();
            $insert_HP['process_user'] = auth()->user()->login_id;
            $insert_HP['process_permission'] = auth()->user()->type;
            $str_process_description = '<b>ダウンロード</b>: ';
            if($request['screen'] == 7){
                $insert_HP['process_screen'] = '出荷通知';
            }else if($request['screen'] == 3){
                $insert_HP['process_screen'] = '注文検索';
            }
            if($request['website'] == "rakuten"){
                $str_process_description .= '楽天用出荷通知データ<br>';
            }else if($request['website'] == "yahoo"){
                $str_process_description .= 'Yahoo用出荷通知データ<br>';
            }
            foreach($data as $val){
                $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';
                //Kiểm tra nếu order trùng thì đưa vào arr
                if(!in_array($val['purchase_id'], $purchase_list))
                {
                    array_push($purchase_list, $val['purchase_id']);//push những order id không có trong mảng vào purchase_list
                }
                // End kiểm tra nếu order trùng thì đưa vào arr
                $shiping_company = 0;
                if(in_array($val['delivery_method'], $sagawa)){
                    $shiping_company = 1002;
                }else if (in_array($val['delivery_method'], $yamato)){
                    $shiping_company = 1001;
                }else if (in_array($val['delivery_method'], $post_office)){
                    $shiping_company = 1003;
                }else {                
                    $shiping_company = 1000;
                }
                if($request['website'] == "rakuten"){
                    $shipment = [
                        'order_code' => $val['order_code'],
                        '送付先ID' => null,
                        '発送明細ID' => null,
                        'お荷物伝票番号' => $val['shipment_code'],
                        '配送会社' => $shiping_company,
                        '発送日' => isset($val['es_shipment_date']) ? date('Y-m-d', strtotime($val['es_shipment_date'])) : null
                    ];
                    array_push($arr_collect, $shipment);
                }else if($request['website'] == "yahoo"){           
                    $shipment = [
                        'order_code' => $val['order_code'],
                        'ShipCompanyCode' => $shiping_company,
                        'ShipInvoiceNumber1' => $val['shipment_code'],
                        'ShipInvoiceNumber2' => null,
                        'ShipDate' => isset($val['es_shipment_date']) ? date('Y-m-d', strtotime($val['es_shipment_date'])) : null,
                        'ShipStatus' => 2
                    ];
                    array_push($arr_collect, $shipment);
                }
            }
            $str_process_description = rtrim($str_process_description, '、');    
            //Kiểm tra nếu có check chọn đổi tình trạng hỗ trợ thành xử lý xuất hàng xong thì cập nhật tình trạng hỗ trợ
            if($request['stage3'] == 3){
                $str_process_description .= '<br>発注ステータスを出荷済に変更する';
                $this->ShipmentNotification->updateStatusAtShipmentNotification($purchase_list);
            }
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            //End kiểm tra nếu có check chọn đổi tình trạng hỗ trợ thành xử lý xuất hàng xong thì cập nhật tình trạng hỗ trợ
            
            // $collection = collect($arr_collect);
            $collection = $arr_collect;
        }else {
            // $collection = collect($arr_collect);
            $collection = $arr_collect;
        }
        $filename = mb_convert_encoding($request['file_name'], 'SJIS-win', 'auto');
        if($request['website'] == 'rakuten'){
            $col_title = array("注文番号", "送付先ID", "発送明細ID", "お荷物伝票番号", "配送会社", "発送日");
            $col_value = array('order_code', '送付先ID', '発送明細ID', 'お荷物伝票番号', '配送会社', '発送日');
        }else if($request['website'] ==  "yahoo"){
            $col_title = array( "OrderId", "ShipCompanyCode", "ShipInvoiceNumber1", "ShipInvoiceNumber2", "ShipDate", "ShipStatus");
            $col_value = array('order_code', 'ShipCompanyCode', 'ShipInvoiceNumber1', 'ShipInvoiceNumber2', 'ShipDate', 'ShipStatus');
        }
        $this->ExportCSV->Export($filename, $col_title, $col_value, $collection);
        // return Excel::download(new ShipmentNotificationExport($collection, $request['website']), $filename.'.csv');
    }
}
