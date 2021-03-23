<?php
namespace App\Repositories\Services\Import;

use App\Repositories\Services\Import\ImportServiceContract;
use App\Model\HistoryProcess\HistoryProcess;
use App\Model\Imports\Import;
use App\Model\Orders\ImportError;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use App\Model\Taxs\TaxDetail;
use Carbon\Carbon;
use Exception;
use DB;
use Illuminate\Support\Facades\Log;

class ImportService implements ImportServiceContract
{
    private $import_model;
    private $historyProcessModel;

    public function __construct(HistoryProcess $historyProcessModel)
    {
        $this->import_model = new Import();
        $this->historyProcessModel = $historyProcessModel;
    }

    /**
     * function index import
     * @author Dat
     * 2019/11/01
     */
    public function IndexImport()
    {
        $query = $this->import_model;
        try
        {
			$query = $query->selectRaw('imports.id, type, date_import, website, number_order, number_success, number_error, number_duplicate, import_set_from, import_set_to, import_errors.id as error_id')
			->leftJoin('import_errors', 'import_errors.import_id', 'imports.id')
			->orderBy('date_import', 'desc')
			->paginate(50);
            return $query;
        } catch (Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "Not connect to Databases";
        }
    }
    /**
     * function import eccube
     * @author Dat
     * 2020/01/17
     */
    public function ImportEcCube ($date_from = null, $date_to = null, $site_type = array())
    {
    	$this->dtb_order = 'dtb_order';
		$this->ec_connect = DB::connection('eccube');
		$mtb_option = config('constants.MTB_OPTION');
		$hisProcess = $this->historyProcessModel;
		$insert_HP = array();
		$insert_HP['process_user'] = auth()->user()->login_id;
		$insert_HP['process_permission'] = auth()->user()->type;
		$insert_HP['process_screen'] = '取込設定';
        try{
        	$order_model_ec = $this->ec_connect->table($this->dtb_order);
        	$query_detail = $this->ec_connect->table($this->dtb_order);
        }catch (Exception $exception) {
			return [
				'status' => false,
				'message' => $exception->getMessage()
			];
        }
    	list($site_type, $site_type_mix) = $this->getSiteType();
        $order_model = new Order();
		$order_rimac  = $order_model;
		//Lấy danh sách order từ bảng orders của hệ thống mới để kiểm tra với danh sách order từ bảng dtb_order phía dưới có trùng order không
        $data_rimac_order   = $order_rimac->selectRaw('orders.order_code as order_id')
                                ->join('imports', 'orders.import_id', '=', 'imports.id')
                                ->where('imports.type' , 1)
								->whereBetween('orders.order_date', [$date_from." 00:00:00", $date_to." 23:59:59"])->get();
        //Lấy danh sách order từ bảng dtb_order
        $order_eccube = $order_model_ec;
        $data_order_eccube = $order_eccube->selectRaw('dtb_order.*')
        						->whereBetween('dtb_order.create_date',[$date_from." 00:00:00", $date_to." 23:59:59"])
								->orderBy('dtb_order.order_id', 'asc')->get();
        $order_validate = [];//Danh sách order của bảng dtb_order: rỗng nếu import trùng, có data import không trùng
		$order_duplicate = '';//Danh sách duplicate khi import trùng
		$number_duplicate = 0;
		$number_error = 0;
		$list_order_error = '';
        foreach($data_order_eccube as $value_ec)
        {
			$import_valid = true;
            foreach ($data_rimac_order as $value_rm) {
                if(($value_ec->order_id."") === ($value_rm->order_id.""))
                {
					$import_valid = false;
					break;
                }
			}
            if($import_valid == true){//Kiểm tra nếu không trùng thì đưa vào danh sách import
                array_push($order_validate, $value_ec->order_id);
            }else if($import_valid == false){//Kiểm tra nếu trùng thì đưa vào danh sách duplicate
				$number_duplicate++;
                $order_duplicate .="$value_ec->order_id 、";
            }
		}
        $order_mix_validate = [];//Danh sách order của bảng order_mix: rỗng nếu import trùng, có data import không trùng
		$order_mix_duplicate = '';//Danh sách duplicate khi import trùng
        if(!empty($site_type_mix))
        {
			list($order_mix_validate, $order_mix_duplicate) = $this->getInportDtbMixOrder($date_from, $date_to, $site_type_mix);
        }
        
        if(!in_array(1, $site_type)){
        	$order_validate = [];
		}

		$date_import = new Carbon();
		$today = Carbon::today()->format('Y-m-d 05:59:59');
        $insert_order = '';
        $data_import = [];
        $order_detail_model = new OrderDetail();
        $import_model = new Import();
        $import_shipment = new Shipment();
        $import_purchase =  new Purchase();
		$insert_import_error = new ImportError();
        // check import dữ liệu
		$query = $order_model_ec;
        $data_import_ec = $query;
		$number_import_success = 0;
		//Lấy chi tiết sản phẩm của order
		// dtb_supplier.name as supplier_name,dtb_supplier.zip01 as supplier_zip01
		// ,dtb_supplier.zip02 as supplier_zip02, dtb_supplier.addr01 as supplier_addr01, dtb_supplier.addr02 as supplier_addr02
		// ,dtb_supplier.tel01 as supplier_tel01, dtb_supplier.tel02 as supplier_tel02, dtb_supplier.tel03 as supplier_tel03,
		// dtb_supplier.cargo_schedule_day, dtb_products.maker_id, dtb_products.maker_code,*
	    $data_import_ec = $query_detail->selectRaw("dtb_order.order_id, 
										dtb_supplier.supplier_id, dtb_supplier.name as supplier_name,dtb_supplier.zip01 as supplier_zip01,dtb_supplier.zip02 as supplier_zip02,
										dtb_supplier.addr01 as supplier_addr01,dtb_supplier.addr02 as supplier_addr02,dtb_supplier.tel01 as supplier_tel01,dtb_supplier.tel02 as supplier_tel02,
										dtb_supplier.tel03 as supplier_tel03,dtb_supplier.supplier_code_sagawa,dtb_supplier.supplier_code_kuroneko,dtb_supplier.cargo_schedule_time_from,
										dtb_supplier.cargo_schedule_time_to,dtb_supplier.cargo_schedule_day,
										dtb_order_detail.quantity, dtb_order_detail.product_id,
										dtb_products_class.deliv_fee, dtb_products_class.cost_price, dtb_products_class.product_code, dtb_products_class.price02,
										dtb_products.maker_id, dtb_products.maker_code, dtb_products.short_name, dtb_products.tax_class,
										dtb_shipping.shipping_id,dtb_shipping.shipping_name01,dtb_shipping.shipping_name02,dtb_shipping.shipping_kana01,
										dtb_shipping.shipping_kana02,dtb_shipping.shipping_addr01,dtb_shipping.shipping_addr02,dtb_shipping.shipping_zip01,
										dtb_shipping.shipping_zip02,dtb_shipping.shipping_tel01,dtb_shipping.shipping_tel02,dtb_shipping.shipping_tel03,
										dtb_shipping.shipping_date,dtb_shipping.shipping_time, dtb_shipping.shipping_option")
									->join('dtb_shipping', "dtb_shipping.order_id", "dtb_order.order_id")
									->leftJoin('dtb_shipment_item', 'dtb_shipment_item.order_id', 'dtb_order.order_id')
	    							->join('dtb_order_detail', function($sub){
										$sub->on("dtb_order_detail.order_id", "dtb_order.order_id");
											$sub->whereRaw('case when dtb_shipment_item.order_id is not null then
											dtb_shipment_item.product_class_id = dtb_order_detail.product_class_id and dtb_shipment_item.shipping_id = dtb_shipping.shipping_id
											else true end');
									})
	    							->leftJoin('dtb_products', 'dtb_order_detail.product_id', 'dtb_products.product_id')
	    							->leftJoin('dtb_supplier', 'dtb_supplier.supplier_id', 'dtb_products.supplier_id')
	    							->leftJoin('dtb_products_class', 'dtb_products_class.product_class_id', 'dtb_order_detail.product_class_id')
	    							->where('dtb_products.del_flg', 0)
									->where('dtb_shipping.del_flg', 0);
									if(!empty($order_validate)){
										$data_import_ec = $data_import_ec->whereIn('dtb_order.order_id', $order_validate);
									}
		$data_import_ec = $data_import_ec->whereBetween('dtb_order.create_date',[$date_from." 00:00:00", $date_to." 23:59:59"])
									->orderBy('dtb_order.order_id', 'asc')
									->get()->toArray();
		//Danh sách order lấy về từ EC_Cube
		$number_import = $data_order_eccube;
 	    if(count($number_import) == 0 && empty($site_type_mix)){//Nếu không có chọn web khác Rimac mà data rỗng thì báo null
 	        return [
				'status' => false,
				'message' => '指定された取込期間(受注日)で受注がありません。'
			];
 	    }else if(!in_array(1, $site_type) && count($order_mix_validate) == 0 && $order_mix_duplicate == ''){//Nếu không có chọn web Rimac mà data rỗng thì báo null
			return [
				'status' => false,
				'message' => '指定された取込期間(受注日)で受注がありません。'
			];
		}
        DB::beginTransaction();
        $list_order = [];
        $data_insert_details = [];
        try {
        	if(in_array(1, $site_type))
        	{
	        	$insert_import = $import_model;
	        	$update_import = $import_model;
				$data_insert_details  = $data_import_ec;
	        	$data_import = [
	                'type' => 1,
	                'date_import' => $date_import,//$data_import
	                'website' => 'EC',
	                'number_order' => count($number_import),
	                'import_set_from' => $date_from,
	                'import_set_to' => $date_to
	            ];
	            $insert_import = $insert_import->create($data_import);
				if($order_duplicate == ''){
					$data_import_error['import_id'] = $insert_import->id;
					$arr_sup = [];
					foreach ($number_import as $key => $value) {
						$order = [];
						$num_page_purchase = 0;
						$ship_code_ = 0;
						$duplicate_ship = [];
						$pay_request = 1;
						$check_order_error = 0;
						$fax = $value->order_fax01 . '-' . $value->order_fax02 . '-' . $value->order_fax03;
						$order = [
								'site_type' => 1,
								'import_id' => $insert_import->id,
								'order_code' => $value->order_id,
								'order_date' => $value->create_date,
								'buyer_name1' => $value->order_name01,
								'buyer_name2' => $value->order_name02,
								'buyer_name1_kana' => $value->order_kana01,
								'buyer_name2_kana' => $value->order_kana02,
								'buyer_address_1' => $value->order_addr01,
								'buyer_address_2' => $value->order_addr02,
								'buyer_email' => $value->order_email,
								'buyer_zip1' => $value->order_zip01,
								'buyer_zip2' => $value->order_zip02,
								'buyer_tel1' => $value->order_tel01.$value->order_tel02.$value->order_tel03,
								'buyer_tel2' => '',
								'buyer_tel3' => '',
								'buyer_sex' => $value->order_sex,
								'buyer_birthday' => $value->order_birth,
								'tax' => $value->tax,
								'fax' => ($fax != '--') ? $fax : '',
								'charge' => $value->charge,
								'sub_total' => round($value->subtotal),
								'order_discount' => round($value->discount),
								'order_total' => round($value->total),
								'use_point' => $value->use_point,
								'payment_total' => round($value->payment_total),
								'payment_id' => $value->payment_id,
								'payment_method' => $value->payment_method,
								'status' => ($value->status == 3) ? 7 : 1,
								'flag_confirm' => 0, // default không có
								'comments' => $value->note ,
						];
						$insert_order = $order_model->create($order);
						foreach($data_insert_details as $value_detail){
							if($value_detail->order_id === $value->order_id)
							{
								$check_order_error++;
								$supplier_id = 0;
								$shipment_id = '';
								$add_ship = true;
								$tax_class = 0;
								$tax_details = TaxDetail::where('tax_class', $value_detail->tax_class)->orderBy('id')->get()->toArray();
								$tax_detail = [];
								foreach($tax_details as $key => $value_tax){
									if($value_tax['apply_date'] <= $value->create_date){
										$tax_detail = $value_tax;
									}
								}
								if(!empty($tax_detail)){
									$tax_class = ($tax_detail['tax_rate'] / 100);
								}
								// đánh số mã đặt hàng
								$page = 0;
								array_push($arr_sup, $value_detail->supplier_id);
								$purchase_date = Carbon::now()->format('Ymd');
								$count_sup = 0;
								foreach($arr_sup as $key => $d){
									if($d == $value_detail->supplier_id  || $value_detail->supplier_id  == ''){
										$count_sup++;
									}
								}
								$page = $count_sup;
								if($value_detail->supplier_id != '')
								{
									if($value_detail->supplier_id < 10)
									{
										$supplier_id = '000'.$value_detail->supplier_id;		
									} else if($value_detail->supplier_id < 100){
										$supplier_id = '00'.$value_detail->supplier_id;
									} else if($value_detail->supplier_id < 1000){
										$supplier_id = '0'.$value_detail->supplier_id;
									}
								}else {
									$supplier_id = '0000';
								}
								$page_ = '';
								if($page < 10)
								{
									$page_ = '000'.$page;
								} else if($page < 100){
									$page_ = '00'.$page;
								} else if($page < 1000){
									$page_ = '0'.$page;
								}
								$purchase_code = $supplier_id."-$purchase_date-$page_";
								$shipment_insert = $import_shipment;
								// check import chung 1 shipment
								if(!empty($duplicate_ship))
								{
									foreach($duplicate_ship as $ship) {
										if($value_detail->supplier_id == $ship['supplied_id'] && $value_detail->shipping_id == $ship['shipping_id']){
											$shipment_id = $ship['shipment_id'];
											$purchase_code = $ship['purchase_code'];
											$add_ship = false;
											break;
										}
									}
								}
								if(empty($duplicate_ship) || $add_ship == true) {
									$ship_code_++;
									$es_shipdate = $today;
									if(!empty($value_detail->cargo_schedule_day)){
										$cagro_day = intval($value_detail->cargo_schedule_day);
										$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +$cagro_day day"));
										$day_of_week = date('w', strtotime($es_shipdate));
										switch($day_of_week){
											case 0:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +1 day"));
												break;
											case 6:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +2 day"));
												break;
										}
									}
									$data_import_shipment = [
											'delivery_method' => 8,
											'shipment_code' => "その他$ship_code_",
											'delivery_way' => 1,
											'shipment_customer' => $value_detail->shipping_name01.$value_detail->shipping_name02,
											'shipment_address' => $value_detail->shipping_addr01.$value_detail->shipping_addr02,
											'shipment_zip' => $value_detail->shipping_zip01 . '-' . $value_detail->shipping_zip02,
											'shipment_phone' => $value_detail->shipping_tel01. '-'. $value_detail->shipping_tel02. '-'. $value_detail->shipping_tel03,
											'type' => 0,
											'status' => 0,
											'del_flg' => 0,
											'pay_request' => $pay_request,
											'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
											'shipment_fee' => round($value_detail->deliv_fee),
											'shipment_date' => $value_detail->shipping_date,
											'receive_date' =>  $value_detail->shipping_date, 
											'es_shipment_date' =>  $es_shipdate, 
											'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
											'shipment_time' => !empty($value_detail->shipping_time) ? $value_detail->shipping_time :'0',
											'es_shipment_time' => !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
									];
									$shipment_insert = $shipment_insert->create($data_import_shipment);// insert shipments table
									$shipment_id = $shipment_insert->id;
									$data_import_shipment['shipment_id'] = $shipment_insert->id;
									array_push($duplicate_ship, [
										'shipment_id' => $shipment_id,//id của bảng shipments hệ thống mới
										'shipping_id' => $value_detail->shipping_id,//id của bảng dtb_shipping
										'supplied_id' => $value_detail->supplier_id,
										'purchase_code' => $purchase_code
										]);
								}
								$cost_price = round($value_detail->cost_price);
								$total_cost_price = $value_detail->quantity * $cost_price;
								$cost_price_tax = $cost_price + round($cost_price * $tax_class);
								$total_cost_price_tax = $total_cost_price + round($total_cost_price * $tax_class);
								$price_sale = round($value_detail->price02);
								$total_price_sale = $value_detail->quantity * $price_sale;
								$price_sale_tax = $price_sale + round($price_sale * $tax_class);
								$total_price_sale_tax = $total_price_sale + round($total_price_sale * $tax_class);
								$purchase_insert = $import_purchase;
								$data_import_purchase = [
										'order_id' => $insert_order->id,
										'purchase_code' => $purchase_code,
										'supplier_id' =>$value_detail->supplier_id ,
										'purchase_quantity' => $value_detail->quantity,
										'cost_price' => $cost_price,
										'total_cost_price' => $total_cost_price,
										'cost_price_tax' => $cost_price_tax,
										'total_cost_price_tax' => $total_cost_price_tax,
								];
								$purchase_insert = $purchase_insert->create($data_import_purchase);
								$shipping_option = '';
								if(in_array($value_detail->shipping_option, [0,1,2,3])){
									$shipping_option = $mtb_option[$value_detail->shipping_option];
								}
								$data_order_detail = [
										'order_id' => $insert_order->id,
										'order_code' => $value_detail->order_id,
										'purchase_id' => $purchase_insert->id,
										'shipment_id' => $shipment_id,
										'product_code' => $value_detail->product_code,
										'sku' => $value_detail->product_code,
										'maker_id' => $value_detail->maker_id,
										'maker_code' => $value_detail->maker_code,
										'product_id' => $value_detail->product_id,
										'product_name' => $value_detail->short_name,
										'quantity' => $value_detail->quantity,
										'price_sale' => $price_sale, // giá bán chưa thuế
										'price_sale_tax'=> $price_sale_tax,  // giá bán có thuế
										'total_price_sale' => $total_price_sale,  // tổng giá bán chưa thuế
										'total_price_sale_tax' => $total_price_sale_tax,  // tổng giá bán có thuế
										'cost_price' => $cost_price,  // nguyên giá chưa thuế
										'total_price' => $total_cost_price, // tổng nguyên giá chưa thuế
										'cost_price_tax' => $cost_price_tax, // nguyên giá có thuế
										'total_price_tax' => $total_cost_price_tax, // tổng nguyên giá có thuế
										'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
										'supplied' => $value_detail->supplier_name == '' ? 'NPO法人クローバープロジェクト21' : $value_detail->supplier_name,
										'supplier_zip1' => $value_detail->supplier_zip01,
										'supplier_zip2' => $value_detail->supplier_zip02,
										'supplier_addr1' => $value_detail->supplier_addr01,
										'supplier_addr2' => $value_detail->supplier_addr02,
										'supplier_tel1' => $value_detail->supplier_tel01,
										'supplier_tel2' => $value_detail->supplier_tel02,
										'supplier_tel3' => $value_detail->supplier_tel03,
										'supplier_code_sagawa' => $value_detail->supplier_code_sagawa,
										'supplier_code_kuroneko' => $value_detail->supplier_code_kuroneko,
										'delivery_method' => 8,
										'ship_name1' => $value_detail->shipping_name01.$value_detail->shipping_name02,
										'ship_name1_kana' => $value_detail->shipping_kana01,
										'ship_name2_kana' => $value_detail->shipping_kana02,
										'ship_phone' => $value_detail->shipping_tel01. '-'. $value_detail->shipping_tel02. '-'. $value_detail->shipping_tel03,
										'ship_zip' => $value_detail->shipping_zip01 . '-' . $value_detail->shipping_zip02,
										'ship_address1' => $value_detail->shipping_addr01,
										'ship_address2' => $value_detail->shipping_addr02,
										'delivery_fee' => $value_detail->deliv_fee,
										'es_delivery_date_from' => $value_detail->shipping_date,
										'delivery_date' => $value_detail->shipping_date,
										'receive_date' => $value_detail->shipping_date, 
										'receive_time' =>  !empty($value_detail->shipping_time) ? $value_detail->shipping_time :'0',
										'delivery_way' => 1, // defaul cách giao hàng 1
										'pay_request' => $pay_request,
										'gift_wrap' => $shipping_option
								];
								$insert_detail = $order_detail_model->create($data_order_detail);
								$pay_request = 0;
							}
						}
						array_push($list_order, $order);
						if($check_order_error == 0){
							$list_order_error .= $value->order_id.',';
							$number_error++;
						}
					}
					$number_import_success = count($list_order) - $number_error;
					if($number_error > 0)
					{
						$data_import_error['list_id'] = $list_order_error;
						$insert_import_error->create($data_import_error);
					}
					$update_import = $update_import->where('id', $insert_import->id)->update(['number_success' => ($number_import_success), 'number_duplicate' => $number_duplicate, 'number_error' => $number_error, 'import_set_from' => $date_from, 'import_set_to' => $date_to]);
				}else {
					$update_import = $update_import->where('id', $insert_import->id)->update(['number_success' => 0, 'number_duplicate' => $number_duplicate, 'number_error' => 0, 'import_set_from' => $date_from, 'import_set_to' => $date_to]);
				}
				$insert_HP['process_description'] = '<b>受注取込:</b><br>ECサイト: 自社<br>取込件数: '.count($number_import).'<br>成功件数: '.$number_import_success.'<br>重複件数: '.$number_duplicate.'<br>エラー件数: '.$number_error;
				$hisProcess->create($insert_HP);
	        }

       		if(!empty($site_type_mix))
        	{
				$mix_inport_flg = $this->inportMixOrderData($date_from, $date_to, $site_type_mix, $order_mix_validate);
        		if($mix_inport_flg == false){
					return [
						'status' => false,
						'message' => 'SQL error.'
					];
				}
			}
            DB::commit();
            return [
				'status' => true,
				'message' => ''
			];
		}catch (Exception $exception)
		{
			DB::rollBack();
			Log::debug($exception->getMessage());
			return [
				'status' => false,
				'message' => $exception->getMessage()
			];
		}
    }

	// function get list order error from import_errors table
	public function getListImportId($error_id = null)
	{
		if(empty($error_id))
		{
			return false;
		}
		$query = new ImportError();
		$query = $query->selectRaw('list_id')->where('id', $error_id);
		return $query->first();
	}
	/**
	 * import lại những hóa đơn bị lỗi với điều kiện hóa đơn đó đã được chỉnh sửa đúng với điều kiện import nếu không thì vẫn báo lỗi.
	 * điều kiện import không lỗi là (order có sản phẩm)
	 */
	public function ReImport ($list_order = null, $import_id = null)
    {
    	$this->dtb_order = 'dtb_order';
		$mtb_option = config('constants.MTB_OPTION');
        $order_model = new Order();
        $order_rimac  = $order_model;
		$order_detail_model_ec_mix = $this->setDtbMixTable();
		$order_model_ec_mix = $this->setDtbMixTable();
        $this->ec_connect = DB::connection('eccube');
		$hisProcess = $this->historyProcessModel;
		$insert_HP = array();
		$insert_HP['process_user'] = auth()->user()->login_id;
		$insert_HP['process_permission'] = auth()->user()->type;
		$insert_HP['process_screen'] = '取込設定';
		$website = config('constants.WEB_TYPE');
		$number_duplicate = 0;
		$number_error = 0;
		$list_order_error = '';
		
        $date_import = new Carbon();
        $today = Carbon::today()->format('Y-m-d 05:59:59');
        $data_import = [];
        $order_detail_model = new OrderDetail();
        $import_model = new Import();
        $import_shipment = new Shipment();
        $import_purchase =  new Purchase();
		$insert_import_error = new ImportError();
        try{
        	$order_model_ec = $this->ec_connect->table($this->dtb_order);
        } catch (Exception $exception) {
			return [
				'status' =>false,
				'message' => $exception->getMessage()
			];
        }
        // check import dữ liệu
		$query = $order_model_ec;
        $data_import_ec = $query;
		$number_import_success = 0;
        // get order from rimacEC
		$order_eccube = $order_model_ec;
		try{
			$old_import = $import_model->where('id', $import_id)->get()->toArray();
			if($old_import[0]['type'] == 1){
				$data_order_eccube = $order_eccube->selectRaw('dtb_order.*')
										->whereIn('dtb_order.order_id', $list_order)
										->orderBy('dtb_order.order_id', 'asc')->get();
				$data_import_ec = $query->selectRaw('
											dtb_supplier.name as supplier_name,dtb_supplier.zip01 as supplier_zip01 ,dtb_supplier.zip02 as supplier_zip02, dtb_supplier.addr01 as supplier_addr01,
											dtb_supplier.addr02 as supplier_addr02, dtb_supplier.tel01 as supplier_tel01, dtb_supplier.tel02 as supplier_tel02, dtb_supplier.tel03 as supplier_tel03,
											dtb_supplier.cargo_schedule_day, *')
										->join('dtb_shipping', "dtb_shipping.order_id", "dtb_order.order_id")
										->leftJoin('dtb_shipment_item', 'dtb_shipment_item.order_id', 'dtb_order.order_id')
										->join('dtb_order_detail', function($sub){
											$sub->on("dtb_order_detail.order_id", "dtb_order.order_id");
												$sub->whereRaw('case when dtb_shipment_item.order_id is not null then
												dtb_shipment_item.product_class_id = dtb_order_detail.product_class_id and dtb_shipment_item.shipping_id = dtb_shipping.shipping_id
												else true end');
										})
										->leftJoin('dtb_products', 'dtb_order_detail.product_id', 'dtb_products.product_id')
										->leftJoin('dtb_supplier', 'dtb_supplier.supplier_id', 'dtb_products.supplier_id')
										->leftJoin('dtb_products_class', 'dtb_products_class.product_class_id', 'dtb_order_detail.product_class_id')
										->where('dtb_products.del_flg', 0)
										->where('dtb_shipping.del_flg', 0)
										->whereIn('dtb_order.order_id', $list_order)
										->orderBy('dtb_order.order_id', 'asc')
										->get()->toArray();

			}else{
				$site_type_order_mix = $this->conSiteTypeMix(array($old_import[0]['type']));
				$data_order_eccube = $order_model_ec_mix->selectRaw('site_type, order_id, order_date,
															buyer_name1, buyer_name2, buyer_name1_kana, buyer_name2_kana,
															buyer_address_1, buyer_address_2, buyer_email,
															buyer_zip1, buyer_zip2, buyer_tel1, buyer_tel2, buyer_tel3,
															buyer_sex, buyer_birthday,
															order_tax, order_charge, order_sub_total,
															order_discount, order_total, use_point,
															payment_total, payment_id, payment_method,
															comments1, status')
												->where('dtb_order_ecsite_mix_view.site_type', $site_type_order_mix[$old_import[0]['type']])
												->whereIn('dtb_order_ecsite_mix_view.order_id', $list_order)
												->groupBy('site_type', 'order_id', 'order_date',
															'buyer_name1', 'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana',
															'buyer_address_1', 'buyer_address_2', 'buyer_email',
															'buyer_zip1', 'buyer_zip2', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3',
															'buyer_sex', 'buyer_birthday',
															'order_tax', 'order_charge', 'order_sub_total',
															'order_discount', 'order_total', 'use_point',
															'payment_total', 'payment_id', 'payment_method',
															'comments1', 'status')->get()->toArray();
															
				$data_import_ec = $order_detail_model_ec_mix->selectRaw('*')
																	->leftJoin('dtb_products', 'dtb_order_ecsite_mix_view.product_id', 'dtb_products.product_id')
																	->where('dtb_products.del_flg', 0)
																	->where('dtb_order_ecsite_mix_view.site_type', $site_type_order_mix[$old_import[0]['type']])
																	->whereIn('dtb_order_ecsite_mix_view.order_id', $list_order)
																	->orderBy('dtb_order_ecsite_mix_view.order_id', 'asc')->get()->toArray();
			}
		}catch(Exception $e){
			return [
				'status' =>false,
				'message' => $e->getMessage()
			];
		}
		$number_import = $data_order_eccube;
        DB::beginTransaction();
        $list_order = [];
        $data_insert_details = [];
        try {
			$update_import = $import_model;
			$data_insert_details  = $data_import_ec;
			$arr_sup = [];
			$list_order_not_exist = '';
			foreach ($number_import as $key => $value) {
				$check_order_error = 0;
				$num_page_purchase = 0;
				$ship_code_ = 0;
				$duplicate_ship = [];
				$pay_request = 1;
				//Kiểm tra nếu order_code không tồn tại trong Rimac thì đưa vào list r thông báo
				$orders = $order_rimac->where('order_code', $value->order_id)->where('import_id', $import_id)->pluck('id')->toArray();
				if(!empty($orders)){
					if(count($orders) > 1){//Nếu order_code có từ 2 trở lên thì báo lỗi
						$list_order_not_exist .= json_encode(array_unique($orders));
					}
					if($old_import[0]['type'] == 1){
						$fax = $value->order_fax01 . '-' . $value->order_fax02 . '-' . $value->order_fax03;
						$order = [
							'order_date' => $value->create_date,
							'buyer_name1' => $value->order_name01,
							'buyer_name2' => $value->order_name02,
							'buyer_name1_kana' => $value->order_kana01,
							'buyer_name2_kana' => $value->order_kana02,
							'buyer_address_1' => $value->order_addr01,
							'buyer_address_2' => $value->order_addr02,
							'buyer_email' => $value->order_email,
							'buyer_zip1' => $value->order_zip01,
							'buyer_zip2' => $value->order_zip02,
							'buyer_tel1' => $value->order_tel01.$value->order_tel02.$value->order_tel03,
							'buyer_tel2' => '',
							'buyer_tel3' => '',
							'buyer_sex' => $value->order_sex,
							'buyer_birthday' => $value->order_birth,
							'tax' => $value->tax,
							'fax' => ($fax != '--') ? $fax : '',
							'charge' => $value->charge,
							'sub_total' => round($value->subtotal),
							'order_discount' => round($value->discount),
							'order_total' => round($value->total),
							'use_point' => $value->use_point,
							'payment_total' => round($value->payment_total),
							'payment_id' => $value->payment_id,
							'payment_method' => $value->payment_method,
							'status' => ($value->status == 3) ? 7 : 1,
							'comments' => $value->note ,
						];
					}else{
						$order = [
							'order_date' => $value->order_date,
							'buyer_name1' => $value->buyer_name1,
							'buyer_name2' => $value->buyer_name2,
							'buyer_name1_kana' => $value->buyer_name1_kana,
							'buyer_name2_kana' => $value->buyer_name2_kana,
							'buyer_address_1' => $value->buyer_address_1,
							'buyer_address_2' => $value->buyer_address_2,
							'buyer_email' => $value->buyer_email,
							'buyer_zip1' => $value->buyer_zip1,
							'buyer_zip2' => $value->buyer_zip2,
							'buyer_tel1' => $value->buyer_tel1.$value->buyer_tel2.$value->buyer_tel3,
							'buyer_tel2' => '',
							'buyer_tel3' => '',
							'buyer_sex' => $value->buyer_sex,
							'buyer_birthday' => $value->buyer_birthday,
							'tax' => $value->order_tax,
							'charge' => $value->order_charge,
							'sub_total' => $value->order_sub_total,
							'order_discount' => $value->order_discount,
							'order_total' => $value->order_total,
							'use_point' => $value->use_point,
							'payment_total' => $value->payment_total,
							'comments' => $value->comments1,
							'payment_id' => $value->payment_id,
							'payment_method' => $value->payment_method,
							'status' => ($value->status == 3) ? 7 : 1,
						];
					}
					$order_model->where('id', $orders[0])->update($order);
					foreach($data_insert_details as $value_detail){
						if($value_detail->order_id === $value->order_id){
							$check_order_error++;
							$supplier_id = 0;
							$shipment_id = '';
							$add_ship = true;
							if($old_import[0]['type'] == 1){//Import lại những order của website EC
								$tax_class = 0;
								$tax_details = TaxDetail::where('tax_class', $value_detail->tax_class)->orderBy('id')->get()->toArray();
								$tax_detail = [];
								foreach($tax_details as $value_tax){
									if($value_tax['apply_date'] <= $value->create_date){
										$tax_detail = $value_tax;
									}
								}
								$tax_class = ($tax_detail['tax_rate'] / 100);
								$shipment_insert = $import_shipment;
								// đánh số mã đặt hàng
								$page = 0;
								array_push($arr_sup, $value_detail->supplier_id);
								$purchase_date = Carbon::now()->format('Ymd');
								$count_sup = 0;
								foreach($arr_sup as $key => $d){
									if($d == $value_detail->supplier_id  || $value_detail->supplier_id  == ''){
										$count_sup++;
									}
								}
								$page = $count_sup;
								if($value_detail->supplier_id != '')
								{
									if($value_detail->supplier_id < 10)
									{
										$supplier_id = '000'.$value_detail->supplier_id;		
									} else if($value_detail->supplier_id < 100){
										$supplier_id = '00'.$value_detail->supplier_id;
									} else if($value_detail->supplier_id < 1000){
										$supplier_id = '0'.$value_detail->supplier_id;
									}
								}else {
									$supplier_id = '0000';
								}
								$page_ = '';
								if($page < 10)
								{
									$page_ = '000'.$page;
								} else if($page < 100){
									$page_ = '00'.$page;
								} else if($page < 1000){
									$page_ = '0'.$page;
								}
								$purchase_code = $supplier_id."-$purchase_date-$page_";
								// check import chung 1 shipment
								if(!empty($duplicate_ship))
								{
									foreach($duplicate_ship as $ship) {
										if($value_detail->supplier_id == $ship['supplied_id'] && $value_detail->shipping_id == $ship['shipping_id']){
											$shipment_id = $ship['shipment_id'];
											$purchase_code = $ship['purchase_code'];
											$add_ship = false;
											break;
										}
									}
								}
								if(empty($duplicate_ship) || $add_ship == true) {
									$ship_code_++;
									$es_shipdate = $today;
									if(!empty($value_detail->cargo_schedule_day)){
										$cagro_day = intval($value_detail->cargo_schedule_day);
										$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +$cagro_day day"));
										$day_of_week = date('w', strtotime($es_shipdate));
										switch($day_of_week){
											case 0:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +1 day"));
												break;
											case 6:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +2 day"));
												break;
										}
									}
									$data_import_shipment = [
											'delivery_method' => 8,
											'shipment_code' => "その他$ship_code_",
											'delivery_way' => 1,
											'shipment_customer' => $value_detail->shipping_name01.$value_detail->shipping_name02,
											'shipment_address' => $value_detail->shipping_addr01.$value_detail->shipping_addr02,
											'shipment_zip' => $value_detail->shipping_zip01 . '-' . $value_detail->shipping_zip02,
											'shipment_phone' => $value_detail->shipping_tel01. '-'. $value_detail->shipping_tel02. '-'. $value_detail->shipping_tel03,
											'type' => 0,
											'status' => 0,
											'del_flg' => 0,
											'pay_request' => $pay_request,
											'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
											'shipment_fee' => round($value_detail->deliv_fee),
											'shipment_date' => $value_detail->shipping_date,
											'receive_date' =>  $value_detail->shipping_date,
											'es_shipment_date' =>  $es_shipdate, 
											'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
											'shipment_time' => !empty($value_detail->shipping_time) ? $value_detail->shipping_time :'0',
											'es_shipment_time' => !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
									];
									$shipment_insert = $shipment_insert->create($data_import_shipment);// insert shipments table
									$shipment_id = $shipment_insert->id;
									$data_import_shipment['shipment_id'] = $shipment_insert->id;
									array_push($duplicate_ship, [
										'shipment_id' => $shipment_id,//id của bảng shipments hệ thống mới
										'shipping_id' => $value_detail->shipping_id,//id của bảng dtb_shipping
										'supplied_id' => $value_detail->supplier_id,
										'purchase_code' => $purchase_code
										]);
								}
								$cost_price = round($value_detail->cost_price);
								$total_cost_price = $value_detail->quantity * $cost_price;
								$cost_price_tax = $cost_price + round($cost_price * $tax_class);
								$total_cost_price_tax = $total_cost_price + round($total_cost_price * $tax_class);
								$price_sale = round($value_detail->price02);
								$total_price_sale = $value_detail->quantity * $price_sale;
								$price_sale_tax = $price_sale + round($price_sale * $tax_class);
								$total_price_sale_tax = $total_price_sale + round($total_price_sale * $tax_class);
								$purchase_insert = $import_purchase;
								$data_import_purchase = [
										'order_id' => $orders[0],
										'purchase_code' => $purchase_code,
										'supplier_id' =>$value_detail->supplier_id ,
										'purchase_quantity' => $value_detail->quantity,
										'cost_price' =>$value_detail->cost_price,
										'total_cost_price' => ($value_detail->quantity*$value_detail->cost_price),
										'cost_price_tax' => ($value_detail->cost_price * $tax_class) + $value_detail->cost_price,
										'total_cost_price_tax' => $value_detail->quantity*(($value_detail->cost_price * $tax_class) + $value_detail->cost_price),
								];
								$purchase_insert = $purchase_insert->create($data_import_purchase);
								$shipping_option = '';
								if(in_array($value_detail->shipping_option, [0,1,2,3])){
									$shipping_option = $mtb_option[$value_detail->shipping_option];
								}
								$data_order_detail = [
										'order_id' => $orders[0],
										'order_code' => $value_detail->order_id,
										'purchase_id' => $purchase_insert->id,
										'shipment_id' => $shipment_id,
										'product_code' => $value_detail->product_code,
										'sku' => $value_detail->product_code,
										'maker_id' => $value_detail->maker_id,
										'maker_code' => $value_detail->maker_code,
										'product_id' => $value_detail->product_id,
										'product_name' => $value_detail->short_name,
										'quantity' => $value_detail->quantity,
										'price_sale' => $price_sale, // giá bán chưa thuế
										'price_sale_tax'=> $price_sale_tax,  // giá bán có thuế
										'total_price_sale' => $total_price_sale,  // tổng giá bán chưa thuế
										'total_price_sale_tax' => $total_price_sale_tax,  // tổng giá bán có thuế
										'cost_price' => $cost_price,  // nguyên giá chưa thuế
										'total_price' => $total_cost_price, // tổng nguyên giá chưa thuế
										'cost_price_tax' => $cost_price_tax, // nguyên giá có thuế
										'total_price_tax' => $total_cost_price_tax, // tổng nguyên giá có thuế
										'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
										'supplied' => $value_detail->supplier_name == '' ? 'NPO法人クローバープロジェクト21' : $value_detail->supplier_name,
										'supplier_zip1' => $value_detail->supplier_zip01,
										'supplier_zip2' => $value_detail->supplier_zip02,
										'supplier_addr1' => $value_detail->supplier_addr01,
										'supplier_addr2' => $value_detail->supplier_addr02,
										'supplier_tel1' => $value_detail->supplier_tel01,
										'supplier_tel2' => $value_detail->supplier_tel02,
										'supplier_tel3' => $value_detail->supplier_tel03,
										'supplier_code_sagawa' => $value_detail->supplier_code_sagawa,
										'supplier_code_kuroneko' => $value_detail->supplier_code_kuroneko,
										'delivery_method' => 8,
										'ship_name1' => $value_detail->shipping_name01.$value_detail->shipping_name02,
										'ship_name1_kana' => $value_detail->shipping_kana01,
										'ship_name2_kana' => $value_detail->shipping_kana02,
										'ship_phone' => $value_detail->shipping_tel01. '-'. $value_detail->shipping_tel02. '-'. $value_detail->shipping_tel03,
										'ship_zip' => $value_detail->shipping_zip01 . '-' . $value_detail->shipping_zip02,
										'ship_address1' => $value_detail->shipping_addr01,
										'ship_address2' => $value_detail->shipping_addr02,
										'delivery_fee' => $value_detail->deliv_fee,
										'es_delivery_date_from' => $value_detail->shipping_date,
										'delivery_date' => $value_detail->shipping_date,
										'receive_date' =>  $value_detail->shipping_date,
										'receive_time' =>  !empty($value_detail->shipping_time) ? $value_detail->shipping_time :'0',
										'delivery_way' => 1, // defaul cách giao hàng 1
										'pay_request' => $pay_request,
										'gift_wrap' => $shipping_option
								];
								$insert_detail = $order_detail_model->create($data_order_detail);
							}else{//Import lại những order của các website khác
								$tax_details = TaxDetail::where('tax_class', $value_detail->tax_class)->orderBy('id')->get()->toArray();
								$tax_detail = [];
								foreach($tax_details as $value_tax){
									if($value_tax['apply_date'] <= $value->order_date){
										$tax_detail = $value_tax;
									}
								}
								$tax_class = ($tax_detail['tax_rate'] / 100);
								// đánh số mã đặt hàng
								$page = 0;
								array_push($arr_sup, $value_detail->supplier_id);
								$purchase_date = Carbon::now()->format('Ymd');
								$count_sup = 0;
								foreach($arr_sup as $d){
									if($d == $value_detail->supplier_id  || $value_detail->supplier_id  == ''){
										$count_sup++;
									}
								}
								$page = $count_sup;
								if($value_detail->supplier_id != '')
								{
									if($value_detail->supplier_id < 10)
									{
										$supplier_id = '000'.$value_detail->supplier_id;		
									} else if($value_detail->supplier_id < 100){
										$supplier_id = '00'.$value_detail->supplier_id;
									} else if($value_detail->supplier_id < 1000){
										$supplier_id = '0'.$value_detail->supplier_id;
									}
								}else {
									$supplier_id = '0000';
								}
								$page_ = '';
								if($page < 10)
								{
									$page_ = '000'.$page;
								} else if($page < 100){
									$page_ = '00'.$page;
								} else if($page < 1000){
									$page_ = '0'.$page;
								}
								$purchase_code = $supplier_id."-$purchase_date-$page_";
								$shipment_insert = $import_shipment;
								// check import chung 1 shipment
								if(!empty($duplicate_ship))
								{
									foreach($duplicate_ship as $ship)
									{
										if($value_detail->supplier_id == $ship['supplied_id']
												&& $value_detail->ship_address_1.$value_detail->ship_address_2.$value_detail->ship_address_3 == $ship['shipment_address']
												&& ($value_detail->ship_zip1 . $value_detail->ship_zip2 == $ship['shipment_zip']//KH sửa
													|| $value_detail->ship_zip1 . '-' . $value_detail->ship_zip2 == $ship['shipment_zip'])//KH sửa
												&& ($value_detail->delivery_date_from == $ship['shipment_date'] || $ship['shipment_date'] == '')
												&& ($value_detail->delivery_time == $ship['shipment_time'] || $ship['shipment_time'] == '0')
												){
													$shipment_id = $ship['shipment_id'];
													$purchase_code = $ship['purchase_code'];
													$add_ship = false;
													break;
										}
									}
								}
								if(empty($duplicate_ship) || $add_ship == true)
								{
									$ship_code_++;
									$es_shipdate = $today;
									if(!empty($value_detail->cargo_schedule_day)){
										$cagro_day = intval($value_detail->cargo_schedule_day);
										$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +$cagro_day day"));
										$day_of_week = date('w', strtotime($es_shipdate));
										switch($day_of_week){
											case 0:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +1 day"));
												break;
											case 6:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +2 day"));
												break;
										}
									}
									$data_import_shipment = [
											'delivery_method' => 8,
											'shipment_code' => "その他$ship_code_",
											'delivery_way' => 1,
											'shipment_customer' => $value_detail->ship_name1.$value_detail->ship_name2,
											'shipment_address' => $value_detail->ship_address_1.$value_detail->ship_address_2.$value_detail->ship_address_3,
											'shipment_zip' => $value_detail->ship_zip1 . $value_detail->ship_zip2,//KH sửa
											'shipment_phone' => $value_detail->ship_tel1 . $value_detail->ship_tel2 . $value_detail->ship_tel3,//KH sửa
											'type' => 0,
											'status' => 0,
											'del_flg' => 0,
											'pay_request' => $pay_request,
											'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
											'shipment_fee' => $value_detail->deliv_fee,
											'shipment_date' => $value_detail->delivery_date_from,
											'shipment_time' => !empty($value_detail->delivery_time) ? $value_detail->delivery_time :'0',
											'receive_date' =>  $value_detail->delivery_date_from,
											'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
											'es_shipment_date' => $es_shipdate,
											'es_shipment_time' => !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
									];
									$shipment_insert = $shipment_insert->create($data_import_shipment);// insert shipments table
									$shipment_id = $shipment_insert->id;
									$data_import_shipment['shipment_id'] = $shipment_insert->id;
									$data_import_shipment['purchase_code'] = $purchase_code;
									array_push($duplicate_ship, $data_import_shipment);
								}
								$cost_price = round($value_detail->cost_price);
								$total_cost_price = $value_detail->quantity * $cost_price;
								$cost_price_tax = $cost_price + round($cost_price * $tax_class);
								$total_cost_price_tax = $total_cost_price + round($total_cost_price * $tax_class);
								$price_sale = round($value_detail->unit_price);
								$total_price_sale = $value_detail->quantity * $price_sale;
								$price_sale_tax = $price_sale + round($price_sale * $tax_class);
								$total_price_sale_tax = $total_price_sale + round($total_price_sale * $tax_class);
								$purchase_insert = $import_purchase;
								$data_import_purchase = array(
										'order_id' => $orders[0],
										'purchase_code' => $purchase_code,
										'supplier_id' =>$value_detail->supplier_id ,
										'purchase_quantity' => $value_detail->quantity,
										'cost_price' =>$cost_price,
										'total_cost_price' => $total_cost_price,
										'cost_price_tax' => $cost_price_tax,
										'total_cost_price_tax' => $total_cost_price_tax,
								);
								$purchase_insert = $purchase_insert->create($data_import_purchase);
								$data_order_detail = array(
										'order_id' => $orders[0],
										'order_code' => $value_detail->order_id,
										'purchase_id' => $purchase_insert->id,
										'shipment_id' => $shipment_id,
										'product_code' => $value_detail->product_code,
										'sku' => $value_detail->product_code,
										'maker_id' => $value_detail->maker_id,
										'maker_code' => $value_detail->maker_code,
										'product_id' => $value_detail->product_id,
										'product_name' => $value_detail->short_name,
										'quantity' => $value_detail->quantity,
										'price_sale' => $price_sale, // giá bán chưa thuế
										'price_sale_tax'=> $price_sale_tax,  // giá bán có thuế
										'total_price_sale' => $total_price_sale,  // tổng giá bán chưa thuế
										'total_price_sale_tax' => $total_price_sale_tax,  // tổng giá bán có thuế
										'cost_price' => $cost_price,  // nguyên giá chưa thuế
										'total_price' => $total_cost_price, // tổng nguyên giá chưa thuế
										'cost_price_tax' => $cost_price_tax, // nguyên giá có thuế
										'total_price_tax' => $total_cost_price_tax, // tổng nguyên giá có thuế
										'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
										'supplied' => $value_detail->supplier_name == '' ? 'NPO法人クローバープロジェクト21' : $value_detail->supplier_name,
										'supplier_zip1' => $value_detail->supplier_zip01,
										'supplier_zip2' => $value_detail->supplier_zip02,
										'supplier_addr1' => $value_detail->supplier_addr01,
										'supplier_addr2' => $value_detail->supplier_addr02,
										'supplier_tel1' => $value_detail->supplier_tel01,
										'supplier_tel2' => $value_detail->supplier_tel02,
										'supplier_tel3' => $value_detail->supplier_tel03,
										'supplier_code_sagawa' => $value_detail->supplier_code_sagawa,
										'supplier_code_kuroneko' => $value_detail->supplier_code_kuroneko,
										'delivery_method' => 8,
										'ship_name1' => $value_detail->ship_name1.$value_detail->ship_name2,
										'ship_name1_kana' => $value_detail->ship_name1_kana,
										'ship_name2_kana' => $value_detail->ship_name2_kana,
										'ship_phone' => $value_detail->ship_tel1 . $value_detail->ship_tel2 . $value_detail->ship_tel3,//KH sửa
										'ship_zip' => $value_detail->ship_zip1 . $value_detail->ship_zip2,//KH sửa
										'ship_address1' => $value_detail->ship_address_1,
										'ship_address2' => $value_detail->ship_address_2.$value_detail->ship_address_3,
										'delivery_fee' => $value_detail->deliv_fee,
										'es_delivery_date_from' => $value_detail->delivery_date_from,
										'delivery_date' => $value_detail->delivery_date_from,
										'receive_date' =>  $value_detail->delivery_date_from,
										'receive_time' =>  $value_detail->delivery_time,
										'delivery_way' => 1, // defaul cách giao hàng 1
										'pay_request' => $pay_request,
										'gift_wrap' => $value_detail->gift_wrap
								);
								$insert_detail = $order_detail_model->create($data_order_detail);
							}
							$pay_request = 0;
						}
					}
					if($check_order_error == 0){
						$list_order_error .= $value->order_id.',';
						$number_error++;
					}
				}else {
					$list_order_error .= $value->order_id.',';
					$number_error++;
				}
			}
			if($list_order_not_exist != ''){//Báo lỗi order_code multiple
				return [
					'status' => false,
					'message' => 'Multiple order: '.$list_order_not_exist
				];
			}
			$number_import_success = count($number_import) - $number_error;
			$number_success_update = $old_import[0]['number_success'] + $number_import_success;
			if($number_error > 0)
			{
				$data_import_error['list_id'] = $list_order_error;
				$insert_import_error->where('import_id', $import_id)->update($data_import_error);
			}
			$update_import = $update_import->where('id', $import_id)->update(['number_success' => $number_success_update, 'number_error' => $number_error]);
			$insert_HP['process_description'] = '<b>再取り込み:</b><br>ECサイト: '.$website[$old_import[0]['type']].
						'<br>取込時間: '.$old_import[0]['date_import'].'<br>取込件数: '.count($number_import).
						'<br>成功件数: '.$number_import_success.'<br>重複件数: '.$number_duplicate.'<br>エラー件数: '.$number_error;
			$hisProcess->create($insert_HP);
            DB::commit();
            return [
				'status' => true,
				'message' => ''
			];
		}catch (Exception $exception)
		{
			DB::rollBack();
			Log::debug($exception->getMessage());
			return [
				'status' => false,
				'message' => 'SQL error.'
			];
		}

    }
    /**
     * POSTからsite_typeの情報を取得し、
	 * site_typeの配列と、
	 * ひろしまグルメを除いたsite_type_mixを返す
     * @author hamasaki
     * 2020/02/07
     */
    public function getSiteType(){
    	$site_type = $_POST['site_type'];

    	$site_type_mix = [];
    	foreach($site_type as $site_type_id){
    		if($site_type_id <> 1) array_push($site_type_mix, $site_type_id);
    	}

    	return [$site_type, $site_type_mix];
    }

    /**
     * ordersのsite_typeのIDを
     * dtb_order_ecsite_mix_viewのsite_typeのIDに変換して返す
     * @author hamasaki
     * 2020/02/12
     */
    public function conSiteTypeMix($site_type){
    	$site_type_mix = [];
    	foreach($site_type as $site_type_id){
    		switch($site_type_id){
    			case 1:
    				$site_type_mix[$site_type_id] = 0;	// 自社
    				break;
    			case 2:
    				$site_type_mix[$site_type_id] = 2;	// 楽天
    				break;
    			case 3:
    				$site_type_mix[$site_type_id] = 8;	// Yahoo
    				break;
    			case 4:
    				$site_type_mix[$site_type_id] = 5;	// Amazonひろしま
    				break;
    			case 5:
    				$site_type_mix[$site_type_id] = 6;	// Amazonワールド
    				break;
    			case 6:
    				$site_type_mix[$site_type_id] = 14;	// AmazonひろしまFBA
    				break;
    			case 7:
    				$site_type_mix[$site_type_id] = 10;	// AmazonワールドFBA
    				break;
    			case 8:
    				$site_type_mix[$site_type_id] = 11;	// Amazonリカー
    				break;
    		}
    	}

    	return $site_type_mix;
    }

    /**
     * dtb_order_ecsite_mix_viewに繋がるか確認し、
	 * 繋がったらテーブルの設定を返す。
	 * 繋がらなかった場合はfalseを返す。
     * @author hamasaki
     * 2020/02/07
     */
    public function setDtbMixTable(){
        $this->ec_connect = DB::connection('eccube');
    	// dtb_order_ecsite_mix_viewの接続チェック
    	$this->dtb_order_ecsite_mix = 'dtb_order_ecsite_mix_view';
    	try
    	{
    		$order_model_ec_mix = $this->ec_connect->table($this->dtb_order_ecsite_mix);
    	} catch (Exception $exception) {
    		return false;
    	}
    	return $order_model_ec_mix;
    }

    /**
     * dtb_order_ecsite_mix_viewから、
     * 指定された期間、
	 * かつ指定されたサイト(ひろしまグルメは除く)の
     * 受注データ配列を返す。
     * @author hamasaki
     * 2020/02/07
     */
    public function getDtbMixOrder($date_from = null, $date_to = null, $site_type = array()){
    	// $site_typeにひろしまグルメのID（１）が含まれていた場合、除外する
    	$site_type_mix = [];
    	foreach($site_type as $site_type_id){
    		if($site_type_id <> 1) array_push($site_type_mix, $site_type_id);
    	}

    	// dtb_order_ecsite_mix_view用にsite_typeを変換する
    	$site_type_order_mix = $this->conSiteTypeMix($site_type_mix);

    	// 指定された条件の受注データを取得し、返す
        $order_model_ec_mix = $this->setDtbMixTable();
        $data_order_mix = $order_model_ec_mix->selectRaw('site_type, order_id, order_date,
								buyer_name1, buyer_name2, buyer_name1_kana, buyer_name2_kana,
								buyer_address_1, buyer_address_2, buyer_email,
								buyer_zip1, buyer_zip2, buyer_tel1, buyer_tel2, buyer_tel3,
								buyer_sex, buyer_birthday,
								order_tax, order_charge, order_sub_total,
								order_discount, order_total, use_point,
								payment_total, payment_id, payment_method,
								comments1, status')
        						->whereBetween('order_date',[$date_from." 00:00:00", $date_to." 23:59:59"])
                                ->whereIn('site_type' , $site_type_order_mix)
								->groupBy('site_type', 'order_id', 'order_date',
											'buyer_name1', 'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana',
											'buyer_address_1', 'buyer_address_2', 'buyer_email',
											'buyer_zip1', 'buyer_zip2', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3',
											'buyer_sex', 'buyer_birthday',
											'order_tax', 'order_charge', 'order_sub_total',
											'order_discount', 'order_total', 'use_point',
											'payment_total', 'payment_id', 'payment_method',
											'comments1', 'status')
        						->orderBy('order_id', 'asc')
								->get();
        return $data_order_mix;
    }

    /**
     * dtb_order_ecsite_mix_viewのうち、
     * 指定された期間、
	 * かつ、指定されたサイト(ひろしまグルメは除く)
	 * かつ、まだordersに取込んでいない
     * 受注データ配列を返す。
	 * また、指定された期間、指定されたサイトには該当するが、
	 * ordersには取込済みの受注データに関しては文字列で返す。
     * @author hamasaki
     * 2020/02/07
     */
    public function getInportDtbMixOrder($date_from = null, $date_to = null, $site_type = array()){
    	// $site_typeにひろしまグルメのID（１）が含まれていた場合、除外する
    	$site_type_mix = [];
    	foreach($site_type as $site_type_id){
    		if($site_type_id <> 1) array_push($site_type_mix, $site_type_id);
    	}

    	// ordersの対象受注データIDを取得する
    	$order_model = new Order();
    	$data_rimac_order   = $order_model->selectRaw('orders.order_code as order_id')
    									->join('imports', 'orders.import_id', '=', 'imports.id')
    									->whereIn('imports.type' , $site_type_mix)
										->whereBetween('orders.order_date', [$date_from." 00:00:00", $date_to." 23:59:59"])
										->orderBy('orders.order_code')
    									->get();
    	// dtb_ordr_ecsite_mix_viewの対象受注データIDを取得する
		$data_order_mix = $this->getDtbMixOrder($date_from, $date_to, $site_type_mix);

    	// 重複していないIDはorder_validateに配列
    	// 重複していたIDはorder_duplicateに文字列で保存
    	$order_validate = [];
    	$order_duplicate = '';
        foreach($data_order_mix as $value_ec)
        {
            $import_valid = true;
            foreach ($data_rimac_order as $value_rm)
            {
                if($value_ec->order_id == $value_rm->order_id)
                {
					$import_valid = false;
					break;
                }
            }
            if($import_valid == true)
            {
                array_push($order_validate, $value_ec->order_id);
            }elseif($import_valid == false)
            {
                $order_duplicate .="$value_ec->order_id 、";
            }
        }

    	return [$order_validate, $order_duplicate];
    }

    /**
     * dtb_order_ecsite_mix_viewのデータを
	 * orders,order_detailsに取込む
	 * 問題なく取込めたらtrue、
	 * 問題が発生した場合はfalseを返す
     * @author hamasaki
     * 2020/02/07
     */
    public function inportMixOrderData($date_from = null, $date_to = null, $site_type_mix = array(), $order_mix_validate = array()){
    	try{
			// dtb_order_ecsite_mix_view用にsite_typeを変換する
			$hisProcess = $this->historyProcessModel;
			$insert_HP = array();
			$insert_HP['process_user'] = auth()->user()->login_id;
			$insert_HP['process_permission'] = auth()->user()->type;
			$insert_HP['process_screen'] = '取込設定';
    		$site_type_order_mix = $this->conSiteTypeMix($site_type_mix);
			$today = Carbon::today()->format('Y-m-d 05:59:59');
			$website = config('constants.WEB_TYPE');
    		foreach($site_type_mix as $site_type_id)
    		{
		    	// 毎回初期化（初期化させないと中身が残ったままになる・・・
		        $order_model = new Order();
		    	$import_shipment = new Shipment();
		    	$import_purchase =  new Purchase();
		    	$import_model = new Import();
		    	$order_detail_model = new OrderDetail();
		    	$date_import = new Carbon();
				$insert_import_error = new ImportError();
		    	$data_import = [];
		    	$order_model_ec_mix = $this->setDtbMixTable();
		    	$order_detail_model_ec_mix = $this->setDtbMixTable();
				$list_order = [];
				$insert_import = $import_model;
				$update_import = $import_model;
				$number_error = 0;
				$number_import_success = 0;
				$arr_mix_duplicate = [];
				$ec_order_date = [];

		    	// 対象となるorder_idの情報を渡されなかった場合、他の情報から対象IDを取得
				$order_mix_duplicate = '';
		    	if(empty($order_mix_validate)){
		    		list($order_mix_validate, $order_mix_duplicate) = $this->getInportDtbMixOrder($date_from, $date_to, $site_type_mix);
				}
				$ec_order_date = $order_model_ec_mix->selectRaw('site_type, order_id, order_date,
															buyer_name1, buyer_name2, buyer_name1_kana, buyer_name2_kana,
															buyer_address_1, buyer_address_2, buyer_email,
															buyer_zip1, buyer_zip2, buyer_tel1, buyer_tel2, buyer_tel3,
															buyer_sex, buyer_birthday,
															order_tax, order_charge, order_sub_total,
															order_discount, order_total, use_point,
															payment_total, payment_id, payment_method,
															comments1, status')
												->whereBetween('dtb_order_ecsite_mix_view.order_date',[$date_from." 00:00:00", $date_to." 23:59:59"])
												->where('dtb_order_ecsite_mix_view.site_type', $site_type_order_mix[$site_type_id]);
				if(!empty($order_mix_validate)){
					$ec_order_date = $ec_order_date->whereIn('dtb_order_ecsite_mix_view.order_id', $order_mix_validate);
				}
				$ec_order_date = $ec_order_date->groupBy('site_type', 'order_id', 'order_date',
															'buyer_name1', 'buyer_name2', 'buyer_name1_kana', 'buyer_name2_kana',
															'buyer_address_1', 'buyer_address_2', 'buyer_email',
															'buyer_zip1', 'buyer_zip2', 'buyer_tel1', 'buyer_tel2', 'buyer_tel3',
															'buyer_sex', 'buyer_birthday',
															'order_tax', 'order_charge', 'order_sub_total',
															'order_discount', 'order_total', 'use_point',
															'payment_total', 'payment_id', 'payment_method',
															'comments1', 'status')
												->get()
												->toArray();
				$data_import = [
					'type' => $site_type_id,
					'date_import' => $date_import,
					'website' => 'EC',
					'number_order' => count($ec_order_date),
					'import_set_from' => $date_from,
					'import_set_to' => $date_to
					];
				$insert_import = $insert_import->create($data_import);
				$data_import_error['import_id'] = $insert_import->id;
				if($order_mix_duplicate == ''){
					$ec_order_detail_date = $order_detail_model_ec_mix->selectRaw('*')
																->leftJoin('dtb_products', 'dtb_order_ecsite_mix_view.product_id', 'dtb_products.product_id')
																->where('dtb_products.del_flg', 0)
																->whereBetween('dtb_order_ecsite_mix_view.order_date',[$date_from." 00:00:00", $date_to." 23:59:59"])
																->where('dtb_order_ecsite_mix_view.site_type', $site_type_order_mix[$site_type_id])
																->whereIn('dtb_order_ecsite_mix_view.order_id', $order_mix_validate)
																->orderBy('dtb_order_ecsite_mix_view.order_id', 'asc')
																->get()
																->toArray();
					$data_insert_details  = $order_model_ec_mix;
					$arr_sup = [];
					$list_order_error = '';
					foreach ($ec_order_date as $key => $value)
					{
						$order = [];
						$num_page_purchase = 0;
						$ship_code_ = 0;
						$duplicate_ship = [];
						$pay_request = 1;
						$check_order_error = 0;
						$order = [
								'site_type' => $site_type_id,
								'import_id' => $insert_import->id,
								'order_code' => $value->order_id,
								'order_date' => $value->order_date,
								'buyer_name1' => $value->buyer_name1,
								'buyer_name2' => $value->buyer_name2,
								'buyer_name1_kana' => $value->buyer_name1_kana,
								'buyer_name2_kana' => $value->buyer_name2_kana,
								'buyer_address_1' => $value->buyer_address_1,
								'buyer_address_2' => $value->buyer_address_2,
								'buyer_email' => $value->buyer_email,
								'buyer_zip1' => $value->buyer_zip1,
								'buyer_zip2' => $value->buyer_zip2,
								'buyer_tel1' => $value->buyer_tel1.$value->buyer_tel2.$value->buyer_tel3,
								'buyer_tel2' => '',
								'buyer_tel3' => '',
								'buyer_sex' => $value->buyer_sex,
								'buyer_birthday' => $value->buyer_birthday,
								'tax' => $value->order_tax,
								'charge' => $value->order_charge,
								'sub_total' => $value->order_sub_total,
								'order_discount' => $value->order_discount,
								'order_total' => $value->order_total,
								'use_point' => $value->use_point,
								'payment_total' => $value->payment_total,
								'comments' => $value->comments1,
								'payment_id' => $value->payment_id,
								'payment_method' => $value->payment_method,
								'status' => ($value->status == 3) ? 7 : 1,
								'flag_confirm' => 0, // default không có
								];
						$insert_order = $order_model->create($order);
						foreach($ec_order_detail_date as $value_detail)
						{
							if($value_detail->order_id === $value->order_id)
							{
								$check_order_error++;
								$supplier_id = 0;
								$shipment_id = '';
								$add_ship = true;
								$tax_class = 0;
								$tax_details = TaxDetail::where('tax_class', $value_detail->tax_class)->orderBy('id')->get()->toArray();
								$tax_detail = [];
								foreach($tax_details as $value_tax){
									if($value_tax['apply_date'] <= $value->order_date){
										$tax_detail = $value_tax;
									}
								}
								$tax_class = ($tax_detail['tax_rate'] / 100);
								// đánh số mã đặt hàng
								$page = 0;
								array_push($arr_sup, $value_detail->supplier_id);
								$purchase_date = Carbon::now()->format('Ymd');
								$count_sup = 0;
								foreach($arr_sup as $d){
									if($d == $value_detail->supplier_id  || $value_detail->supplier_id  == ''){
										$count_sup++;
									}
								}
								$page = $count_sup;
								if($value_detail->supplier_id != '')
								{
									if($value_detail->supplier_id < 10)
									{
										$supplier_id = '000'.$value_detail->supplier_id;		
									} else if($value_detail->supplier_id < 100){
										$supplier_id = '00'.$value_detail->supplier_id;
									} else if($value_detail->supplier_id < 1000){
										$supplier_id = '0'.$value_detail->supplier_id;
									}
								}else {
									$supplier_id = '0000';
								}
								$page_ = '';
								if($page < 10)
								{
									$page_ = '000'.$page;
								} else if($page < 100){
									$page_ = '00'.$page;
								} else if($page < 1000){
									$page_ = '0'.$page;
								}
								$purchase_code = $supplier_id."-$purchase_date-$page_";
								$shipment_insert = $import_shipment;
								// check import chung 1 shipment
								if(!empty($duplicate_ship))
								{
									foreach($duplicate_ship as $ship)
									{
										if($value_detail->supplier_id == $ship['supplied_id']
												&& $value_detail->ship_address_1.$value_detail->ship_address_2.$value_detail->ship_address_3 == $ship['shipment_address']
												&& ($value_detail->ship_zip1 . $value_detail->ship_zip2 == $ship['shipment_zip']//KH sửa
													|| $value_detail->ship_zip1 . '-' . $value_detail->ship_zip2 == $ship['shipment_zip'])//KH sửa
												&& ($value_detail->delivery_date_from == $ship['shipment_date'] || $ship['shipment_date'] == '')
												&& ($value_detail->delivery_time == $ship['shipment_time'] || $ship['shipment_time'] == '0')
												){
													$shipment_id = $ship['shipment_id'];
													$purchase_code = $ship['purchase_code'];
													$add_ship = false;
													break;
										}
									}
								}
								if(empty($duplicate_ship) || $add_ship == true)
								{
									$ship_code_++;
									$es_shipdate = $today;
									if(!empty($value_detail->cargo_schedule_day)){
										$cagro_day = intval($value_detail->cargo_schedule_day);
										$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +$cagro_day day"));
										$day_of_week = date('w', strtotime($es_shipdate));
										switch($day_of_week){
											case 0:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +1 day"));
												break;
											case 6:
												$es_shipdate = date('Y-m-d', strtotime($es_shipdate." +2 day"));
												break;
										}
									}
									$data_import_shipment = [
											'delivery_method' => 8,
											'shipment_code' => "その他$ship_code_",
											'delivery_way' => 1,
											'shipment_customer' => $value_detail->ship_name1.$value_detail->ship_name2,
											'shipment_address' => $value_detail->ship_address_1.$value_detail->ship_address_2.$value_detail->ship_address_3,
											'shipment_zip' => $value_detail->ship_zip1 . $value_detail->ship_zip2,//KH sửa
											'shipment_phone' => $value_detail->ship_tel1 . $value_detail->ship_tel2 . $value_detail->ship_tel3,//KH sửa
											'type' => 0,
											'status' => 0,
											'del_flg' => 0,
											'pay_request' => $pay_request,
											'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
											'shipment_fee' => $value_detail->deliv_fee,
											'shipment_date' => $value_detail->delivery_date_from,
											'shipment_time' => !empty($value_detail->delivery_time) ? $value_detail->delivery_time :'0',
											'receive_date' =>  $value_detail->delivery_date_from,
											'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
											'es_shipment_date' => $es_shipdate,
											'es_shipment_time' => !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
									];
									$shipment_insert = $shipment_insert->create($data_import_shipment);// insert shipments table
									$shipment_id = $shipment_insert->id;
									$data_import_shipment['shipment_id'] = $shipment_insert->id;
									$data_import_shipment['purchase_code'] = $purchase_code;
									array_push($duplicate_ship, $data_import_shipment);
								}
								$cost_price = round($value_detail->cost_price);
								$total_cost_price = $value_detail->quantity * $cost_price;
								$cost_price_tax = $cost_price + round($cost_price * $tax_class);
								$total_cost_price_tax = $total_cost_price + round($total_cost_price * $tax_class);
								$price_sale = round($value_detail->unit_price);
								$total_price_sale = $value_detail->quantity * $price_sale;
								$price_sale_tax = $price_sale + round($price_sale * $tax_class);
								$total_price_sale_tax = $total_price_sale + round($total_price_sale * $tax_class);
								$purchase_insert = $import_purchase;
								$data_import_purchase = array(
										'order_id' => $insert_order->id,
										'purchase_code' => $purchase_code,
										'supplier_id' =>$value_detail->supplier_id ,
										'purchase_quantity' => $value_detail->quantity,
										'cost_price' =>$cost_price,
										'total_cost_price' => $total_cost_price,
										'cost_price_tax' => $cost_price_tax,
										'total_cost_price_tax' => $total_cost_price_tax,
								);
								$purchase_insert = $purchase_insert->create($data_import_purchase);
								$data_order_detail = array(
										'order_id' => $insert_order->id,
										'order_code' => $value_detail->order_id,
										'purchase_id' => $purchase_insert->id,
										'shipment_id' => $shipment_id,
										'product_code' => $value_detail->product_code,
										'sku' => $value_detail->product_code,
										'maker_id' => $value_detail->maker_id,
										'maker_code' => $value_detail->maker_code,
										'product_id' => $value_detail->product_id,
										'product_name' => $value_detail->short_name,
										'quantity' => $value_detail->quantity,
										'price_sale' => $price_sale, // giá bán chưa thuế
										'price_sale_tax'=> $price_sale_tax,  // giá bán có thuế
										'total_price_sale' => $total_price_sale,  // tổng giá bán chưa thuế
										'total_price_sale_tax' => $total_price_sale_tax,  // tổng giá bán có thuế
										'cost_price' => $cost_price,  // nguyên giá chưa thuế
										'total_price' => $total_cost_price, // tổng nguyên giá chưa thuế
										'cost_price_tax' => $cost_price_tax, // nguyên giá có thuế
										'total_price_tax' => $total_cost_price_tax, // tổng nguyên giá có thuế
										'supplied_id' => $value_detail->supplier_id == '' ? 0 : $value_detail->supplier_id,
										'supplied' => $value_detail->supplier_name == '' ? 'NPO法人クローバープロジェクト21' : $value_detail->supplier_name,
										'supplier_zip1' => $value_detail->supplier_zip01,
										'supplier_zip2' => $value_detail->supplier_zip02,
										'supplier_addr1' => $value_detail->supplier_addr01,
										'supplier_addr2' => $value_detail->supplier_addr02,
										'supplier_tel1' => $value_detail->supplier_tel01,
										'supplier_tel2' => $value_detail->supplier_tel02,
										'supplier_tel3' => $value_detail->supplier_tel03,
										'supplier_code_sagawa' => $value_detail->supplier_code_sagawa,
										'supplier_code_kuroneko' => $value_detail->supplier_code_kuroneko,
										'delivery_method' => 8,
										'ship_name1' => $value_detail->ship_name1.$value_detail->ship_name2,
										'ship_name1_kana' => $value_detail->ship_name1_kana,
										'ship_name2_kana' => $value_detail->ship_name2_kana,
										'ship_phone' => $value_detail->ship_tel1 . $value_detail->ship_tel2 . $value_detail->ship_tel3,//KH sửa
										'ship_zip' => $value_detail->ship_zip1 . $value_detail->ship_zip2,//KH sửa
										'ship_address1' => $value_detail->ship_address_1,
										'ship_address2' => $value_detail->ship_address_2.$value_detail->ship_address_3,
										'delivery_fee' => $value_detail->deliv_fee,
										'es_delivery_date_from' => $value_detail->delivery_date_from,
										'delivery_date' => $value_detail->delivery_date_from,
										'receive_date' =>  $value_detail->delivery_date_from,
										'receive_time' =>  $value_detail->delivery_time,
										'delivery_way' => 1, // defaul cách giao hàng 1
										'pay_request' => $pay_request,
										'gift_wrap' => $value_detail->gift_wrap
								);
								$insert_detail = $order_detail_model->create($data_order_detail);
								$pay_request = 0;
							}
						}
						array_push($list_order, $order);
						if($check_order_error == 0){
							$list_order_error .= $value->order_id.',';
							$number_error++;
						}
					}
					if($number_error > 0)
					{
						$data_import_error['list_id'] = $list_order_error;
						$insert_import_error->create($data_import_error);
					}
					$number_import_success = count($list_order) - $number_error;
					$update_import = $update_import->where('id', $insert_import->id)->update(['number_success' => $number_import_success, 'number_duplicate' => 0, 'number_error' => $number_error, 'import_set_from' => $date_from, 'import_set_to' => $date_to]);
				}else {
					$order_mix_duplicate = rtrim($order_mix_duplicate, ' 、');
					$arr_mix_duplicate = explode(' 、', $order_mix_duplicate);
					$update_import = $update_import->where('id', $insert_import->id)->update(['number_success' => $number_import_success, 'number_duplicate' => count($arr_mix_duplicate), 'number_error' => 0, 'import_set_from' => $date_from, 'import_set_to' => $date_to]);
				}
				$insert_HP['process_description'] = '<b>受注取込:</b><br>ECサイト: '.$website[$site_type_id].'<br>取込件数: '.(count($ec_order_date)).'<br>成功件数: '.$number_import_success.'<br>重複件数: '.(count($arr_mix_duplicate)).'<br>エラー件数: '.$number_error;
				$hisProcess->create($insert_HP);
    		}
    	}catch (Exception $exception){
			Log::debug($exception->getMessage());
    		return false;
    	}
    	return true;
    }

}