<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Suppliers\Supplier;
use Faker\Generator as Faker;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        foreach (range(1,300) as $index_) { 
            $index = $faker->numberBetween(1,20);
            $supplier_id = Supplier::where('id', $index)->pluck('id')->first();
            $date = $faker->dateTimeBetween('2019-01-01', '2020-1-31')->format('Y/m/d h:i:s');
            DB::table('orders')->insert([
                'site_type' =>$faker->numberBetween(1,8), 
                'import_id' =>$faker->numberBetween(1,5), 
                'order_code' => "DUMMY-".$faker->randomNumber(3).'-'.$faker->randomNumber(3).'-'.$faker->randomNumber(3), 
                'order_date' => $date,
                'buyer_name1' => str_replace("\n", "", $faker->name),
                'buyer_name2' => str_replace("\n", "", $faker->name),
                'buyer_country' => 'Japan',
                'buyer_address_1' => str_replace("\n", "", $faker->address),
                'buyer_address_2'=> str_replace("\n", "", $faker->address),
                'buyer_email' => $faker->email, 
                'buyer_zip1' => $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9),
                'buyer_zip2' => $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9),
                'buyer_tel1' => $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).'-'.
                                $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).'-'.
                                $faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9).$faker->numberBetween(0,9),
                'buyer_tel2' => '',
                'buyer_tel3' => '',
                'tax' => '0',
                'charge' => '0',
                'sub_total'  => $faker->randomNumber(5),
                'order_delivery_fee'  => $faker->randomNumber(5),
                'order_gift_wrap_price'  => $faker->randomNumber(5), 
                'order_discount'  => $faker->randomNumber(5), 
                'order_total' => $faker->randomNumber(5), 
                'use_point' => '0', 
                'payment_total' => $faker->randomNumber(5), 
                'order_site_charge' => $faker->randomNumber(5),         
                'payment_id'  => $faker->randomNumber(1), 
                'payment_method' => 'クレジットカード', 
                'credit_type' => 'VISA',
                'status' => $faker->numberBetween(1,7), 
                'support_cus' => $faker->numberBetween(1,7),
                'flag_confirm' => $faker->numberBetween(0,2),
                'purchase_date' => $date,
                'delivery_date' =>  date('Y/m/d H:i:s', strtotime($date . ' +'.$faker->numberBetween(1,4).' day')),
                'created_at' => $faker->dateTimeBetween('2018-10-01', '2018-12-30'),
            ]);
        }
    }
}
