<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryMethodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('delivery_methods')->insert([
            'delivery_name' => '佐川急便'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'ヤマト宅急便'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'ネコポス'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'コンパクト便'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'ゆうパック'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'ゆうパケット'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => '代引き'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => 'その他'
        ]);
        DB::table('delivery_methods')->insert([
            'delivery_name' => '佐川急便(秘伝II)'
        ]);
    }
}
