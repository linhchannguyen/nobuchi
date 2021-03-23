<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private $products_model;
    private $ec_connect;
    public function run()
    {
        $faker = Faker\Factory::create();
        $this->ec_connect = DB::connection('eccube');
        $this->products_model = $this->ec_connect->table('dtb_products');
        $products = $this->products_model->select(
            'dtb_products_class.product_id', 'dtb_products_class.product_class_id', 'dtb_products.status', 'dtb_products.name', 'dtb_products.maker_id',
            'dtb_products.maker_code', 'dtb_products.deliv_date_id', 'dtb_products.note',
            'dtb_products.supplier_id', 'dtb_products.ec_deliv_id', 'dtb_products.short_name', 'dtb_products.handling_flg', 'dtb_products.tax_class',
            'dtb_products_class.product_type_id', 'dtb_products_class.product_code', 'dtb_products_class.stock', 'dtb_products_class.stock_unlimited',
            'dtb_products_class.sale_limit', 'dtb_products_class.price01', 'dtb_products_class.price02', 'dtb_products_class.cost_price', 'dtb_products_class.deliv_fee', 
            'dtb_products_class.point_rate', 'dtb_products_class.create_date', 'dtb_products_class.update_date', 
            'dtb_products.group1_id', 'dtb_products.group2_id', 'dtb_products.group3_id', 'dtb_products.group4_id',
            'dtb_products.group5_id', 'dtb_products_class.del_flg as product_class_del_flg', 'dtb_products.del_flg as product_del_flg'
        )->join('dtb_products_class', 'dtb_products.product_id', '=', 'dtb_products_class.product_id')
        ->where('dtb_products.del_flg', 0)->where('dtb_products_class.del_flg', 0)->get();
        foreach($products as $value){
            $products = array();
            $products['category_id'] = $value->product_type_id;
            $products['name'] = $value->name;
            $products['short_name'] = $value->short_name;
            $products['product_class_id'] = $value->product_class_id;
            $products['product_id'] = $value->product_id;
            $products['code'] = $value->product_code;
            $products['note'] = ((self::checkNumber($value->note) && !empty($value->note)) ? $value->note: 1);
            $products['price_sale'] = $value->price01;//Giá thường
            $products['price_sale_2'] = $value->price02;//Giá bán chưa thuế
            $products['cost_price'] = $value->cost_price;//Nguyên giá chưa thuế
            $products['supplied_id'] = $value->supplier_id;
            // $products['delivery_method'] = $faker->numberBetween(1,8);//$value->ec_deliv_id;
            $products['fee'] = $value->deliv_fee;
            $products['sku'] =  $value->product_code;
            $products['maker_id'] =  $value->maker_id;
            $products['maker_code'] =  $value->maker_code;
            $products['status'] = $value->status;//Trạng thái sp: 1: public, 2: private
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
            $products['tax_class'] = $value->tax_class;//Thuế: 1 = 10%, 2 = 8%
            DB::table('products')->insert($products);
        }
    }
    
    public function checkNumber($string){
        for($i = 0; $i < strlen($string); $i++){
            if(is_numeric($string[$i]) == false){
                return false;
            }
        }
        return true;
    }
}
