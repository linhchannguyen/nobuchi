<?php

namespace App\Http\Controllers\Payables;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Model\HistoryProcess\HistoryProcess;
use App\Repositories\Services\Supplier\SupplierServiceContract;
use App\Repositories\Services\PayableDetail\PayableDetailServiceContract;
use App\Repositories\Services\Order\OrderDetailServiceContract;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotifiedPayExportSheets;
use Illuminate\Support\Facades\Storage;

class PayableDetailController extends Controller
{
    /**
     * class Payable detail controller
     * class controll the data and show data in view
     * @author channl
     * date 2019/10/03
     */
    protected $Supplier;
    protected $PayableDetail;
    protected $detail_service;
    protected $historyProcessModel;
    public function __construct(SupplierServiceContract $Supplier, PayableDetailServiceContract $PayableDetail, OrderDetailServiceContract $detail_service, HistoryProcess $historyProcessModel){
        $this->Supplier = $Supplier;
        $this->PayableDetail = $PayableDetail;
        $this->detail_service = $detail_service;
        $this->historyProcessModel = $historyProcessModel;
    }

    /**
     * function index
     * @author channl
     * Created: 2019/10/17
     * Updated: 2019/10/17
     */
    public function index(Request $request){
        $supplier_id = $request['supplier_id'];
        $year = $request['year'];
        $month = $request['month'];
        $order_id = $request['order_id'];
        $purchase_id = $request['purchase_id'];
        if(!is_numeric($supplier_id) || $supplier_id == 0 || !is_numeric($year) || !is_numeric($month)){// Nếu định dạng sai thì quay về trang trước
            return back();
        }
        $suppliers = $this->Supplier->getAll();// Danh sách nhà cung cấp
        $this->data['title'] = '仕入先別買掛詳細一覧';
        $breadcrumbs = [
            //breabcbums cấp 1
            0 => [
                0 => '仕入先別買掛一覧',
                1 => 'payable',
            ],
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['active'] = 6;
        $this->data['supplier_id'] = $supplier_id;
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['suppliers'] = $suppliers;
        $this->data['payabledetail'] = $this->PayableDetail->PayableDetail($supplier_id, $year, $month, $order_id, $purchase_id);// Chi tiết tiền trả cho nhà cung cấp
        return view('payables.payable_detail', $this->data);
    }

    
    /**
     * function ajax_update_order_detail
     * Description: Cập nhật thông tin order
     * @param $order_id, $supplier_id, $year, $month
     * @author channl
     * Created: 2019/10/23
     * Updated: 2019/10/23
     */
    public function ajax_update_order_detail(Request $request){
        // check neu da co chinh sua roi thi reload lai tran
        $data = $request['data'];
        $updated_at = $data['check_update'];
        $date_update =  $this->detail_service->checkUpdate($updated_at);
        if($date_update['validate'] == false)
        {
            return [
                'status' => false,
                'message' => '発注情報（金額、送り状番号、納品日など）は変更されました。画面をリロードして最新データで再度ご確認してくださいませ。',
            ];
        }
        // end
        $update['data'] = $data['arr_checked'];
        return $this->PayableDetail->updateOrderDetail($update);
    }

    /**
     * function ajax_search_order_detail
     * Description: Tìm kiếm thông tin order
     * @param $order_id, $supplier_id, $year, $month
     * @author channl
     * Created: 2019/10/23
     * Updated: 2019/10/23
     */
    public function ajax_search_order_detail(Request $request){
        $order_id = $request['order_id'];
        $purchase_id = $request['purchase_id'];
        $supplier_id = $request['supplier_id'];
        $year = $request['year'];
        $month = $request['month'];
        $data_search = $this->PayableDetail->PayableDetail($supplier_id, $year, $month, $order_id, $purchase_id);
        if(empty($data_search)){// Nếu tìm không thấy thì success = false else true
            $result = array (
                'success' => false,
                'message' => '検索条件に該当するデータがありません。'
            );
        }else{
            $result = array (
                'data' => $data_search->toArray(),
                'success' => true
            );
        }
        return Response::json($result);
    }

    /**
     * function ajax_order_payable
     * Description: Xuất file tiền chi trả cho NCC
     * @param 
     * @author channl
     * Created: 2019/12/12
     * Updated: 2019/12/12
     */
    public function ajax_order_payable(Request $request){
        $hisProcess = $this->historyProcessModel;
        $arr_detail = $request->get('arr_detail') != null ? $request->get('arr_detail') : null;
        $payment_term = $request->get('payment_term');//Kỳ hạn thống kê
        $check_download_pdf = $request->get('pdf');
        $data = $this->PayableDetail->exportPayableForSupplier($arr_detail);
        $insert_HP = array();
        $insert_HP['process_user'] = auth()->user()->login_id;
        $insert_HP['process_permission'] = auth()->user()->type;
        $insert_HP['process_screen'] = '仕入先別買掛詳細一覧';
        if($check_download_pdf == null){
            $str_process_description = '<b>ダウンロード</b>: 支払通知書<br>';
            if(!empty($data)){
                foreach($data as $value){
                    $str_process_description .= '受注ID: '.$value['order_code'].' - 発注ID: '.$value['purchase_code'].'<br>';
                }
                $str_process_description = rtrim($str_process_description, '<br>');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            return Excel::download(new NotifiedPayExportSheets($data, $payment_term), '', \Maatwebsite\Excel\Excel::XLSX);
        }else {
            $str_process_description = '<b>ダウンロード</b>: PDF支払通知書<br>';
            if(!empty($data)){
                foreach($data as $value){
                    $str_process_description .= '受注ID: '.$value['order_code'].' - 発注ID: '.$value['purchase_code'].'<br>';
                }
                $str_process_description = rtrim($str_process_description, '<br>');
                $insert_HP['process_description'] = $str_process_description;
                $hisProcess->create($insert_HP);
            }
            $request->supplier_name = str_replace("(", "_", $request->supplier_name);
            $request->supplier_name = str_replace(")", "_", $request->supplier_name);
            $xls_file = $request->supplier_name . date('YmdHis').'.xlsx';
            $pdf_file = $request->supplier_name . date('YmdHis').'.pdf';
            Excel::store(new NotifiedPayExportSheets($data, $payment_term), $xls_file);
            $inputPath = storage_path('app'.DIRECTORY_SEPARATOR.$xls_file);
            $outputPath = storage_path('app'.DIRECTORY_SEPARATOR);

            //Linux thi dung lenh nay
            $conv_inputPath = str_replace("(", "_", $inputPath);
            $conv_inputPath = str_replace(")", "_", $conv_inputPath);
            // $moveCommand = "export HOME=/tmp;/opt/libreoffice6.4/program/soffice --headless --convert-to pdf:writer_pdf_Export --outdir $outputPath $conv_inputPath 2>&1";
            // $moveCommand = "export HOME=/tmp;/usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export --outdir $outputPath $conv_inputPath 2>&1";
            $moveCommand = "\"C:\Program Files\LibreOffice\program\soffice.exe\" --headless --convert-to pdf:writer_pdf_Export --outdir $outputPath $conv_inputPath";
            shell_exec($moveCommand);
            $temp_pdf = file_get_contents($outputPath.$pdf_file);
            $headers = [
                'Content-Type'=>'application/octet-stream',
                'Content-Transfer-Encoding'=>'Binary',
                'Content-disposition'=>"attachment; filename=\"". basename($pdf_file) . "\"",
            ];
            $response = response($temp_pdf)->withHeaders($headers);
            Storage::delete($xls_file);
            Storage::delete($pdf_file);
            return $response;
        }
    }
}
