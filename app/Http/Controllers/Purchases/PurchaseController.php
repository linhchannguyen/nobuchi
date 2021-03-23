<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Repositories\Services\Purchase\PurchaseServiceContract;
use App\Model\HistoryProcess\HistoryProcess;
use App\Exports\PurchaseExportSheets;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\PurchaseSendMail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;
class PurchaseController extends Controller
{
    /**
     * class Purchase controller
     * class controll the data and show data in view
     * @author channl
     * date 2019/10/25
     */
    public $arr_check = [];
    private $PurchaseService;
    protected $historyProcessModel;
    public function __construct(PurchaseServiceContract $PurchaseService, HistoryProcess $historyProcessModel)
    {
        $this->PurchaseService = $PurchaseService;
        $this->historyProcessModel = $historyProcessModel;
    }

    /**
     * funciton index
     * Description: show list purchases
     * @author channl
     * Created: 2019/10/24
     * Updated: 2019/10/24
     */
    public function index(){
        $this->data['title'] = '発注書出力・送信';
        $this->data['active'] = 3;
        return view('purchases.index', $this->data);
    }

    /**
     * function ajax_search_purchase
     * Description: search order
     * @author channl
     * Created: 2019/11/14
     * Updated: 2019/11/14
     */
    public function ajax_search_purchase(Request $request){
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        // $this->data['data_table1'] = $this->PurchaseService->getTotalOrder($range, $date_from, $date_to);
        $this->data['data_table2'] = $this->PurchaseService->getListPurchaseBySupplier($range, $date_from, $date_to);
        $result = array (
            'data_2' => $this->data['data_table2'],
        );
        return Response::json($result);
    }
    /**
     * function ajax_export_purchase
     * Description: export purchases
     * @author channl
     * Created: 2019/11/14
     * Updated: 2019/11/14
     */
    public function ajax_export_purchase(Request $request){
        $hisProcess = $this->historyProcessModel;
        $current = Carbon::now()->format('Y-m-d');
        $date_from = $request->get('date_from');//tìm kiếm từ ngày
        $date_to = $request->get('date_to');//tìm kiếm đến ngày
        $range = (int)$request->get('range');//phạm vi tìm kiếm
        $sup_id = $request->get('supplier_id');
        $supplier_id = (!empty($sup_id) || $sup_id == "0") ? (int)$sup_id : null;//phạm vi tìm kiếm
        $stage1 = $request->get('stage1') != null ? 1 : null;//stage: bỏ loại đang bảo lưu
        $stage2 = $request->get('stage2') != null ? 2 : null;//stage: bỏ loại cần xác nhận
        $stage3 = $request->get('stage3') != null ? 3 : null;//stage: đổi tình trạng hỗ trợ -> status 4: đang xử lý đóng gói trong khi click tự động đánh bill gửi hàng
        $stage4 = $request->get('stage4') != null ? 4 : null;//stage: tự động gửi fax trong lúc download
        $sel_download = $request->get('sel_download') != null ? $request->get('sel_download') : null;//loại download: (1: giấy đặt hàng và giấy hướng dẫn đóng gói, 2: giấy đặt hàng, 3: giấy hướng dẫn đóng gói)
        if($sel_download == '11' || $sel_download == 11) {
                $sel_download = 1;
        }else if($sel_download == '22' || $sel_download == 22) {
                $sel_download = 2;
        }else if($sel_download == '33' || $sel_download == 33) {
                $sel_download = 3;
        }

        $arr_detail = $request->get('arr_detail') != null ? $request->get('arr_detail') : null;//xuất order theo mảng order detail ở màn hình 3
        $check_download_pdf = $request->get('pdf');
        if($check_download_pdf == null){
            $data = $this->PurchaseService->exportPurchase($date_from, $date_to, $supplier_id, $range, $arr_detail, $stage1, $stage2);
            if(!empty($data) && $stage3 == 3){
                $arr_purchase_update =  [];
                $screen = $request['screen'];
                if($screen == 22){
                    $purchase_list = $request['arr_purchase'];//Nếu có chọn đổi status ở màn hình 22 thì update ngày đặt hàng là ngày hiện tại
                    foreach($data as $key => $value){
                        $data[$key]['purchase_date'] = $current;
                        if(!in_array($value['purchase_id'], $arr_purchase_update)){
                            if($value['purchase_status'] == 1){
                                array_push($arr_purchase_update, $value['purchase_id']);
                            }
                        }
                    }
                }else {
                    foreach($data as $key => $value){
                        if($screen == 5){//Nếu có chọn đổi status ở màn hình 5 thì update ngày đặt hàng là ngày hiện tại
                            $data[$key]['purchase_date'] = $current;
                        }
                        if(!in_array($value['purchase_id'], $arr_purchase_update)){
                            array_push($arr_purchase_update, $value['purchase_id']);
                        }
                    }
                }
                if(!empty($arr_purchase_update)){
                    $this->PurchaseService->updateStatusAtPurchase($arr_purchase_update, $screen);
                }
            }
            //Ghi log user
            if(!empty($data)){
                $screen_ = '';
                $update_status_act = '';
                if(isset($request['screen'])){
                    if($request['screen'] == 22){
                        $screen_ = '仕入先様用発注確認画面';
                    }else if($request['screen'] == 10){
                        $screen_ = '注文内容編集';
                    }else if($request['screen'] == 9){
                        $screen_ = '仕入先別買掛詳細一覧';
                    }else if($request['screen'] == 7){
                        if($stage3 == 3){
                            $update_status_act = '発注ステータスを出荷済に変更する';
                        }
                        $screen_ = '出荷通知';
                    }else if($request['screen'] == 6){
                        if($stage3 == 3){
                            $update_status_act = '発注ステータスを送り状作成済に変更する';
                        }
                        $screen_ = '送り状出力';
                    }else if($request['screen'] == 5){
                        if($stage3 == 3){
                            $update_status_act = '発注ステータスを発注済、梱包済に変更する';
                        }
                        $screen_ = '発注書出力・送信';
                    }else if($request['screen'] == 3){
                        $screen_ = '注文検索';
                    }
                }
                $act = '';
                if($sel_download == 1){
                    $act = '発注一覧表＋発注明細・梱包指示書';
                }else if($sel_download == 2){
                    $act = '発注一覧表';
                }else if($sel_download == 3){
                    $act = '発注明細・梱包指示書';
                }
                $insert_HP = array();
                $insert_HP['process_user'] = auth()->user()->login_id;
                $insert_HP['process_permission'] = auth()->user()->type;
                $insert_HP['process_screen'] = $screen_;
                $str_process_description = '<b>ダウンロード</b>: '.$act.'<br>';
                if($update_status_act != ''){
                    $str_process_description .= $update_status_act.'<br>';
                }
                foreach($data as $value){
                    $str_process_description .= '受注ID: '.$value['order_code'].'('.$value['purchase_code'].')、';
                }
                $str_process_description = rtrim($str_process_description, '、');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            if($stage4 == 4){
                $this->FaxSendMail($data, $sel_download, $supplier_id, '');
            }
            return Excel::download(new PurchaseExportSheets($data, $sel_download), '', \Maatwebsite\Excel\Excel::XLSX);
        }else {
            $hisProcess = $this->historyProcessModel;
            $detail_id = $request->get('arr_detail') != null ? $request->get('arr_detail') : null;//xuất order theo mảng order detail ở màn hình 3   
            $data = $this->PurchaseService->exportPurchase($date_from, $date_to, $supplier_id, $range, $detail_id, $stage1, $stage2);
            if(!empty($data) && $stage3 == 3){
                $arr_purchase_update =  [];
                $screen = $request['screen'];
                if($screen == 22){
                    foreach($data as $key => $value){
                        $data[$key]['purchase_date'] = $current;//Nếu có chọn đổi status ở màn hình 22 thì update ngày đặt hàng là ngày hiện tại
                        if(!in_array($value['purchase_id'], $arr_purchase_update)){
                            if($value['purchase_status'] == 1){
                                array_push($arr_purchase_update, $value['purchase_id']);
                            }
                        }
                    }
                }else {
                    foreach($data as $key => $value){
                        if($screen == 5){//Nếu có chọn đổi status ở màn hình 5 thì update ngày đặt hàng là ngày hiện tại
                            $data[$key]['purchase_date'] = $current;
                        }
                        if(!in_array($value['purchase_id'], $arr_purchase_update)){
                            array_push($arr_purchase_update, $value['purchase_id']);
                        }
                    }
                }
                if(!empty($arr_purchase_update)){
                    $this->PurchaseService->updateStatusAtPurchase($arr_purchase_update, $screen);
                }
            }
            //Add log user
            $screen_ = '';
            $update_status_act = '';
            if(isset($request['screen'])){
                if($request['screen'] == 22){
                    $screen_ = '仕入先様用発注確認画面';
                }else if($request['screen'] == 10){
                    $screen_ = '注文内容編集';
                }else if($request['screen'] == 9){
                    $screen_ = '仕入先別買掛詳細一覧';
                }else if($request['screen'] == 7){
                    if($stage3 == 3){
                        $update_status_act = '発注ステータスを出荷済に変更する';
                    }
                    $screen_ = '出荷通知';
                }else if($request['screen'] == 6){
                    if($stage3 == 3){
                        $update_status_act = '発注ステータスを送り状作成済に変更する';
                    }
                    $screen_ = '送り状出力';
                }else if($request['screen'] == 5){
                    if($stage3 == 3){
                        $update_status_act = '発注ステータスを発注済、梱包済に変更する';
                    }
                    $screen_ = '発注書出力・送信';
                }else if($request['screen'] == 3){
                    $screen_ = '注文検索';
                }
            }
            $act = '';
            if($sel_download == 1){
                $act = 'PDF発注一覧表＋発注明細・梱包指示書';
            }else if($sel_download == 2){
                $act = 'PDF発注一覧表';
            }else if($sel_download == 3){
                $act = 'PDF発注明細・梱包指示書';
            }
            $insert_HP = array();
            $insert_HP['process_user'] = auth()->user()->login_id;
            $insert_HP['process_permission'] = auth()->user()->type;
            $insert_HP['process_screen'] = $screen_;
            $str_process_description = '<b>ダウンロード</b>: '.$act.'<br>';
            if($update_status_act != ''){
                $str_process_description .= $update_status_act.'<br>';
            }
            foreach($data as $val){
                $str_process_description .= '受注ID: '.$val['order_code'].'('.$val['purchase_code'].')、';
            }
            $str_process_description = rtrim($str_process_description, '、');
            $insert_HP['process_description'] = $str_process_description;
            $hisProcess->create($insert_HP);
            // Add log user
            $request->supplier_name = str_replace("(", "_", $request->supplier_name);
            $request->supplier_name = str_replace(")", "_", $request->supplier_name);
            $xls_file = $request->supplier_name . date('YmdHis').'.xlsx';
            $pdf_file = $request->supplier_name . date('YmdHis').'.pdf';
            Excel::store(new PurchaseExportSheets($data, $sel_download), $xls_file);
            $inputPath = storage_path('app'.DIRECTORY_SEPARATOR.$xls_file);
            $outputPath = storage_path('app'.DIRECTORY_SEPARATOR);
            //Linux thi dung lenh nay       
            $conv_inputPath = str_replace("(", "_", $inputPath);
            $conv_inputPath = str_replace(")", "_", $conv_inputPath);
            //$moveCommand = "export HOME=/tmp;/opt/libreoffice6.4/program/soffice --headless --convert-to pdf:writer_pdf_Export --outdir $outputPath $conv_inputPath 2>&1";     
            //$moveCommand = "export HOME=/tmp;/usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export --outdir $outputPath $conv_inputPath 2>&1";     
            $moveCommand = "\"C:\Program Files\LibreOffice\program\soffice.exe\" --headless --convert-to pdf:draw_pdf_Export --outdir $outputPath $conv_inputPath";
            shell_exec($moveCommand);
            $temp_pdf = file_get_contents($outputPath.$pdf_file);
            $headers = [
                'Content-Type'=>'application/octet-stream',
                'Content-Transfer-Encoding'=>'Binary',
                'Content-disposition'=>"attachment; filename=\"". basename($pdf_file) . "\"",
            ];
            $response = response($temp_pdf)->withHeaders($headers);
            if($stage4 == 4){
                $this->FaxSendMail($data, $sel_download, $supplier_id, $pdf_file);
            }
            Storage::delete($xls_file);
            Storage::delete($pdf_file);
            return $response;
        }
    }
    /**
     * function __checkDuplicate
     * Description: check duplicate by condition
     * @author chan_nl
     * Created: 2020/06/19
     * Updated: 2020/06/19
     */
    private function __checkDuplicate($conditions = [])
    {
        $sheet = [];
        foreach($this->arr_check as $key =>$value) {
            if($conditions['delivery_method'] == $value['delivery_method'] && $conditions['delivery_way'] == $value['delivery_way']
                && $conditions['ship_zip'] == $value['ship_zip'] && $conditions['purchase_code'] == $value['purchase_code']
                &&$conditions['order_id'] == $value['id']){
                $package_purchase = [
                    'product_code' => $value['product_code'],
                    'product_name' => $value['product_name'],
                    'quantity_set' => $value['quantity_set'],
                    'quantity' => $value['quantity'],
                    'quantity_in_set' => $value['quantity'],
                    'cost_price_tax' => $value['cost_price'],
                    'total_price_tax' => $value['total_price']
                ];

                array_push($sheet, $package_purchase);
                unset($this->arr_check[$key]);
            }
        }
        if(empty($sheet))
        {
            return false;
        }
        return $sheet;
    }

    /**
     * メールをeFAX宛に送り、FAXを送信してもらう
     * @author hamasaki
     * Created: 2020/02/20
     * Updated: 2020/02/28
     */
    public function FaxSendMail($data, $sel_download, $supplier_id, $file){
    	// 作成日をファイル名として取得
    	$file_name = date('YmdHis');

    	// 仕入先の情報を取得
    	$supplier = DB::select('SELECT id, name, fax01 || fax02 || fax03 AS fax, email FROM suppliers WHERE id = '.$supplier_id);

        // ファイルを作り一旦保存する（保存しないと送信ができない）
        if($file == ''){
            Excel::store(new PurchaseExportSheets($data, $sel_download), $file_name.'.xlsx');
        }

    	// 各種データを変数に入れる
    	$name = $supplier[0]->name;				// 仕入先名
    	$to = '81'.substr($supplier[0]->fax, 1).'@efaxsend.com';	// メールの送り先アドレス
//    	$to = $supplier[0]->email;	// メールの送り先アドレス(テスト用)
    	$text = '{nocoverpage}';

    	// メールを送信
    	Mail::to($to)->send(new PurchaseSendMail($name, $text, ($file != '' ? $file : $file_name.'.xlsx')));

    	// 自らのアドレスにもメールを送信
    	$text = 'eFAXに、'.$name.'('.$to.')にFAXを送るメールを送信しました。';
    	$to = config('mail.from');
    	$to = $to['address'];
    	Mail::to($to)->send(new PurchaseSendMail($name, $text, NULL));

    	Storage::delete($file_name.'.xlsx');

    	return true;
    }
}
