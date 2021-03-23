<?php

use App\Model\Products\Product;
use App\Model\Orders\Order;
use App\Model\Suppliers\Supplier;
use App\Model\Taxs\TaxDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderDetailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $count_p = Product::get();
        foreach (range(1,300) as $index) { 
            $i = $faker->numberBetween(2,5);
            $arr_sup = [];
            $arr_page = [];
            $page_ = 0;
            foreach (range(1,$i) as $index_) { 
                $order = Order::where('id', $index)->first();  

                $p_index = Product::select('product_id')->whereNotNull('code')->whereNotNull('supplied_id')->orderBy(DB::raw('random()'))->limit(1)->pluck('product_id')->first();
                $products = Product::where('product_id', $p_index)->first();
                $product_id = $products->product_id;
                $product_name = $products->short_name;
                $product_code = $products->code;
                $maker_id = $products->maker_id;
                $maker_code = $products->maker_code;
                $cost_price = $products->cost_price;
                $price_sale2 = $products->price_sale_2;
                $supplier_id = $products->supplied_id;
                $tax_class = $products->tax_class;
                $fee = $products->fee;
                $tax_details = TaxDetail::where('tax_class', $tax_class)->orderBy('id')->get()->toArray();
                $tax_detail = [];
                foreach($tax_details as $key => $value){
                    if($value['apply_date'] <= $order->order_date){
                        $tax_detail = $value;
                    }
                }
                $tax = ($tax_detail['tax_rate'] / 100);
                $quantity = $faker->numberBetween(1,5);
                $cost_price_tax = round($cost_price) + (round($cost_price) * $tax);
                $pre_untax = round($cost_price) * $quantity;
                $pre_tax = $pre_untax * $tax;
                $total_cost_price_tax = $pre_untax + round($pre_tax);
                $price_sale_tax = round($price_sale2) + (round($price_sale2) * $tax);
                $total_price_sale_tax = round($price_sale_tax) * $quantity;
                if(empty($supplier_id)){
                    $supplier_id = 1;
                }
                $supplier = Supplier::where('id', $supplier_id)->first();
                $supplier_name = '';
                if(!empty($supplier)){
                    $supplier_name = $supplier->name;
                }
                $page = 0;
                array_push($arr_sup, $supplier_id);
                if(count($arr_sup) > 0){
                    foreach($arr_sup as $key => $val){
                        if($val === $supplier_id || $supplier_id === null){
                            $page++;
                        }
                    }
                }
                $purchase_date = $order->purchase_date;
                $purchase_code = '';
                $prev_purchase = '';
                if($supplier_id < 10){
                    $prev_purchase = '000'.$supplier_id;
                }else if($supplier_id < 100){
                    $prev_purchase = '00'.$supplier_id;
                }else if ($supplier_id < 1000){
                    $prev_purchase = '0'.$supplier_id;
                }else {
                    $prev_purchase = $supplier_id;
                }
                $purchase_code = $prev_purchase.'-'.str_replace('/','', date('Y/m/d', strtotime($purchase_date))).'-000'.$page;
                if($product_id != null){
                    DB::table('order_details')->insert([           
                        'order_id' =>  $order->id,
                        'order_code' => $order->order_code,
                        'product_code' => $product_code,
                        'product_id' => $product_id,
                        'product_name' => $product_name,
                        'quantity' => $quantity,
                        'quantity_set' => 1,
                        'price_sale' => round($price_sale2),//Tiền bán chưa thuế
                        'total_price_sale' => round($price_sale2) * $quantity,//Tổng tiền bán chưa thuế
                        'price_sale_tax' => round($price_sale_tax),//Tiền bán có thuế
                        'total_price_sale_tax' => round($total_price_sale_tax),//Tổng tiền bán có thuế
                        'cost_price' => round($cost_price),//Tiền mua chưa thuế
                        'total_price' => round($cost_price) * $quantity,//Tổng tiền mua chưa thuế
                        'cost_price_tax' => round($cost_price_tax),//Tiền mua có thuế
                        'total_price_tax' => round($total_cost_price_tax),//Tổng tiền mua có thuế
                        'site_type' => $order->site_type,
                        'tax' => $tax,
                        'discount' => $faker->randomNumber(3),
                        'supplied_id' => $supplier_id,
                        'supplied' => $supplier_name,
                        'supplier_zip1' => (!empty($supplier)) ? $supplier->zip01 : '',
                        'supplier_zip2' => (!empty($supplier)) ? $supplier->zip02 : '',
                        'supplier_addr1' => (!empty($supplier)) ? $supplier->addr01 : '',
                        'supplier_addr2' => (!empty($supplier)) ? $supplier->addr02 : '',
                        'supplier_tel1' => (!empty($supplier)) ? $supplier->tel01 : '',
                        'supplier_tel2' => (!empty($supplier)) ? $supplier->tel02 : '',
                        'supplier_tel3' => (!empty($supplier)) ? $supplier->tel03 : '',
                        'supplier_code_sagawa' => (!empty($supplier)) ? $supplier->supplier_code_sagawa : '',
                        'supplier_code_kuroneko' => (!empty($supplier)) ? $supplier->supplier_code_kuroneko : '',
                        'sku' => $product_code,
                        'maker_id' => $maker_id,
                        'maker_code' => $maker_code,
                        'delivery_method' => $faker->numberBetween(1,9),
                        'delivery_way' => $faker->numberBetween(1,4),
                        'ship_name1' => str_replace("\n", "", $faker->name),
                        'ship_name2' => str_replace("\n", "", $faker->name),
                        'ship_country' => 'JP',
                        'ship_address1' => str_replace("\n", "", $faker->address),
                        'ship_address2' => str_replace("\n", "", $faker->address),
                        'ship_zip' => $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).'-'.
                                      $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9),
                        'ship_phone' => $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).'-'.
                                        $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).'-'.
                                        $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9),
                        'delivery_fee' => $fee,
                        'delivery_payment' => $faker->randomNumber(4),
                        'es_delivery_date_from' => date('Y/m/d H:i:s', strtotime($purchase_date . ' +'.$faker->numberBetween(1,2).' day')),//Ngày tập kết hàng (ngày dự định xuất hàng)
                        'es_delivery_date_to' => date('Y/m/d H:i:s', strtotime($order->delivery_date . ' +'.$faker->numberBetween(1,2).' day')),//Ngày giao hàng (ngày dự định nhận hàng)
                        'es_delivery_date' => date('Y/m/d H:i:s', strtotime($order->delivery_date . ' -'.$faker->numberBetween(1,2).' day')),
                        'delivery_date_from' => $order->delivery_date,//Not sài
                        'delivery_date_to' => $order->delivery_date,//Not sài
                        'delivery_date' => $order->delivery_date,//Ngày shipping của order
                        'delivery_time' => $faker->time($format = 'H:i:s', $max = 'now'),//Giờ shipping
                        'receive_date' => $order->delivery_date,
                        'receive_time' => ($supplier['cargo_schedule_time_from'] != '') ? ($supplier['cargo_schedule_time_from'].'-'.$supplier['cargo_schedule_time_to']) : '',
                        'purchase_date' => $purchase_date,
                        'purchase_code' => $purchase_code,
                        'pay_request' => $index_ == 1 ? 1: 0
                    ]);
                }
            }
        }
    }
}
