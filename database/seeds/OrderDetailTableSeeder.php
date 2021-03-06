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
                        'price_sale' => round($price_sale2),//Ti???n b??n ch??a thu???
                        'total_price_sale' => round($price_sale2) * $quantity,//T???ng ti???n b??n ch??a thu???
                        'price_sale_tax' => round($price_sale_tax),//Ti???n b??n c?? thu???
                        'total_price_sale_tax' => round($total_price_sale_tax),//T???ng ti???n b??n c?? thu???
                        'cost_price' => round($cost_price),//Ti???n mua ch??a thu???
                        'total_price' => round($cost_price) * $quantity,//T???ng ti???n mua ch??a thu???
                        'cost_price_tax' => round($cost_price_tax),//Ti???n mua c?? thu???
                        'total_price_tax' => round($total_cost_price_tax),//T???ng ti???n mua c?? thu???
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
                        'es_delivery_date_from' => date('Y/m/d H:i:s', strtotime($purchase_date . ' +'.$faker->numberBetween(1,2).' day')),//Ng??y t???p k???t h??ng (ng??y d??? ?????nh xu???t h??ng)
                        'es_delivery_date_to' => date('Y/m/d H:i:s', strtotime($order->delivery_date . ' +'.$faker->numberBetween(1,2).' day')),//Ng??y giao h??ng (ng??y d??? ?????nh nh???n h??ng)
                        'es_delivery_date' => date('Y/m/d H:i:s', strtotime($order->delivery_date . ' -'.$faker->numberBetween(1,2).' day')),
                        'delivery_date_from' => $order->delivery_date,//Not s??i
                        'delivery_date_to' => $order->delivery_date,//Not s??i
                        'delivery_date' => $order->delivery_date,//Ng??y shipping c???a order
                        'delivery_time' => $faker->time($format = 'H:i:s', $max = 'now'),//Gi??? shipping
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
