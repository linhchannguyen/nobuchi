<?php
namespace App\Repositories\Services\ECImport;

use App\Model\Imports\Import;
// use App\Model\Orders\ImportEccube;
use App\Model\Orders\Order;
use App\Model\Groups\Group;
use App\Model\Orders\ImportError;
use App\Model\Orders\OrderDetail;
use App\Model\Products\ProductStatus;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use App\Model\Suppliers\Supplier;
use App\Model\Taxs\TaxDetail;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ECImportService implements ECImportServiceContract
{
    private $dtb_order;
    private $order_model;
    private $ec_connect;


    public function __construct()
    {
        $this->dtb_order = 'dtb_order';
        $this->ec_connect = DB::connection('eccube');
        try
        {
            $this->order_model= $this->ec_connect->table($this->dtb_order);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
    public function importOrder()
    {
            $date_import = new Carbon();
            $today = Carbon::today()->format('Y-m-d 05:59:59');
            $yesterday = Carbon::yesterday()->format('Y-m-d 06:00:00');
            $insert_order = '';
            $data_import = [];
            $order_model = new Order();
            $order_detail_model = new OrderDetail();
            $import_model = new Import();
            // $imporeccube = new ImportEccube();
            $import_shipment = new Shipment();
            $import_purchase =  new Purchase();
            $insert_import_error =  new ImportError();
            // check import dữ liệu
            $check_import = $import_model->selectRaw('id')
                                ->whereDate('date_import', $date_import)
                                ->where('type', 1)
                                ->where('website', 'EC')
                                ->get()->toArray();
            if(!empty($check_import))
            {
                return [
                    'status' => false,
                    'message' => 'import duplicate'
                ];
            }
            $query = $this->order_model;
            $data_import_ec = $query;
            $order_data_ec = $query;
            $number_import = 0;
            $number_import_success = 0;
            $data_import_ec = $query->selectRaw('
            dtb_supplier.name as supplier_name,dtb_supplier.zip01 as supplier_zip01
            ,dtb_supplier.zip02 as supplier_zip02, dtb_supplier.addr01 as supplier_addr01, dtb_supplier.addr02 as supplier_addr02
            ,dtb_supplier.tel01 as supplier_tel01, dtb_supplier.tel02 as supplier_tel02, dtb_supplier.tel03 as supplier_tel03,
            dtb_products.maker_id, dtb_products.maker_code,*')
            ->join('dtb_order_detail','dtb_order.order_id', 'dtb_order_detail.order_id')
            ->leftJoin('dtb_products', 'dtb_order_detail.product_id', 'dtb_products.product_id')
            ->leftJoin('dtb_supplier', 'dtb_supplier.supplier_id', 'dtb_products.supplier_id')
            ->leftJoin('dtb_shipping', 'dtb_order.order_id', 'dtb_shipping.order_id')
            ->leftJoin('dtb_products_class', 'dtb_products_class.product_class_id', 'dtb_order_detail.product_class_id')
            ->where('dtb_products.del_flg', 0)
            ->where('dtb_shipping.del_flg', 0)
            ->whereBetween('dtb_order.create_date',[$yesterday, $today])
            ->orderBy('dtb_order.order_id', 'asc')
            ->get()->toArray();
            $number_import = $data_import_ec;
            $number_import_error = 0;
            $data_import_error = [];
            $list_order_error = '';
            if(count($number_import) == 0)
            {
                Log::info('EC-CUBE Order empty');
                return [
                    'status' => false,
                    'message' => 'Data empty'
                ];
            }

            // return  $data_import_ec ;
            DB::beginTransaction();
            $list_order = [];
            $data_insert_details = [];
            try {
                $insert_import = $import_model;
                $update_import = $import_model;
                $data_insert_details  = $data_import_ec ;
                $data_import = [
                    'type' => 1,
                    'date_import' => $date_import,//$data_import
                    'website' => 'EC',
                    'number_order' => count($number_import)
                ];
            // set data insert into import_eccubes
            // foreach($data_import_ec as $value)
            // {
            //         $ec_import = [
            //             'order_id' =>  $value->order_id, 
            //             'order_temp_id' =>  $value->order_temp_id, 
            //             'customer_id' =>  $value->customer_id, 
            //             'message' =>  $value->message, 
            //             'classcategory_name1' =>  $value->classcategory_name1, 
            //             'classcategory_name2' =>  $value->classcategory_name2, 
            //             'product_name' =>  $value->short_name,
            //             'product_id' =>  $value->product_id, 
            //             'product_class_id' =>  $value->product_class_id, 
            //             'product_code' =>  $value->product_code, 
            //             'quantity' =>  $value->quantity, 
            //             'price' =>  $value->price, 
            //             'point_rate' =>  $value->point_rate,
            //             'order_name01' =>  $value->order_name01, 
            //             'order_name02' =>  $value->order_name02,
            //             'order_kana01' =>  $value->order_kana01, 
            //             'order_kana02' =>  $value->order_kana02, 
            //             'order_email' =>  $value->order_email, 
            //             'order_tel01' =>  $value->order_tel01, 
            //             'order_tel02' =>  $value->order_tel02, 
            //             'order_tel03' =>  $value->order_tel03, 
            //             'order_fax01' =>  $value->order_fax01, 
            //             'order_fax02' =>  $value->order_fax02, 
            //             'order_fax03' =>  $value->order_fax03, 
            //             'order_zip01' =>  $value->order_zip01, 
            //             'order_zip02' =>  $value->order_zip02, 
            //             'order_pref' =>  $value->order_pref, 
            //             'order_addr01' =>  $value->order_addr01, 
            //             'order_addr02' =>  $value->order_addr02, 
            //             'order_sex' =>  $value->order_sex, 
            //             'order_birth' =>  $value->order_birth, 
            //             'order_job' =>  $value->order_job, 
            //             'subtotal' =>  $value->subtotal, 
            //             'discount' =>  $value->discount, 
            //             'deliv_id' =>  $value->deliv_id, 
            //             'deliv_fee' =>  $value->deliv_fee, 
            //             'charge' =>  $value->charge, 
            //             'use_point' =>  $value->use_point, 
            //             'add_point' =>  $value->add_point, 
            //             'birth_point' =>  $value->birth_point, 
            //             'tax' =>  $value->tax, 
            //             'total' =>  $value->total, 
            //             'payment_total' =>  $value->payment_total,
            //             'payment_id' =>  $value->payment_id, 
            //             'payment_method' =>  $value->payment_method, 
            //             'note' =>  $value->note, 
            //             'status' => $value->status,
            //             'commit_date' =>  $value->commit_date, 
            //             'payment_date' =>  $value->payment_date, 
            //             'device_type_id' =>  $value->device_type_id, 
            //             'del_flg' =>  $value->del_flg, 
            //             'memo01' =>  $value->memo01, 
            //             'memo02' =>  $value->memo02, 
            //             'memo03' =>  $value->memo03, 
            //             'memo04' =>  $value->memo04, 
            //             'memo05' =>  $value->memo05, 
            //             'memo06' =>  $value->memo06, 
            //             'memo07' =>  $value->memo07, 
            //             'memo08' =>  $value->memo08, 
            //             'memo09' =>  $value->memo09, 
            //             'memo10' =>  $value->memo10, 
            //             'order_type_id' =>  $value->order_type_id, 
            //             // 'coupon_id' =>  $value->coupon_id, 
            //             // 'discount_coupon' =>  $value->discount_coupon
            //     ];
            //     $imporeccube = $imporeccube->create($ec_import);
            // }
            $insert_import = $insert_import->create($data_import);
            $data_import_error['import_id'] = $insert_import->id;
            foreach ($number_import as $key => $value) {
                $arr_sup = [];
                $arr_page = [];
                $order = [];
                $num_page_purchase = 0;
                $ship_code_ = 0;
                $duplicate_ship = [];
                $pay_request = 1;
                if($value->order_id == ''
                 || ($value->order_name01 == '' & $value->order_name02 == '' & $value->order_kana01 == '' & $value->order_kana01 == '' )
                 || $value->total == ''
                 )
                {
                    $list_order_error = $value->order_id.',';
                    $number_import_error++;
                    continue;
                }
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
                    'buyer_tel1' => $value->order_tel01, 
                    'buyer_tel2' => $value->order_tel02, 
                    'buyer_tel3' => $value->order_tel03, 
                    'buyer_sex' => $value->order_sex, 
                    'buyer_birthday' => $value->order_birth, 
                    'tax' => $value->tax,
                    'fax' => ($fax != '--') ? $fax : '',
                    'charge' => $value->charge, 
                    'sub_total' => $value->subtotal, 
                    'order_discount' => $value->discount, 
                    'order_total' => $value->total, 
                    'use_point' => $value->use_point, 
                    'payment_total' => $value->payment_total, 
                    'payment_id' => $value->payment_id, 
                    'payment_method' => $value->payment_method, 
                    'status' => ($value->status == 3) ? 7 : 1,
                    'flag_confirm' => 0, // default không có 
                    'comments' => $value->note , 
                ];
                $insert_order = $order_model->create($order);
                foreach($data_insert_details as $value_detail)
                {
                    if($value_detail->order_id === $value->order_id)
                    {
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
                        $tax_class = ($tax_detail['tax_rate'] / 100);
                        $shipment_insert = $import_shipment;
                        // check import chung 1 shipment
                        if(!empty($duplicate_ship))
                        {
                            foreach($duplicate_ship as $ship) {
                                if($value_detail->supplier_id == $ship['supplied_id']
                                && $value_detail->shipping_addr01.$value_detail->shipping_addr02 == $ship['shipment_address']
                                && $value_detail->shipping_zip01 . '-' . $value_detail->shipping_zip02 == $ship['shipment_zip']
                                && ($value_detail->shipping_date == $ship['shipment_date'] || $value_detail->shipping_date == null)
                                && ($value_detail->shipping_time == $ship['shipment_time'] || $value_detail->shipping_time == null)
                                ){
                                    $shipment_id = $ship['shipment_id'];
                                    $add_ship = false;
                                break;
                                }
                            }
                        }
                        if(empty($duplicate_ship) || $add_ship == true) {
                            $ship_code_++;
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
                                'shipment_fee' => $value_detail->deliv_fee, 
                                'shipment_date' => $value_detail->shipping_date,
                                'receive_date' =>  $value_detail->shipping_date, 
                                'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
                                'shipment_time' => '午前中',
                                'es_shipment_time' => !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
                            ];
                            $shipment_insert = $shipment_insert->create($data_import_shipment);// insert shipments table
                            $shipment_id = $shipment_insert->id;
                            $data_import_shipment['shipment_id'] = $shipment_insert->id;
                            array_push($duplicate_ship, $data_import_shipment);
                        }
                        $purchase_insert = $import_purchase;
                        // đánh số mã đặt hàng
                        $page = 1;
                        array_push($arr_sup, $value_detail->supplier_id);
                        $purchase_date = Carbon::now()->format('Ymd');
                        if(count($arr_sup) > 0)
                        {
                            foreach($arr_sup as $key => $d){
                                if($d == $value_detail->supplier_id  || $value_detail->supplier_id  == ''){
                                    $page++;
                                }
                            }
                        }    
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
                        }
                        $data_import_purchase = [
                            'order_id' => $insert_order->id,
                            'purchase_code' => $supplier_id."-$purchase_date-0$page",
                            'supplier_id' =>$value_detail->supplier_id ,
                            'purchase_quantity' => $value_detail->quantity,
                            'cost_price' =>$value_detail->cost_price,
                            'total_cost_price' => ($value_detail->quantity*$value_detail->cost_price),
                            'cost_price_tax' => ($value_detail->cost_price * $tax_class) + $value_detail->cost_price,
                            'total_cost_price_tax' => $value_detail->quantity*(($value_detail->cost_price * $tax_class) + $value_detail->cost_price),
                            'purchase_date' =>  $date_import
                        ];
                        $purchase_insert = $purchase_insert->create($data_import_purchase);
                        $data_order_detail = [
                            'site_type' => 1,
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
                            'price_sale' => $value_detail->price02, // giá bán chưa thuế
                            'price_sale_tax'=> ($value_detail->price02 * $tax_class) + $value_detail->price02,  // giá bán có thuế
                            'total_price_sale' => ($value_detail->quantity*$value_detail->price02),  // tổng giá bán chưa thuế
                            'total_price_sale_tax' => $value_detail->quantity*(($value_detail->price02 * $tax_class) + $value_detail->price02), // tổng giá bán có thuế
                            'cost_price' => $value_detail->cost_price,  // nguyên giá chưa thuế
                            'total_price' => ($value_detail->quantity*$value_detail->cost_price), // tổng nguyên giá chưa thuế
                            'cost_price_tax' => ($value_detail->cost_price * $tax_class) + $value_detail->cost_price, // nguyên giá có thuế
                            'total_price_tax' => $value_detail->quantity*(($value_detail->cost_price * $tax_class) + $value_detail->cost_price), // tổng nguyên giá có thuế
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
                            // 'ship_name2' => $value_detail->shipping_name02, //hiện tại không dùng name2
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
                            'receive_time' =>  !empty($value_detail->cargo_schedule_time_from) ? $value_detail->cargo_schedule_time_from.'-'.$value_detail->cargo_schedule_time_to: '',
                            'delivery_way' => 1, // defaul cách giao hàng 1    
                            'pay_request' => $pay_request
                        ];
                        $insert_detail = $order_detail_model->create($data_order_detail);
                        $pay_request = 0;
                    }
                }
                array_push($list_order, $order);
            }
            $number_import_success = count($list_order);
            $data_update_import = ['number_success' => $number_import_success, 'number_error' => $number_import_error] ;
            if($number_import_error > 0)
            {
                $data_import_error['list_id'] = $list_order_error;
                $insert_import_error->create($data_import_error);
            }
            $update_import = $update_import->where('id', $insert_import->id)->update($data_update_import);
            DB::commit();
            // return $insert_order;
        }catch (Exception $exception)
        {
            DB::rollBack();
            Log::debug($exception->getMessage());
        }
    }

    /**
     * function import groups from ec-cube table
     */
    public function groups () {
        $dtb_group = 'dtb_group';
        $ec_group  = $this->ec_connect->table($dtb_group)->select(DB::raw('id, name, level, del_flg'))->orderBy('id')->get()->toArray();
        DB::beginTransaction();
        try{
            $groups = new Group();
            $groups->truncate();
            foreach($ec_group as $value){
                $groups->insert((array)$value);
            }
            DB::commit();
        }catch(Exception $ex){
            Log::error($ex->getMessage());
            DB::rollBack();
        }
    }

    /**
     * function import suppliers from ec-cube table
     */
    public function suppliers () {
        $dtb_supplier = 'dtb_supplier';
        try{
            $ec_supplier  = $this->ec_connect->table($dtb_supplier)
                                             ->select(DB::raw('dtb_supplier.*, mtb_pref.name as addr'))->leftJoin('mtb_pref', 'mtb_pref.id', 'dtb_supplier.pref')
                                             ->orderBy('supplier_id')->get()->toArray();
            DB::beginTransaction();
            $suppliers = new Supplier();
            $suppliers->truncate();
            $rimac_sup = [
                'id' => 0,
                'name' => 'NPO法人クローバープロジェクト21',
                'zip01' => '733',
                'zip02' => '0832',
                'addr01' => '広島県広島市西区草津港1丁目8-1',
                'addr02' => '広島市中央卸売市場関連棟238番',
                'tel01' => '082',
                'tel02' => '276',
                'tel03' => '7500',
                'fax01' => '082',
                'fax02' => '276',
                'fax03' => '7500',
            ];
            $suppliers->insert($rimac_sup);
            foreach($ec_supplier as $value){
                $supplier = [
                    'id' => $value->supplier_id,
                    'name' => $value->name,
                    'zip01' => $value->zip01,
                    'zip02' => $value->zip02,
                    'pref' => $value->addr,
                    'addr01' => $value->addr01,
                    'addr02' => $value->addr02,
                    'email' => $value->email,
                    'tel01' => $value->tel01,
                    'tel02' => $value->tel02,
                    'tel03' => $value->tel03,
                    'fax01' => $value->fax01,
                    'fax02' => $value->fax02,
                    'fax03' => $value->fax03,
                    'staff' => $value->staff,
                    'supplier_code_sagawa' => $value->supplier_code_sagawa,
                    'rank' => $value->rank,
                    'creator_id' => $value->creator_id,
                    'create_date' => $value->create_date,
                    'update_date' => $value->update_date,
                    'del_flg' => $value->del_flg,
                    'supplier_code_kuroneko' => $value->supplier_code_kuroneko,
                    'cargo_schedule_day' => $value->cargo_schedule_day,
                    'cargo_schedule_time_from' => $value->cargo_schedule_time_from,
                    'cargo_schedule_time_to' => $value->cargo_schedule_time_to,
                    'edi_type' => $value->edi_type,
                    'holiday_sun' => $value->holiday_sun,
                    'holiday_mon' => $value->holiday_mon,
                    'holiday_tue' => $value->holiday_tue,
                    'holiday_wed' => $value->holiday_wed,
                    'holiday_thu' => $value->holiday_thu,
                    'holiday_fri' => $value->holiday_fri,
                    'holiday_sat' => $value->holiday_sat,
                    'supplier_class' => $value->supplier_class,
                    'shipping_method' => rand(1,8)
                ];
                $suppliers->insert($supplier);
            }
            DB::commit();
            return [
                'status' => true,
            ];
        }catch(Exception $ex){
            Log::error($ex->getMessage());
            DB::rollBack();
            return [
                'status' => false,
            ];
        }
    }

    /**
     * function import product_statuses from ec-cube table
     */
    public function product_statuses () {
        $dtb_product_status = 'dtb_product_status';
        $ec_group  = $this->ec_connect->table($dtb_product_status)->select(DB::raw('product_status_id, product_id, del_flg, create_date as created_at, update_date as updated_at'))->orderBy('product_status_id')->get()->toArray();
        DB::beginTransaction();
        try{
            $product_status = new ProductStatus();
            $product_status->truncate();
            foreach($ec_group as $value){
                $product_status->insert((array)$value);
            }
            DB::commit();
            return [
                'status' => true
            ];
        }catch(Exception $ex){
            Log::error($ex->getMessage());
            DB::rollBack();
            return [
                'status' => false
            ];
        }
    }

    /**
     * function import ecube
     * @author Dat
     * 2019/10/29
     */
    public function import($date = null)
    {
        $date = '2014-08-07';

        try {
            return $this->order_model->whereDate('create_date',$date)->get();
        }catch (Exception $exception)
        {
            Log::error($exception->getMessage());
            return [
                'status' => 500,
                'message' => "cann't connect to EC database"
            ];
        }
    }
    /**
     * function import products
     * @author Dat
     * 20200108 
     */
    
    public function checkNumber($string){
        for($i = 0; $i < strlen($string); $i++){
            if(is_numeric($string[$i]) == false){
                return false;
            }
        }
        return true;
    }
    public function products()
    {
        $dtb_product = 'dtb_products';
        $dtb_products_class = 'dtb_products_class';
        $query_dtb_product = $this->ec_connect->table($dtb_product);
        DB::beginTransaction();
        try {
            $query_dtb_product = $query_dtb_product
            ->selectRaw("dtb_products_class.product_id, dtb_products_class.product_class_id, dtb_products.status, dtb_products.name, dtb_products.maker_id,
            dtb_products.maker_code, dtb_products.deliv_date_id, dtb_products.note,
            dtb_products.supplier_id, dtb_products.ec_deliv_id, dtb_products.short_name, dtb_products.handling_flg, dtb_products.tax_class,
            dtb_products_class.product_type_id, dtb_products_class.product_code, dtb_products_class.stock, dtb_products_class.stock_unlimited,
            dtb_products_class.sale_limit, dtb_products_class.price01, dtb_products_class.price02, dtb_products_class.cost_price, dtb_products_class.deliv_fee, 
            dtb_products_class.point_rate, dtb_products_class.create_date, dtb_products_class.update_date, 
            dtb_products.group1_id, dtb_products.group2_id, dtb_products.group3_id, dtb_products.group4_id, dtb_products.group5_id,
            dtb_products_class.del_flg as product_class_del_flg, dtb_products.del_flg as product_del_flg")
            ->join($dtb_products_class, "$dtb_product.product_id", '=', "$dtb_products_class.product_id")
            ->where('dtb_products.del_flg', 0)->where('dtb_products_class.del_flg', 0)
            ->get();
            DB::table('products')->truncate();
            foreach($query_dtb_product as $value){
                $products = array();
                $products['category_id'] = $value->product_type_id;
                $products['name'] = $value->name;
                $products['short_name'] = $value->short_name;
                $products['product_class_id'] = $value->product_class_id;
                $products['product_id'] = $value->product_id;
                $products['code'] = $value->product_code;
                $products['note'] = ((self::checkNumber($value->note) && !empty($value->note)) ? $value->note: 1);
                $products['price_sale'] = $value->price01;
                $products['price_sale_2'] = $value->price02;
                $products['cost_price'] = $value->cost_price;
                $products['supplied_id'] = $value->supplier_id;
                // $products['delivery_method'] = rand(1,8);
                $products['fee'] = $value->deliv_fee;
                $products['sku'] =  $value->product_code;
                $products['maker_id'] =  $value->maker_id;
                $products['maker_code'] =  $value->maker_code;
                $products['status'] = $value->status;
                $products['created_at'] = $value->create_date;
                $products['updated_at'] = $value->update_date;
                $products['point_rate'] = $value->point_rate;
                // $products['deliv_date_id'] = $value->deliv_date_id;
                $products['group1_id'] = $value->group1_id;
                $products['group2_id'] = $value->group2_id;
                $products['group3_id'] = $value->group3_id;
                $products['group4_id'] = $value->group4_id;
                $products['group5_id'] = $value->group5_id;
                $products['product_del_flg'] = $value->product_del_flg;
                $products['product_class_del_flg'] = $value->product_class_del_flg;
                $products['handling_flg'] = $value->handling_flg;
                $products['tax_class'] = $value->tax_class;
                DB::table('products')->insert($products);
            }
            DB::commit();
            return [
                'status' => true
            ];
        }catch(Exception $exception)
        {
            DB::rollBack();
            Log::debug($exception->getMessage());
            return [
                'status' => false
            ];
        }
    }

    /**
     * function importMaster
     * Description: restore data products, suppliers table
     * @author chan_nl
     * Created: 2020/7/4
     * Updated: 2020/7/4
     */
    public function importMaster(){
        $check_import = true;
        $product_status = self::product_statuses();
        if($product_status['status']){            
            $products_imp = self::products();
            if($products_imp['status']){
                $suppliers_imp = self::suppliers();
                if($suppliers_imp['status'] == false){
                    $check_import = false;
                }
            }else {
                $check_import = false;
            }
        }else {
            $check_import = false;
        }
        return $check_import;
    }
}