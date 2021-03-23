<?php

use App\Model\Purchases\Purchase;
use App\Model\Products\Product;
use Illuminate\Database\Seeder;
use App\Model\Orders\OrderDetail;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class PurchaseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $o_detail = OrderDetail::all();
        foreach (range(1,count($o_detail)) as $index) { 
            $order_detail = OrderDetail::where('id', $index)->first();
            $quantity = $order_detail->quantity;
            $supplier_id = $order_detail->supplied_id;
            $order_id = $order_detail->order_id;
            $order_detail_id = $order_detail->id;
            $od_purchase_code = $order_detail->purchase_code;
            $od_purchase_date = $order_detail->purchase_date;
            
            $inser_purchase = new Purchase();
            $purchases = [           
                'purchase_code' => $od_purchase_code, 
                'supplier_id' => $supplier_id, 
                'order_id' => $order_id,
                'status' => $faker->numberBetween(1,5), //1: Chưa xử lý, 2: Đã đặt hàng (đã in), 3: Đã tạo shipment, 4: Đã thông báo xuất hàng, 5: Hủy
                // 'original_cost' =>$faker->numberBetween(100, 1000), 
                // 'total'=> $faker->numberBetween(100,100000),                 
                'purchase_quantity' => $quantity,
                'cost_price' => round($order_detail->cost_price),//Tiền mua hàng chưa thuế
                'cost_price_tax' => round($order_detail->cost_price_tax),//Tiền mua hàng có thuế
                'total_cost_price' => round($order_detail->total_price),//Tổng tiền mua hàng chưa thuế
                'total_cost_price_tax' => round($order_detail->total_price_tax),//Tổng tiền mua hàng có thuế
                // 'confirm_date' => $faker->dateTimeBetween('2019-01-01', '2019-05-30'), 
                // 'confirm_by' => $faker->name,
                'flag_download' => $faker->numberBetween(0, 1),//0: chua download, 1: da download
                // 'flag_confirm_supplier' => $faker->numberBetween(0, 1),//0: chua giao hang, 1: da giao hang
                'purchase_date' => $od_purchase_date,
                'created_at' => $faker->dateTimeBetween('2019-01-01', '2019-05-30')
            ];
            $data_purchases = $inser_purchase->create($purchases);
            DB::table('order_details')->where('id', $order_detail_id)->update(['purchase_id' => $data_purchases->id]);
        }
    }
}
