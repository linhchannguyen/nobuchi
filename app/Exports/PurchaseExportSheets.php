<?php

namespace App\Exports;

use Exception;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
class PurchaseExportSheets implements WithMultipleSheets
{    
    // use Exportable;

    protected $data;
    private $sel_download;
    public $arr_check = [];
    public $run = 1;
    public function __construct($data = [], $sel_download)
    {
        $this->data = $data;
        $this->sel_download = $sel_download;//Chọn option xuất file màn hình [5]
    }

    /**
     * function __checkSheet
     * Description: Hàm lấy những sản phẩm có cùng đk shipment_id, purchase_code, order_id để đưa vào 1 sheet -> xuất giấy chỉ dẫn đóng gói
     * Lưu ý để dùng được hàm nay bắt buộc order_id phải sắp xếp tăng dần hoặc giảm dần
     * @return array
     */
    private function __checkSheet($conditions = [])
    {
        $sheet = [];
        foreach($this->arr_check as $key =>$value) {
            if($conditions['shipment_id'] == $value['shipment_id'] && $conditions['purchase_code'] == $value['purchase_code'] && $conditions['order_id'] == $value['id']){
                $package_purchase = [
                    'maker_code' => $value['maker_code'],
                    'product_name' => $value['product_name'],
                    'quantity_set' => $value['note'],
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
    public function sheets(): array
    {
        $sheets = [];
        if($this->run <= 1){//Giải pháp tạm thời chạy run 1 lần vì vào sheets nó chạy 2 lần k rõ lý do nên time download bị lâu
            set_time_limit(0);
            $this->run++;
            $count_data = (!empty($this->data)) ? count($this->data) : 0;
            DB::beginTransaction();// start transaction
            try{
                if($count_data > 0){
                    $this->arr_check = $this->data;
                    $user = auth()->user();
                    $delivery_method = config('constants.DELIVERY_METHOD');
                    $delivery_way = config('constants.DELIVERY_WAY');
                    $export_purchase = [];
                    $purchase_info = [];//Lấy thông tin chung của order
                    $package_purchase = [];
                    $package_info = [];//Lấy thông tin chung giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                    $arr_duplicate = [];
                    $arr_collect = [];
                    $pur_date = '';
                    foreach($this->data as $key => $val){
                        // Xử lý xuất giấy đặt hàng
                        $order_purchases = [
                            'order_detail_id' => $val['order_detail_id'],
                            'product_name' => $val['product_name'],
                            'maker_code' => $val['maker_code'],
                            'quantity_set' => $val['note'],
                            'product_info' => $val['product_info'],
                            'quantity' => $val['quantity'],
                            'cost_price' => $val['cost_price'],
                            'total_price' => $val['total_price'],
                            'es_delivery_date' => $val['es_delivery_date'],
                            'shipment_code' => $val['shipment_code'],
                            'supplier_id' => $val['supplied_id'],
                            'order_id' => $val['id'],
                            'cost_price_tax' => $val['cost_price_tax'],
                            'total_cost_price' => $val['total_price'],
                            'total_cost_price_tax' => $val['total_price_tax'],
                            'message' => $val['message'],
                            'comments' => $val['comments'],
                            'purchase_code' => $val['purchase_code'],
                        ];
                        /**
                         * Kiểm tra nếu trong data có những order trùng nhau thì push nó vào 1 phần tử theo dạng
                         * VD: $this->data có dữ liệu order: [1, 1, 2]
                         * Array[0] => object [0 => 1, 1 => 1]
                         * Array[1] => object [0 => 2]
                         */
                        //Cùng ngày đặt hàng thì chung 1 sheet
                        if(date('Y-m-d', strtotime($val['purchase_date'])) != $pur_date)
                        {
                            $arr_collect = [];
                            $data_info = [//mảng chứa thông tin chung từ dòng 1 đến dòng 3 của file
                                0 => $val['supplied'],
                                1 => $val['purchase_date'] != null ? $val['purchase_date'] : null,
                                2 => $val['purchase_code'] != null ? $val['purchase_code'] : null,
                            ];
                            array_push($purchase_info, $data_info);
                        }
                        
                        array_push($arr_collect, $order_purchases);
                        if(date('Y-m-d', strtotime($val['purchase_date'])) == $pur_date)
                        {
                            array_pop($arr_duplicate);
                        }
                        $pur_date = date('Y-m-d', strtotime($val['purchase_date']));
                        array_push($arr_duplicate, collect($arr_collect));
                        // End xử lý xuất giấy đặt hàng
                    }
                    $export_purchases = [];
                    $purchase_infos = [];
                    foreach($arr_duplicate as $key => $values){
                        $export_purchase_ = [];
                        $check_count = 0;
                        $check_count_ = false;
                        foreach($values as $val){
                            $check_count++;
                            $comment = '詳細は発注明細・梱包指示書を確認してください';
                            if(mb_strlen($val['comments']) <= 48){
                                $comment = $val['comments'];
                            }
                            if(empty($val['maker_code']) || $val['maker_code'] == "null"){
                                $val['maker_code'] = '';
                            }
                            $product_name = str_replace("\n", "", $val['product_name']);
                            $product_name = str_replace(" ", "　", $product_name);
                            $quantity_set = $val['quantity_set'];
                            if(!is_numeric($quantity_set)){
                                $quantity_set = 1;
                            }
                            $order_purchases_ = [
                                'no' => '',
                                'purchase_code' => $val['purchase_code'],
                                'product_name' => $product_name,
                                'maker_code' => $val['maker_code'],
                                'es_delivery_date' => $val['es_delivery_date'],//Ngày tập kết hàng
                                'shipment_code' => $val['shipment_code'],
                                'quantity_set' => $quantity_set,
                                'quantity' => $val['quantity'],
                                'total_quantity' => $quantity_set * $val['quantity'],
                                'cost_price' => $val['cost_price'],
                                'total_price' => $val['total_price'],
                                'comments' => $comment,
                            ];
                            array_push($export_purchase_, $order_purchases_);
                            if($check_count == 16){
                                array_push($export_purchase, collect($export_purchase_));
                                array_push($purchase_infos, $purchase_info[$key]);
                                $export_purchase_ = [];
                                $check_count = 0;
                                $check_count_ = true;
                            }
                        }
                        if($check_count < 16 && $check_count){
                            array_push($export_purchase, collect($export_purchase_));
                            array_push($purchase_infos, $purchase_info[$key]);
                        }
                    }
                    $package_infos = [];//Lấy thông tin chung giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                    foreach($this->data as $key => $val){
                        // Xử lý xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                        $sheetChild = [];
                        $conditions = [
                            'shipment_id' => $val['shipment_id'],
                            'order_id' => $val['id'],
                            'purchase_code' => $val['purchase_code'],
                        ];
                        $sheetChild = $this->__checkSheet($conditions);
                        
                        if($sheetChild != false)
                        {
                            $package_info_ = [
                                'supplied' => $val['supplied'],
                                'purchase_date' => ($val['purchase_date'] != null) ? $val['purchase_date'] : null,
                                'purchase_code' => ($val['purchase_code'] != null) ? $val['purchase_code'] : null,
                                'order_date' => $val['order_date'],
                                'order_code' => $val['order_code'],
                                'buyer_zip' => $val['buyer_zip1'].$val['buyer_zip2'],
                                'ship_zip' => $val['ship_zip'],
                                'buyer_add' => $val['buyer_add'],
                                'ship_add' => $val['ship_add'],
                                'buyer_name' => $val['buyer_name'],
                                'ship_name' => $val['ship_name'],
                                'buyer_phone' => $val['buyer_tel1'],
                                'ship_phone' => $val['ship_phone'],
                                'es_delivery_date' => $val['es_delivery_date'],//Ngày dự định xuất hàng (ngày tập kết hàng)
                                'es_shipment_time' => $val['es_shipment_time'],//Giờ dự định xuất hàng (giờ tập kết hàng)
                                'shipment_date' => $val['shipment_date'],//Ngày dự định nhận hàng (ngày xuất hàng)
                                'shipment_time' => $val['shipment_time'],//Giờ dự định nhận hàng (ngày xuất hàng)
                                'delivery_method' => $delivery_method[$val['delivery_method']],
                                'deli_method' => $val['delivery_method'],
                                'delivery_way' => $delivery_way[$val['delivery_way']],
                                'shipment_code' => $val['shipment_code'],
                                'money_daibiki' => $val['money_daibiki'],
                                'pay_request' => $val['pay_request'],
                                'comments' => $val['comments'],
                                'wrapping_paper_type' => $val['wrapping_paper_type'],
                                'gift_wrap' => $val['gift_wrap'],
                                'message' => $val['message'],
                            ];
                            array_push($package_info, $package_info_);
                            array_push($package_purchase, ($sheetChild));
                        }
                        // End xử lý xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                    }
                    $package_purchases = [];
                    $package_infos = [];
                    $package_page = 0;
                    foreach($package_purchase as $key => $values){
                        $package_purchase_ = [];
                        $check_count = 0;
                        $total = ceil(count($values)/4);
                        $check_total = false;
                        foreach($values as $val){
                            $check_count++;
                            array_push($package_purchase_, $val);
                            if($check_count == 4){
                                $check_total = true;
                                $package_info[$key]['page'] = ++$package_page;
                                $package_info[$key]['total'] = $total;
                                array_push($package_purchases, collect($package_purchase_));
                                array_push($package_infos, $package_info[$key]);
                                $package_purchase_ = [];
                                $check_count = 0;
                            }
                        }
                        if($check_count < 4){
                            if(!$check_total){
                                $package_page = 0;
                            }
                            array_push($package_purchases, collect($package_purchase_));
                            $package_info[$key]['page'] = ++$package_page;
                            $package_info[$key]['total'] = $total;
                            array_push($package_infos, $package_info[$key]);
                        }
                    }
                    if($this->sel_download == 1){//Xuất giấy đặt hàng và giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                        for($i = 0; $i < sizeof($export_purchase); $i++){//Chạy danh sách id của order để lấy thông tin order detail                            
                            $page = [
                                0 => $i + 1,
                                1 => sizeof($export_purchase) + sizeof($package_purchases)
                            ];
                            $sheets[] = new PurchaseExport($export_purchase[$i], $purchase_infos[$i], $page);
                        }
                        for($j = 0; $j < sizeof($package_purchases); $j++){
                            $page = [
                                0 => sizeof($export_purchase)+ ($j+1),
                                1 => sizeof($package_purchases) + sizeof($export_purchase)
                            ];
                            $sheets[] = new PackInstructionsExport($package_purchases[$j], $package_infos[$j], $page);
                        }
                    }else if($this->sel_download == 2){//xuất giấy đặt hàng        
                        for($i = 0; $i < sizeof($export_purchase); $i++){//Chạy danh sách id của order để lấy thông tin order detail                 
                            $page = [
                                0 => $i + 1,
                                1 => sizeof($export_purchase)
                            ];
                            $sheets[] = new PurchaseExport($export_purchase[$i], $purchase_infos[$i], $page);
                        }
                    }
                    else if($this->sel_download == 3){//Xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng
                        for($j = 0; $j < sizeof($package_purchases); $j++){
                            $page = [
                                0 => $j + 1,
                                1 => sizeof($package_purchases)
                            ];
                            $sheets[] = new PackInstructionsExport($package_purchases[$j], $package_infos[$j], $page);
                        }
                    }
                }else {
                    $sheets[] = null;
                }
            DB::commit(); // commit database
            }catch(Exception $e){
                DB::rollBack(); // reset data
                Log::error($e->getMessage());
            }
        }
        
        return $sheets;
    }
}