<?php
// set namspace PayableDetailService
namespace App\Repositories\Services\PayableDetail;

use App\Model\HistoryProcess\HistoryProcess;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PayableDetailService implements PayableDetailServiceContract
{
    /**
     * class PayableDetailService implements PayableDetailServiceContract
     * connect model and controller
     * get data from database
     * @author Chan
     * date 2019/10/15
    */

    protected $Purchase;
    protected $OrderDetail;
    protected $Order;
    protected $Shipment;
    protected $historyProcessModel;

    public function __construct(){
        $this->Purchase = new Purchase();
        $this->OrderDetail = new OrderDetail();
        $this->Order = new Order();
        $this->Shipment = new Shipment();
        $this->historyProcessModel = new HistoryProcess();
    }

    /**
     * function PayableDetail()
     * Desciption: Thống kê tiền phải trả cho nhà cung cấp theo niên độ năm. Logic lấy ngày tập kết hàng làm điều kiện thống kê
     * @author channl
     * Created: 2019/10/16
     * Updated: 2019/12/16
     */
    public function PayableDetail($supplier_id = null, $year, $month, $order_id = null, $purchase_id = null)
    {
        try {
            $query = $this->Order;
            $query = $query->select(
                'order_details.id as o_detail_id', 'order_details.supplied_id', 'order_details.supplied', 'orders.purchase_date as p_created_at',
                'shipments.es_shipment_date as od_deliv_date', 'shipments.id as ship_id', 'orders.id as o_id', 
                'orders.order_code as o_order_id', 'purchases.purchase_code as p_code', 'purchases.id as p_id',
                'order_details.product_name as od_product_name', 'order_details.ship_address1 as od_ship_address1', 
                'order_details.ship_address2 as od_ship_address2', 'order_details.ship_address3 as od_ship_address3', 
                'order_details.quantity as od_quantity', 'order_details.cost_price as od_cost_price', 'purchases.total_cost_price as od_total_price', 'purchases.total_cost_price_tax as od_total_price_tax',
                'purchases.price_edit as p_price_edit', 'order_details.tax as od_tax', 'shipments.delivery_method', 'order_details.updated_at as o_updated_at',
            )
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('purchases', 'purchases.id', '=', 'order_details.purchase_id')
            ->join('shipments', 'shipments.id', '=', 'order_details.shipment_id');
            if($year != 0 && $month == 0){
                $last_year = ($year - 1);
                $query = $query->whereBetween(DB::raw('CAST(shipments.es_shipment_date AS DATE)'), ["$last_year/10/01", "$year/09/30"]);
            }
            else if($year == 0 && $month != 0){
                $query = $query->whereMonth('shipments.es_shipment_date', $month);
            }else if($year != 0 && $month != 0){
                $query = $query->whereYear('shipments.es_shipment_date', $year)->whereMonth('shipments.es_shipment_date', $month);
            }
            if($order_id != null){
                $query = $query->where('orders.order_code', 'like',"%".$order_id."%");
            }
            if($purchase_id != null){
                $query = $query->where('purchases.purchase_code','like' , "%".$purchase_id."%");
            }
            if($supplier_id != -1){                
                $query = $query->where('order_details.supplied_id', $supplier_id);
            }
            $query = $query->whereIn('orders.status', [3,4,5,6,7]);
            $query = $query->where('purchases.status', 4);
            return $query->orderBy('orders.purchase_date', 'asc')
            ->paginate(50);
        }catch(Exception $ex){
            Log::debug($ex->getMessage());
        }
    }

    /**
     * function updateOrderDetail()
     * Desciption: Cập nhật thông tin tiền chi trả cho nhà cung cấp
     * @author channl
     * Created: 2019/10/22
     * Updated: 2019/10/22
     */
    public function updateOrderDetail($request){
        $hisProcess = $this->historyProcessModel;
        $query_orderdetail = $this->OrderDetail;
        $query_purchase = $this->Purchase;
        $query_shipment = $this->Shipment;
        DB::beginTransaction();// start transaction database
        try{
            $user = auth()->user();
            Log::info('User: '.$user->id.'|'.$user->name.' update data:');           
            $insert_HP = array();
            $insert_HP['process_user'] = auth()->user()->login_id;
            $insert_HP['process_permission'] = auth()->user()->type;
            $insert_HP['process_screen'] = '仕入先別買掛詳細一覧';
            $str_process_description = '<b>変更</b>:';
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
                $od_cost_price = $data[11];//Giá bán chưa thuế
                $od_quantity = $data[12];//Số lượng
                $total_price_update = ($od_cost_price * intval($od_quantity)) + $price_edit;// Tiền mua hàng sau khi chỉnh sửa
                $total_price_tax_update = $total_price_update + round($total_price_update * $tax);
                // Update order detail      
                $query_orderdetail->where('id', $od_id)->update([
                    'delivery_date' => $deliv_date,//Cập nhật ngày giao hàng
                    'total_price' => $total_price_update,//Cập nhật tiền mua hàng
                    'total_price_tax' => $total_price_tax_update//Cập nhật tiền mua hàng có thuế
                ]);
                $query_purchase->where('id', $p_id)->update([
                    'price_edit' => $price_edit,// Cập nhật tiền đính chính: đổi logic không cộng dồn tiền đính chính cũ 
                    'total_cost_price' => $total_price_update,//Cập nhật tiền mua hàng
                    'total_cost_price_tax' => $total_price_tax_update,//Cập nhật tiền mua hàng có thuế
                ]);    
                $query_shipment->where('id', $ship_id)->update([
                    'es_shipment_date' => $deliv_date,//Cập nhật ngày dự định giao hàng
                    'updated_by' => auth()->user()->name//Cập nhật ngày giao hàng
                ]);
                $str_process_description .= '<br>受注ID: '.$data[8].' - 発注ID: '.$data[9].'(納品日(配達日時): '.$data[10].' => '.$deliv_date.', 訂正金額: '.$data[6].' => '.$price_edit.')';
            }        
            $str_process_description = rtrim($str_process_description, '<br>'); 
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
     * Function exportPayableForSupplier
     * Description: Xuất giấy chi trả tiền cho NCC. Logic lấy ngày tập kết hàng làm điều kiện thống kê
     * @author channl
     * create: 2019/11/12
     * update: 2019/11/12
     */
    public function exportPayableForSupplier($order_detail_id = []){
        try{
            $query = $this->Order;
            $query = $query->select('orders.id', 'orders.order_code', 'order_details.id as order_detail_id', 'orders.status', 
                                    'order_details.supplied', 'order_details.supplied_id', 'purchases.purchase_code', 'orders.purchase_date', 'shipments.es_shipment_date as shipment_date',
                                    'purchases.cost_price', 'purchases.cost_price_tax', 'purchases.total_cost_price as total_price', 'purchases.total_cost_price_tax as total_price_tax',
                                    'order_details.product_name', 'order_details.product_code', 'order_details.quantity_set', 'order_details.product_info',
                                    'site_type.name as site_name', 'order_details.quantity', 'products.note', 'products.maker_code');
            $query = $query->join('order_details', 'orders.id', 'order_details.order_id')
                           ->join('products', 'products.product_id', 'order_details.product_id')
                           ->join('purchases', 'purchases.id', 'order_details.purchase_id')
                           ->join('shipments', 'shipments.id', 'order_details.shipment_id')
                           ->join('site_type', 'site_type.id', 'orders.site_type')
                           ->distinct();
            $query = $query->whereIn('order_details.id', $order_detail_id);
            
            return $query->orderBy('shipments.es_shipment_date')->get();
        }catch(Exception $e) {
            Log::debug($e->getMessage());
        }
    }
}