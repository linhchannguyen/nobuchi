<?php
namespace App\Repositories\Services\RakutenImport;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\Imports\Import;
use App\Model\Orders\ImportRakuten;
use App\Model\Orders\Order;
use App\Model\Orders\OrderDetail;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use Carbon\Carbon;

define("RMS_SERVICE_SECRET", "SP279945_SYD6FTEhKTnFNL2M");
define("RMS_LICENSE_KEY", "SL279945_Iy9HWuM3jfuCCBy7");//SL279945_NHaQimHFtCxre9fa
define("RMS_API_RAKUTEN_PAY_SEARCH_ORDER", 'https://api.rms.rakuten.co.jp/es/2.0/order/searchOrder/');
define("RMS_API_RAKUTEN_PAY_GET_ORDER", 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/');

class RakutenImportService implements RakutenImportServiceContract
{
    private $import_model;
    private $import_rakuten_model;
    private $order_model;
    private $order_detail_model;
    private $purchase_model;
    private $shipment_model;
    
    public function __construct(){
        $this->import_model = new Import();
        $this->import_rakuten_model = new ImportRakuten();
        $this->order_model = new Order();
        $this->order_detail_model = new OrderDetail();
        $this->purchase_model = new Purchase();
        $this->shipment_model = new Shipment();
    }

    /**
     * function import rakuten
     * @author channl
     * 2019/10/30
     */
    public function import($date = null)
    {
        DB::beginTransaction();// start transaction
        try {
            /** element: $checkTotalPages
             * kiểm tra sô lượng page: dữ liệu get trong ngày thì thường k tới 1k (chỉ có page = 1) order nhưng trường hợp shop
             * đó bán đắt thì có khi page lên đến 2 nên phải truyền page 2 vào để lấy data page = 2
             */
            $checkTotalPages = $this->_getListOrdersRakuten(1);//đầu tiên truyền page = 1 để kiểm tra xem ngày đó có order không
            if(isset($checkTotalPages['PaginationResponseModel'])){//kiểm tra nếu data trả về có object PaginationResponseModel thì mới thực hiện tiếp, nếu không có object nghĩa là ngày đó k có order (bán ế)
                $totalPages = $checkTotalPages['PaginationResponseModel']['totalPages'];//lấy tổng số page
                for($i = 1; $i <= $totalPages; $i++){//nếu page > 1 thì chạy các page còn lại để lấy danh sách order
                    $listOrder = $this->_getListOrdersRakuten($i);//lấy danh sách order page thứ i
                    if(count($listOrder['orderNumberList']) > 0){//kiểm tra nếu ngày đó có order thì mới import và ngược lại
                        $orderNumberList = $listOrder['orderNumberList'];//danh sách order                        
                        $date_import = Carbon::now()->format('Y/m/d');
                        $get_import = $this->import_model->where([
                            'type' => 2,
                            'date_import' => $date_import
                        ])->get();
                        if(count($get_import) <= 0){
                            //Insert danh sách order vào bảng imports
                            $import = array();
                            $import['type'] = 2;
                            $import['date_import'] = $date_import;
                            $import['website'] = 'Rakuten';
                            $import['number_order'] = count($listOrder['orderNumberList']);
                            $import['number_success'] = count($listOrder['orderNumberList']);
                            // $import['number_error'] = ;
                            // $import['number_duplicate'] = ;
                            $import_success = Import::create($import);
                            $import_id = $import_success->id;
                            //End Insert danh sách order vào bảng imports
                            
                            foreach($orderNumberList as $value){//chạy từng order để lấy thông tin order
                                $getOrder = $this->_getOrder(array($value));
                                print_r($getOrder);
                                $order['OrderModelList'] = $getOrder['OrderModelList'][0];
                                $order['OrdererModel'] = $order['OrderModelList']['OrdererModel'];
                                $order['SettlementModel'] = $order['OrderModelList']['SettlementModel'];
                                $order['DeliveryModel'] = $order['OrderModelList']['DeliveryModel'];
                                $order['PointModel'] = $order['OrderModelList']['PointModel'];
                                $order['PackageModelList'] = $getOrder['OrderModelList'][0]['PackageModelList'][0];
                                $order['SenderModel'] = $order['PackageModelList']['SenderModel'];
                                $order['ItemModelList'] = $order['PackageModelList']['ItemModelList'];
                                $item_quantity = count($order['ItemModelList']);//kiểm tra xem KH mua bao nhiêu sản phẩm
                                //Lấy thông tin shipping
                                // if(count($order['PackageModelList']['ShippingModelList']) > 0){
                                //     $order['ShippingModelList'] = $order['PackageModelList']['ShippingModelList'];
                                // }
                                //End lấy thông tin shipping

                                //Insert order vào bảng orders
                                $orders_ = $this->order_model->select('id')->where([
                                    ['order_code', '=', $order['OrderModelList']['orderNumber']],
                                    ['order_date', '=', $order['OrderModelList']['orderDatetime']]
                                ])->get();
                                if(count($orders_) <= 0){
                                    $orders = array();
                                    $orders['site_type']                = 2;
                                    $orders['import_id']                = $import_id;
                                    $orders['order_code']               = $order['OrderModelList']['orderNumber'];//Mã order
                                    $orders['order_date']               = $order['OrderModelList']['orderDatetime'];//Ngày đặt order
                                    $orders['buyer_name1']              = $order['OrdererModel']['familyName'];
                                    $orders['buyer_name2']              = $order['OrdererModel']['firstName'];
                                    $orders['buyer_name1_kana']         = $order['OrdererModel']['familyNameKana'];
                                    $orders['buyer_name2_kana']         = $order['OrdererModel']['firstNameKana'];
                                    $orders['buyer_country']            = $order['OrdererModel']['prefecture'];
                                    $orders['buyer_address_1']          = $order['OrdererModel']['prefecture'].$order['OrdererModel']['city'];
                                    $orders['buyer_address_2']          = $order['OrdererModel']['subAddress'];
                                    $orders['buyer_email']              = $order['OrdererModel']['emailAddress'];
                                    $orders['buyer_zip1']               = $order['OrdererModel']['zipCode1'];
                                    $orders['buyer_zip2']               = $order['OrdererModel']['zipCode2'];
                                    $orders['buyer_tel1']               = $order['OrdererModel']['phoneNumber1'];
                                    $orders['buyer_tel2']               = $order['OrdererModel']['phoneNumber2'];
                                    $orders['buyer_tel3']               = $order['OrdererModel']['phoneNumber3'];
                                    $orders['buyer_sex']                = $order['OrdererModel']['sex'];
                                    if($order['OrdererModel']['birthYear'] != null && $order['OrdererModel']['birthMonth'] != null && $order['OrdererModel']['birthDay'] != null){
                                        $orders['buyer_birthday']       = $order['OrdererModel']['birthYear'] . '-' . $order['OrdererModel']['birthMonth'] . '-' . $order['OrdererModel']['birthDay'];
                                    }
                                    $orders['tax']                      = $order['OrderModelList']['goodsTax'];//Thuế của order
                                    $orders['charge']                   = $order['OrderModelList']['paymentCharge'];//Phí
                                    $orders['sub_total']                = $order['OrderModelList']['goodsPrice'];//Tổng của giá bán * số lượng
                                    // $orders['order_sub_total']          = $order['OrderModelList']['totalPrice'];//Tổng tiền đã tính phí
                                    $orders['order_delivery_fee']       = $order['OrderModelList']['postagePrice'];//Phí giao hàng
                                    $orders['order_gift_wrap_price']    = $order['OrderModelList']['giftCheckFlag'];
                                    // $orders['order_tax']                = $order['OrderModelList']['goodsTax'];//Thuế của order
                                    // $orders['order_charge']             = $order['OrderModelList']['paymentCharge'];//Phí
                                    $orders['order_discount']           = $order['OrderModelList']['couponAllTotalPrice'];//Giảm giá của order
                                    $orders['order_total']              = $order['OrderModelList']['requestPrice'];//Tổng tiền đã tính phí và trừ use point
                                    $orders['use_point']                = $order['PointModel']['usedPoint'];//Điểm sử dụng
                                    $orders['payment_total']            = $order['OrderModelList']['requestPrice'];
                                    $orders['order_site_charge']        = $order['OrderModelList']['paymentCharge'];
                                    $orders['comments']                 = $order['OrderModelList']['remarks'];
                                    $orders['noshi_name']               = $order['PackageModelList']['noshi'];
                                    $orders['payment_method']           = $order['SettlementModel']['settlementMethod'];
                                    $orders['credit_type']              = $order['SettlementModel']['cardName'];
                                    $orders['cargo_schedule_day']       = $order['OrderModelList']['shippingInstDatetime'];
                                    $orders['status']                   = 1;
                                    $orders['support_cus']              = 1;
                                    $orders['flag_confirm']             = 0;
                                    $orders['delivery_date']            = $order['OrderModelList']['deliveryDate'];

                                    //insert
                                    $order_success = $this->order_model->create($orders);

                                    $arr_sup = [];
                                    $arr_page = [];
                                    $add_ship = true;
                                    $arr_ship = [];
                                    $page_ = 0;
                                    $ship_code_number = 1;
                                    $shipment_id = 0;//Dùng để thêm shipment_id vào order_details
                                    for($j = 0; $j < $item_quantity; $j++){//Lấy thông tin sản phẩm thứ j
                                        $add_ship = true;
                                        //products
                                        $products_ = DB::table('products')->where([
                                            'code' => $order['ItemModelList'][$j]['itemNumber'],
                                            'product_del_flg' => 0,
                                            'product_class_del_flg' => 0,
                                            'status' => 1
                                        ])->get()->first();
                                        $product_id = (!empty($products_)) ? $products_->product_id : 0;
                                        $cost_price = (!empty($products_)) ? $products_->cost_price : 0;
                                        $category_id = (!empty($products_)) ? $products_->category_id : 0;
                                        $short_name = (!empty($products_)) ? $products_->short_name : '';
                                        $supplier_id = (!empty($products_)) ? $products_->supplied_id : null;
                                        $price_sale2 = (!empty($products_)) ? $products_->price_sale_2 : 0;
                                        $tax_class = (!empty($products_)) ? $products_->tax_class : 0;
                                        $tax_details = DB::table('tax_details')->where('tax_class', $tax_class)->orderBy('id')->get()->toArray();
                                        $tax_detail = [];
                                        $tax = 0;
                                        if(!empty($tax_details)){
                                            foreach($tax_details as $tax_det){
                                                if($tax_det->apply_date <= $order_success->order_date){
                                                    $tax_detail = $tax_det;
                                                }
                                            }
                                            $tax = ($tax_detail->tax_rate / 100);
                                        }
                                        $cost_price_tax = round($cost_price + ($cost_price * $tax));
                                        $total_cost_price_tax = $order['ItemModelList'][$j]['units'] * $cost_price_tax;
                                        $price_sale_tax = round($order['ItemModelList'][$j]['price']);
                                        $total_price_sale_tax = $order['ItemModelList'][$j]['units'] * $price_sale_tax;
                                        //suppliers
                                        $s_name = '';
                                        $zip = '';
                                        $zip2 = '';
                                        $address = '';
                                        $address2 = '';
                                        $tel = '';
                                        $tel2 = '';
                                        $tel3 = '';
                                        $supplier_code_sagawa = '';
                                        $supplier_code_kuroneko = '';
                                        $delivery_method = 0;
                                        if($supplier_id != null){
                                            $suppliers_ = DB::table('suppliers')->where('id', $supplier_id)->get()->first();
                                            $s_name = (!empty($suppliers_)) ? $suppliers_->name : '';
                                            $zip = (!empty($suppliers_)) ? $suppliers_->zip01 : '';
                                            $zip2 = (!empty($suppliers_)) ? $suppliers_->zip02 : '';
                                            $address = (!empty($suppliers_)) ? $suppliers_->addr01 : '';
                                            $address2 = (!empty($suppliers_)) ? $suppliers_->addr02 : '';
                                            $tel = (!empty($suppliers_)) ? $suppliers_->tel01 : '';
                                            $tel2 = (!empty($suppliers_)) ? $suppliers_->tel02 : '';
                                            $tel3 = (!empty($suppliers_)) ? $suppliers_->tel03 : '';
                                            $supplier_code_sagawa = (!empty($suppliers_)) ? $suppliers_->supplier_code_sagawa : '';
                                            $supplier_code_kuroneko = (!empty($suppliers_)) ? $suppliers_->supplier_code_kuroneko : '';
                                            // $delivery_method = (!empty($suppliers_)) ? $suppliers_->shipping_method : 0;
                                        }

                                        //shipments
                                        $page = 1;//Dùng để tạo mã đặt hàng

                                        //purchases
                                        $purchases = array();
                                        $purchase_date = Carbon::now()->format('Ymd');
                                        $purchase_code = '';
                                        $prev_purchase = '';
                                        if(!empty($arr_ship))
                                        {
                                            foreach($arr_ship as $ship)
                                            {
                                                if($supplier_id == $ship['sup-id'])
                                                {
                                                    $add_ship = false;
                                                    $shipment_id = $ship['id'];
                                                break;
                                                }
                                            }
                                        }
                                        if(empty($arr_ship) || $add_ship == true)
                                        {
                                             //shipments
                                             $shipments = array();
                                             $shipments['shipment_code']     = 'その他'.$ship_code_number;
                                             // $shipments['shipment_quantity'] = $order['ItemModelList'][$j]['units'];//Không dùng do 1 shipment có nhiều sp, mỗi sp có sl khác nhau
                                             $shipments['shipment_customer'] = $order['OrdererModel']['familyName'].$order['OrdererModel']['firstName'];
                                             $shipments['shipment_address']  = $order['OrdererModel']['prefecture'].$order['OrdererModel']['city'].$order['OrdererModel']['subAddress'];
                                             $shipments['shipment_email']    = $order['OrdererModel']['emailAddress'];
                                             // $shipments['shipment_fax'] = 0;
                                             $shipments['shipment_phone']    = $order['OrdererModel']['phoneNumber1'].'-'.$order['OrdererModel']['phoneNumber2'].'-'.$order['OrdererModel']['phoneNumber3'];
                                             $shipments['shipment_at']       = $order['OrdererModel']['prefecture'].$order['OrdererModel']['city'].$order['OrdererModel']['subAddress'];
                                             $shipments['type']              = 0;
                                             $shipments['status']            = 0;
                                             $shipments['shipment_date']     = $order['OrderModelList']['deliveryDate'];
                                             // $shipments['shipment_time'] = 0;
                                             // $shipments['receive_date'] = $order['OrderModelList']['deliveryDate'];
                                             // $shipments['receive_time']      =;
                                             $shipments['delivery_method']   = 8;
                                             $shipments['delivery_way']      = 1;
                                             $shipments['shipment_zip']      = $order['OrdererModel']['zipCode1'].'-'.$order['OrdererModel']['zipCode2'];
                                             $shipments['shipping_pref']     = $order['OrdererModel']['prefecture'];
                                             $shipments['shipment_fee']      = 0;
                                             // $shipments['shipment_payment'] = 0;
                                             // $shipments['created_by'] = 0;
                                             // $shipments['updated_by'] = 0;
                                             $shipments['del_flg']           = 0;
                                             $shipments['pay_request']       = ($j == 0) ? 1 : 0;
                                             // $shipments['deleted_by'] = 0;
                                             // $shipments['invoice_id'] = 0;
                                             $shipments['supplied_id']       = $supplier_id;
                                             $shipments['es_shipment_date']  = $order['OrderModelList']['deliveryDate'];
                                             // $shipments['es_shipment_time'] = 0;
                                            $ship_code_number++;
                                             $shipments_success = $this->shipment_model->create($shipments);
                                             $shipment_id = $shipments_success->id;
                                            $ship_detail = [
                                                'id' => $shipment_id,
                                                'ship-code' => $shipments['shipment_code'],
                                                'sup-id' => $supplier_id
                                            ];
                                            array_push($arr_ship, $ship_detail);
                                        }
                                        // dat hang
                                        if(!in_array($supplier_id, $arr_sup)){
                                            array_push($arr_sup, $supplier_id);
                                            $page_++;
                                            array_push($arr_page, $page_);
                                        }
                                        if(count($arr_sup) > 0){
                                            foreach($arr_sup as $key => $val){
                                                if($val === $supplier_id || $supplier_id === null){
                                                    $page = $arr_page[$key];
                                                }
                                            }
                                        }
                                        if($supplier_id < 10){
                                            $prev_purchase = '000'.$supplier_id;
                                        }else if($supplier_id < 100){
                                            $prev_purchase = '00'.$supplier_id;
                                        }else if ($supplier_id < 1000){
                                            $prev_purchase = '0'.$supplier_id;
                                        }else {
                                            $prev_purchase = $supplier_id;
                                        }
                                        $purchase_code = $prev_purchase.'-'.$purchase_date.'-'.$page;
                                        $purchases['purchase_code']         = $purchase_code;
                                        $purchases['order_id']              = $order_success->id;
                                        $purchases['supplier_id']           = $supplier_id;
                                        // $purchases['status'] = ;
                                        $purchases['purchase_quantity']     = $order['ItemModelList'][$j]['units'];
                                        $purchases['cost_price']            = $cost_price;//giá vốn;
                                        $purchases['total_cost_price']      = $order['ItemModelList'][$j]['units'] * $cost_price;//tổng giá vốn
                                        $purchases['cost_price_tax']        = $cost_price_tax;//giá vốn có thuế
                                        $purchases['total_cost_price_tax']  = $total_cost_price_tax;//tổng giá vốn có thuế
                                        $purchases['price_edit']            = 0;
                                        $purchases['flag_download']         = 0;
                                        $purchases['purchase_date']         = $order['OrderModelList']['orderDatetime'];
                                        $purchases_success = $this->purchase_model->create($purchases);

                                        //order_details
                                        $order_details = array();                                    
                                        $order_details['order_id']              = $order_success->id;
                                        $order_details['order_code']            = $order_success->order_code;
                                        $order_details['shipment_id']           = $shipment_id;
                                        $order_details['purchase_id']           = $purchases_success->id;
                                        $order_details['product_code']          = $order['ItemModelList'][$j]['itemNumber'];
                                        $order_details['product_id']            = $product_id;//$order['ItemModelList'][$j]['itemId'];
                                        $order_details['product_name']          = $order['ItemModelList'][$j]['itemName'];
                                        $order_details['quantity']              = $order['ItemModelList'][$j]['units'];
                                        $order_details['quantity_set']          = 1;
                                        $order_details['price_sale']            = $price_sale2;
                                        $order_details['total_price_sale']      = $price_sale2 * $order['ItemModelList'][$j]['units'];
                                        $order_details['price_sale_tax']        = $price_sale_tax;
                                        $order_details['total_price_sale_tax']  = $total_price_sale_tax;
                                        $order_details['cost_price']            = $cost_price;//giá vốn
                                        $order_details['total_price']           = $order['ItemModelList'][$j]['units'] * $cost_price;//tổng giá vốn
                                        $order_details['cost_price_tax']        = $cost_price_tax;//giá vốn có thuế
                                        $order_details['total_price_tax']       = $total_cost_price_tax;//tổng giá vốn có thuế
                                        $order_details['tax']                   = $tax;//thuế của order
                                        $order_details['discount']              = $order['OrderModelList']['couponAllTotalPrice'];//giảm giá
                                        $order_details['type']                  = $category_id;//loại sản phẩm
                                        $order_details['site_type']             = 2;
                                        $order_details['supplied_id']           = $supplier_id;
                                        $order_details['supplied']              = $s_name;
                                        $order_details['supplier_zip1']         = $zip;
                                        $order_details['supplier_zip2']         = $zip2;
                                        $order_details['supplier_addr1']        = $address;
                                        $order_details['supplier_addr2']        = $address2;
                                        $order_details['supplier_tel1']         = $tel;
                                        $order_details['supplier_tel2']         = $tel2;
                                        $order_details['supplier_tel3']         = $tel3;
                                        $order_details['supplier_code_sagawa']  = $supplier_code_sagawa;
                                        $order_details['supplier_code_kuroneko']= $supplier_code_kuroneko;
                                        $order_details['product_name_sub']      = $short_name;
                                        $order_details['sku']                   = $order['ItemModelList'][$j]['itemNumber'];
                                        $order_details['delivery_method']       = 8;//$delivery_method confirm lại sau
                                        $order_details['delivery_way']          = 1;
                                        $order_details['ship_name1']            = $order['OrdererModel']['familyName'];
                                        $order_details['ship_name2']            =$order['OrdererModel']['firstName'];
                                        $order_details['ship_name1_kana']       = $order['OrdererModel']['familyNameKana'];
                                        $order_details['ship_name2_kana']       = $order['OrdererModel']['firstNameKana'];
                                        $order_details['ship_country']          = $order['OrdererModel']['prefecture'];
                                        $order_details['ship_address1']         = $order['OrdererModel']['prefecture'].$order['OrdererModel']['city'];
                                        $order_details['ship_address2']         = $order['OrdererModel']['subAddress'];
                                        $order_details['ship_address3']         = '';
                                        $order_details['ship_address1_kana']    = '';
                                        $order_details['ship_zip']              = $order['OrdererModel']['zipCode1'].'-'.$order['OrdererModel']['zipCode2'];
                                        $order_details['ship_phone']            = $order['OrdererModel']['phoneNumber1'].'-'.$order['OrdererModel']['phoneNumber2'].'-'.$order['OrdererModel']['phoneNumber3'];
                                        $order_details['delivery_fee']          = $order['OrderModelList']['postagePrice'];
                                        // $order_details['delivery_payment']   =
                                        // $order_details['es_delivery_date']      = $order['OrderModelList']['deliveryDate'];
                                        $order_details['es_delivery_date_from'] = $order['OrderModelList']['deliveryDate'];//Ngày dự định xuất hàng (ngày tập kết hàng) <=> es_shipment_date
                                        // $order_details['es_delivery_date_to']   = $order['OrderModelList']['deliveryDate'];
                                        // $order_details['delivery_date_from']    = $order['OrderModelList']['deliveryDate'];
                                        // $order_details['delivery_date_to']      = $order['OrderModelList']['deliveryDate'];
                                        $order_details['delivery_date']         = $order['OrderModelList']['deliveryDate'];//Ngày giao hàng của order <=> shipment_date
                                        // $order_details['delivery_time'] =
                                        $order_details['receive_date']          = $order['OrderModelList']['deliveryDate'];
                                        // $order_details['receive_time'] =
                                        // $order_details['wrapping_paper_type'] =
                                        // $order_details['wrapping_ribbon_type'] =
                                        // $order_details['gift_wrap'] =
                                        // $order_details['gift_wrap_kind'] =
                                        // $order_details['gift_message'] =
                                        $order_details['message'] = $order['OrderModelList']['remarks'];
                                        $order_details['pay_request'] = ($j == 0) ? 1 : 0;
                                        //insert
                                        $this->order_detail_model->create($order_details);
                                        
                                        //Insert chi tiết order vào bảng import_rakutens
                                        $import_rakutens = array();
                                        $import_rakutens['order_id']                = $order['OrderModelList']['orderNumber'];
                                        $import_rakutens['order_date']              = $order['OrderModelList']['orderDatetime'];
                                        $import_rakutens['product_id']              = $order['ItemModelList'][$j]['itemNumber'];
                                        $import_rakutens['quantity']                = $order['ItemModelList'][$j]['units'];
                                        $import_rakutens['price']                   = $order['ItemModelList'][$j]['price'];
                                        $import_rakutens['select_date']             = $order['ItemModelList'][$j]['selectedChoice'];
                                        $import_rakutens['buyer_name1']             = $order['OrdererModel']['familyName'];
                                        $import_rakutens['buyer_name2']             = $order['OrdererModel']['firstName'];
                                        $import_rakutens['buyer_name1_kana']        = $order['OrdererModel']['familyNameKana'];
                                        $import_rakutens['buyer_name2_kana']        = $order['OrdererModel']['firstNameKana'];
                                        $import_rakutens['buyer_email']             = $order['OrdererModel']['emailAddress'];
                                        $import_rakutens['buyer_zip1']              = $order['OrdererModel']['zipCode1'];
                                        $import_rakutens['buyer_zip2']              = $order['OrdererModel']['zipCode2'];
                                        $import_rakutens['buyer_pref']              = $order['OrdererModel']['prefecture'];
                                        $import_rakutens['buyer_city']              = $order['OrdererModel']['city'];
                                        $import_rakutens['buyer_area']              = $order['OrdererModel']['subAddress'];
                                        $import_rakutens['buyer_tel1']              = $order['OrdererModel']['phoneNumber1'];
                                        $import_rakutens['buyer_tel2']              = $order['OrdererModel']['phoneNumber2'];
                                        $import_rakutens['buyer_tel3']              = $order['OrdererModel']['phoneNumber3'];
                                        $import_rakutens['buyer_sex']               = $order['OrdererModel']['sex'];
                                        if($order['OrdererModel']['birthYear'] != null && $order['OrdererModel']['birthMonth'] != null && $order['OrdererModel']['birthDay'] != null){
                                            $import_rakutens['buyer_birthday']          = $order['OrdererModel']['birthYear'] . '-' . $order['OrdererModel']['birthMonth'] . '-' . $order['OrdererModel']['birthDay'];
                                        }
                                        $import_rakutens['recipient_name1']         = $order['SenderModel']['familyName'];
                                        $import_rakutens['recipient_name2']         = $order['SenderModel']['firstName'];
                                        $import_rakutens['recipient_name1_kana']    = $order['SenderModel']['familyNameKana'];
                                        $import_rakutens['recipient_name2_kana']    = $order['SenderModel']['firstNameKana'];
                                        $import_rakutens['ship_zip1']               = $order['SenderModel']['zipCode1'];
                                        $import_rakutens['ship_zip2']               = $order['SenderModel']['zipCode2'];
                                        $import_rakutens['ship_pref']               = $order['SenderModel']['prefecture'];
                                        $import_rakutens['ship_city']               = $order['SenderModel']['city'];
                                        $import_rakutens['ship_area']               = $order['SenderModel']['subAddress'];
                                        $import_rakutens['ship_tel1']               = $order['SenderModel']['phoneNumber1'];
                                        $import_rakutens['ship_tel2']               = $order['SenderModel']['phoneNumber2'];
                                        $import_rakutens['ship_tel3']               = $order['SenderModel']['phoneNumber3'];
                                        $import_rakutens['ship_option']             = $order['SenderModel']['isolatedIslandFlag'];
                                        $import_rakutens['payment_method']          = $order['SettlementModel']['settlementMethod'];
                                        $import_rakutens['creadit_type']            = $order['SettlementModel']['cardName'];
                                        $import_rakutens['credit_no']               = '(非表示)';
                                        $import_rakutens['credit_holder']           = '(非表示)';
                                        $import_rakutens['credit_expiration_date']  = '(非表示)';
                                        $import_rakutens['credit_split']            = $order['SettlementModel']['cardPayType'];
                                        $import_rakutens['credit_split_note']       = 0;
                                        $import_rakutens['deliv_type']              = $order['DeliveryModel']['deliveryName'];
                                        $import_rakutens['comment']                 = $order['OrderModelList']['remarks'];
                                        $import_rakutens['wrapping_paper_type']     = 0;
                                        $import_rakutens['wrapping_robbon_type']    = 0;
                                        $import_rakutens['gift_check']              = $order['OrderModelList']['giftCheckFlag'];
                                        $import_rakutens['total1']                  = $order['OrderModelList']['goodsPrice'];//Tổng tiền bán * số lượng
                                        $import_rakutens['deliv_fee']               = $order['OrderModelList']['postagePrice'];//Phí giao hàng
                                        $import_rakutens['tax']                     = $order['OrderModelList']['goodsTax'];//Thuế
                                        $import_rakutens['charge']                  = $order['OrderModelList']['paymentCharge'];//phí
                                        $import_rakutens['total2']                  = $order['OrderModelList']['totalPrice'];//Tổng tiền đã tính phí và chưa trừ use point
                                        $import_rakutens['terminal_type']           = 0;
                                        $import_rakutens['point_use']               = $order['PointModel']['usedPoint'];
                                        $import_rakutens['point_terms_of_use']      = 0;
                                        $import_rakutens['point_value']             = 0;
                                        $import_rakutens['point_status']            = 0;
                                        $import_rakutens['total3']                  = $order['OrderModelList']['requestPrice'];//đã trừ usedPoint
                                        $import_rakutens['acceptance_charge']       = 0;
                                        $import_rakutens['memo']                    = $order['OrderModelList']['memo'];
                                        
                                        // insert
                                        $this->import_rakuten_model->create($import_rakutens);
                                    }
                                    // End Insert chi tiết order vào bảng import_rakutens
                                }
                                //End insert order vào bảng orders
                            }
                        }else {
                            //Ghi log nếu như cố tình chạy hàm import tự động nhiều lần
                            Log::error('Run function import in RakutenImportService:: Data completed. Not allowed to run multiple times!!!!');
                            return [
                                'status' => 204,
                                'message' => "Data completed"
                            ];
                        }
                    }
                }
            }else {                
                print_r('License key not support. Please create new key!!!');
            }
        DB::commit(); // commit database
        }catch (Exception $exception)
        {
            DB::rollBack(); // reset data
            Log::error($exception);
            return [
                'status' => 500,
                'message' => "cann't connect to EC database"
            ];
        }
    }

    /**
     * function get order
     * Description: get order info
     * @author channl
     * Created: 2019/10/30
     * Updated: 2019/10/30
     */
    public function _getOrder($orderNumber) {        
        $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
        $header = array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: ESA {$authkey}",
        );
        $requestJson = json_encode([
            'orderNumberList' => $orderNumber,
            'version' => 2
        ]);
        
        $url = RMS_API_RAKUTEN_PAY_GET_ORDER;
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $requestJson);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_TIMEOUT,        30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
        
        $response = curl_exec($ch);
        if(curl_error($ch)){
            $response = curl_error($ch);
        }
        
        $response = json_decode( $response, true );
        
        curl_close($ch);
        return $response;
    }

    /**
     * function get list order in API Rakuten
     * @author channl
     * Created: 2019/10/30
     * Updated: 2019/10/30
     */

    private function _getListOrdersRakuten($page){        
        $dateType = 1;
        $endDate = Carbon::now();
        $endDate->modify('0 day');
        $startDate = clone $endDate;
        $startDate->modify('-1 day');
        $startDateTime = $startDate->format("Y-m-d\T06:00:00+0900");
        $endDateTime = $endDate->format("Y-m-d\T05:59:59+0900");
        $orderProgressList = [ 100, 200, 300, 400, 500, 600, 700, 800, 900 ];
        $paginationRequestModel = [
            'requestRecordsAmount' => 1000,
            'requestPage' => $page,
        ];
        $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
        $header = array(
          'Content-Type: application/json; charset=utf-8',
          "Authorization: ESA {$authkey}",
        );
        
        $requestJson = json_encode([
            'dateType' => $dateType,//期間検索種別
            'startDatetime' => $startDateTime,//検索対象期間先頭日時
            'endDatetime' => $endDateTime,//検索対象エンド点
            'orderProgressList'=> $orderProgressList,//取得したいオーダーステータス
            'PaginationRequestModel' => $paginationRequestModel // pagination
        ]);
        $url = RMS_API_RAKUTEN_PAY_SEARCH_ORDER;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $requestJson);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_TIMEOUT,        30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します        
        $response = curl_exec($ch);
        if(curl_error($ch)){
          $response = curl_error($ch);
        }        
        $response = json_decode( $response, true );        
        curl_close($ch);
        return $response;
        // return $this->_searchOrder($dateType, $startDateTime, $endDateTime, $orderProgressList, $paginationRequestModel);
    }

    // private function _searchOrder($dateType, $startDateTime, $endDateTime, $orderProgressList, $paginationRequestModel) {
    //     $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
    //     $header = array(
    //       'Content-Type: application/json; charset=utf-8',
    //       "Authorization: ESA {$authkey}",
    //     );
        
    //     $requestJson = json_encode([
    //         'dateType' => $dateType,//期間検索種別
    //         'startDatetime' => $startDateTime,//検索対象期間先頭日時
    //         'endDatetime' => $endDateTime,//検索対象エンド点
    //         'orderProgressList'=> $orderProgressList,//取得したいオーダーステータス
    //         'PaginationRequestModel' => $paginationRequestModel // pagination
    //     ]);
    //     $url = RMS_API_RAKUTEN_PAY_SEARCH_ORDER;
        
    //     $ch = curl_init($url);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS,     $requestJson);
    //     curl_setopt($ch, CURLOPT_POST,           true);
    //     curl_setopt($ch, CURLOPT_TIMEOUT,        30);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します        
    //     $response = curl_exec($ch);
    //     if(curl_error($ch)){
    //       $response = curl_error($ch);
    //     }        
    //     $response = json_decode( $response, true );        
    //     curl_close($ch);
    //     return $response;
    // }
}