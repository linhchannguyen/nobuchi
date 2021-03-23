<?php

namespace App\Http\Controllers\SupplierHome;

use App\Model\Users\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Services\Order\OrderDetailServiceContract;
use App\Repositories\Services\SupplierHome\SupplierHomeServiceContract;

class PurchaseConfirmController extends Controller
{
    private $SupplierHomeService;
    private $detail_service;
    public function __construct(SupplierHomeServiceContract $SupplierHomeService, OrderDetailServiceContract $detail_service){
        $this->SupplierHomeService = $SupplierHomeService;
        $this->detail_service = $detail_service;
    }
    /**
     * function index
     * Description: show screen purchase confirm for supplier
     */
    public function index(Request $request){
        $user_login = auth()->user();
        $user = Users::find($user_login->id);
        $check_date = false;
        $data_length = 0;
        $message = '';
        $date = '';
        $date_to = '';
        $breadcrumbs = [
            0 => [//breabcrums cấp 1
                0 => '仕入先様用トップ',//title nav-bar
                1 => 'supplier',//nav-bar
            ],
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['title'] = '仕入先様用発注確認画面';
        if(isset($request->flag_p_status_1)){
            if($request->flag_p_status_1 == 1){
                $this->data['flag_p_status_1'] = 1;
            }else {
                $this->data['flag_p_status_1'] = 0;                
            }
        }else {
            $this->data['flag_p_status_1'] = 0;
        }
        if(isset($request->flag_p_status_2)){
            if($request->flag_p_status_2 == 2){
                $this->data['flag_p_status_2'] = 2;
            }else {
                $this->data['flag_p_status_2'] = 0;                
            }
        }else {
            $this->data['flag_p_status_2'] = 0;              
        }
        if(isset($request->flag_p_status_3)){
            if($request->flag_p_status_3 == 3){
                $this->data['flag_p_status_3'] = 3;
            }else {
                $this->data['flag_p_status_3'] = 0;                
            }
        }else {            
            $this->data['flag_p_status_3'] = 0;  
        }
        if(isset($request['date'])){
            if (preg_match("/^[0-9]{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/", $request['date'])) {
                $date = $request['date']; 
                $check_date = true;
            }
        }
        if($check_date == true && isset($request['date_to'])){
            if(preg_match("/^[0-9]{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/", $request['date_to'])){
                $date_to = $request['date_to'];
                $check_date = true;
            }else {
                $check_date = false;
            }
        }
        if($check_date == true){
            if($date_to < $date && $date_to != '' && $date != ''){//Nếu màn hình detail có chọn ngày to > from thì thông báo lỗi
                $message = '日時至は日時自以降で選択してください。';
            }else {               
                if($date_to == '') {//Lần đầu truy cập màn hình detail thì search theo ngày $data nên gán $date_to = $data
                    $date_to = $date;
                }
                $this->data['data'] = $this->SupplierHomeService->getPurchaseDetailByDate($user->supplier->id, $date, $date_to, $this->data['flag_p_status_1'], $this->data['flag_p_status_2'], $this->data['flag_p_status_3']);
                if(!empty($this->data['data'])){
                    $data_length = count($this->data['data']);
                }
            }
        }else {
            $message = '無効な日付です。再入力してください。';
        }
        $data_old = [];
        if($data_length > 0){
            $arr_collect = [];
            $order_code = '';
            $arr_shipment = [];
            foreach($this->data['data'] as $val){
                if(!in_array($val->ship_id, $arr_shipment)){
                    array_push($arr_shipment, $val->ship_id);
                }
            }
            $data = DB::table('order_details')->selectRaw("order_details.id as od_id, order_details.order_code, shipments.id, shipments.shipment_code,
                                            shipments.shipment_date")
                                            ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                                            ->whereIn('shipments.id', $arr_shipment)
                                            ->orderBy('order_details.order_code')->get()->toArray();
            foreach($data as $val){
                $values = [
                    'order_detail_id' => $val->od_id,
                    'order_code' => $val->order_code,
                    'shipment_date' => date('Y/m/d', strtotime($val->shipment_date)),
                    'ship_id' => $val->id,
                    'shipment_code' => $val->shipment_code
                ];
                if($val->order_code != $order_code)
                {
                    $arr_collect = [];
                }
                array_push($arr_collect, $values);
                if($val->order_code == $order_code)
                {
                    array_pop($data_old);
                }
                $order_code = $val->order_code;
                array_push($data_old, $arr_collect);
            }
        }
        $this->data['message'] = $message;
        $this->data['data_length'] = $data_length;
        $this->data['data_old'] = $data_old;
        $this->data['date'] = $request['date'];        
        $this->data['date_to'] = $request['date_to'];
        $this->data['supplied'] = $user->supplier->name;
        return view('home.supplier.purchase_confirm', $this->data);
    }
    
    /**
     * function ajax_update_order_detail
     * Description: Cập nhật thông tin order
     * @author channl
     * Created: 2019/11/11
     * Updated: 2019/11/11
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
        $update['one_on_one'] = isset($data['one_on_one']) ? $data['one_on_one'] : null;
        $update['one_on_many'] = isset($data['one_on_many']) ? $data['one_on_many'] : null;
        $update['list_remove_ship'] = isset($data['list_remove_ship']) ? $data['list_remove_ship'] : null;
        return $this->SupplierHomeService->updatePurchaseDetail($update);
    }

    /**
     * function ajaxUpdateStatusPurchase
     * Description: Cập nhật status đặt hàng
     * @author chan_nl
     * Created: 2020/05/07
     * Updated: 2020/05/07
     */
    public function ajaxUpdateStatusPurchase(Request $request){
        return $this->SupplierHomeService->updatePurchaseStatus($request->data);
    }
}
