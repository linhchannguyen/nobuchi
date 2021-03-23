<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->ec_connect = DB::connection('eccube');
        $this->product_status_model = $this->ec_connect->table('dtb_product_status');
        $product_status_model = $this->product_status_model->selectRaw('dtb_product_status.product_status_id, dtb_product_status.del_flg, product_id,
                                                                        mtb_status.name as name_status, create_date as created_date, update_date as updated_date')
        ->join('mtb_status', 'dtb_product_status.product_status_id', '=', 'mtb_status.id')->get()->toArray();
        foreach($product_status_model as $value) {
            $data_insert = [
                'product_status_id' => $value->product_status_id,
                'product_id' => $value->product_id,
                'del_flg' => $value->del_flg,
                'created_at' => $value->created_date,
                'updated_at' => $value->updated_date
            ];
            DB::table('product_statuses')->insert($data_insert);
        }
    }
}
