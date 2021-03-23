<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
class NotifiedPayExportSheets implements WithMultipleSheets
{    
    use Exportable;

    protected $data;
    protected $payment_term = [];
    public $arr_check = [];
    public $run = 1;
    public function __construct($data = [], $payment_term = [])
    {
        $this->data = $data;
        $this->payment_term = $payment_term;
    }

    /**
     * function __checkSheet
     * Description: Hàm lấy những sản phẩm có cùng đk delivery_method, delivery_way, ship_zip để đưa vào 1 sheet -> xuất giấy chỉ dẫn đóng gói
     * @return array
     */
    private function __checkSheet($conditions = [])
    {
        $sheet = [];
        foreach($this->arr_check as $key =>$value) {
            $purchase_date = !empty($value['purchase_date']) ? date('Y/m/d', strtotime($value['purchase_date'])) : null;
            // if($conditions['purchase_date'] == $purchase_date)
            // {
                $es_shipdate = $value['shipment_date'];
                if(empty($es_shipdate)){
                    $es_shipdate = '';
                }else {
                    $es_shipdate = date('Y/m/d', strtotime($es_shipdate));
                }
                $package_purchase = [
                    'shipment_date' => $es_shipdate,
                    'purchase_code' => $value['purchase_code'],
                    'site_name' => $value['site_name'],
                    'product_name' => $value['product_name'],
                    'maker_code' => $value['maker_code'],
                    'quantity_set' => $value['note'],
                    'product_info' => $value['product_info'],
                    'quantity_in_set' => $value['quantity'],
                    'cost_price' => $value['cost_price'],
                    'total_price' => $value['total_price'],
                    'total_price_tax' => $value['total_price_tax'],
                ];
                

                array_push($sheet, $package_purchase);
                unset($this->arr_check[$key]);
            // }
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
            $this->run++;
            $count_data = (!empty($this->data)) ? count($this->data) : 0;
            DB::beginTransaction();// start transaction
            try{
                if($count_data > 0){
                    $this->arr_check = $this->data->toArray();
                    $user = auth()->user();
                    $package_purchase = [];
                    $package_info = [];

                    // Xử lý thông báo tiền chi trả cho NCC
                    foreach($this->data as $key => $val){
                        $sheetChild = [];
                        $purchase_date = !empty($val['purchase_date']) ? date('Y/m/d', strtotime($val['purchase_date'])) : null;
                        $conditions = [
                            'purchase_date' => $purchase_date
                        ];

                        $sheetChild = $this->__checkSheet($conditions);

                        if($sheetChild != false)
                        {
                            $package_info_ = [
                                'supplied' => $val['supplied'],
                                'purchase_date' => $purchase_date,
                                'payment_term' => $this->payment_term,
                                'purchase_code' => $val['purchase_code']
                            ];
                            array_push($package_info, $package_info_);
                            array_push($package_purchase, collect($sheetChild));
                        }
                    }
                    // End xử lý thông báo tiền chi trả cho NCC
                    // In từng sheet
                    for($i = 0; $i < sizeof($package_purchase); $i++){
                        $page = [
                            0 => $i + 1,
                            1 => sizeof($package_purchase)
                        ];
                        $sheets[] = new NotifiedPayExport($package_purchase[$i], $package_info[$i], $page);
                        Log::info('User: '.$user->id.'|'.$user->login_id.' - Download payable for supplier: '.$package_info[$i]['supplied']);
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