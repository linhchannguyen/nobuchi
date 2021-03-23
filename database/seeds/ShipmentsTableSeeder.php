<?php

use Faker\Factory;
use App\Model\Purchases\Purchase;
use App\Model\Shipments\Shipment;
use App\Model\Orders\OrderDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {        
        $faker = Faker\Factory::create();
        $purchase_ = Purchase::all();
        $order_details = OrderDetail::orderBy('order_id', 'asc')->get();
        $shipments_insert = [];
        $add_ship = true;
        foreach ($order_details as $value) {
            $shipment_id = 0;
            if(!empty($shipments_insert))
            {
                foreach ($shipments_insert as $value_ship) {
                    if($value_ship['order_id'] == $value->order_id &&
                       $value_ship['shipment_date'] == $value->delivery_date&&
                       $value_ship['shipment_time'] == $value->receive_time&&
                       $value_ship['supplied_id'] == $value->supplied_id &&
                       $value_ship['delivery_way'] == $value->delivery_way)
                    {
                        $shipment_id = $value_ship['shipment_id'];
                        $add_ship = false;
                        break;
                    } else
                    {
                     $add_ship = true;
                    }
                }
            }
            if($add_ship == true)
            {
                $inser_shipment = new Shipment();
                $shipments = [
                    'es_shipment_date' => $value->es_delivery_date,//Ngày tập kết hàng (ngày dự định xuất hàng)
                    'es_shipment_time' => $value->receive_time,//Giờ dự định giao hàng
                    'shipment_date' => $value->delivery_date,//Ngày giao hàng (ngày dự định nhận hàng)
                    'shipment_time' => '0',
                    'shipment_code' => $faker->postcode(),
                    'supplied_id' => $value->supplied_id,
                    'shipment_customer' => $value->ship_name1,
                    'shipment_address' => $value->ship_address1.$value->ship_address2.$value->ship_address3,
                    'shipment_email' => $faker->email(),
                    'status' => $faker->numberBetween(1,9),
                    'shipment_zip' => $value->ship_zip,
                    'shipment_phone' => $value->ship_phone,
                    'delivery_method' => $value->delivery_method,
                    'delivery_way' => $value->delivery_way,
                    'pay_request' => $value->pay_request,
                    'shipment_quantity' => $value->quantity,
                ];
                $inser_shipment = $inser_shipment->create($shipments);
                $shipment_id = $inser_shipment->id;
                $shipments['shipment_id'] = $shipment_id;
                $shipments['order_id'] = $value->order_id;
                array_push($shipments_insert, $shipments);
            }
            DB::table('order_details')->where('id', $value->id)->update(['shipment_id' => $shipment_id]);
        }
    }
}
