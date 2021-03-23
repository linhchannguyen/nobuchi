<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private $tax_detail;
    private $ec_connect;
    public function run()
    {
        $this->ec_connect = DB::connection('eccube');
        $this->tax_detail = $this->ec_connect->table('mtb_tax_class');
        $tax_detail = $this->tax_detail->get();
        foreach($tax_detail as $value){
            $tax_detail = array();
            $tax_detail['id'] = $value->id;
            $tax_detail['tax_class'] = $value->tax_class;
            // $tax_detail['name'] = $value->name;
            $tax_detail['default_flg'] = $value->default_flg;
            $tax_detail['apply_date'] = $value->apply_date;
            $tax_detail['rank'] = $value->rank;
            $tax_detail['del_flg'] = $value->del_flg;
            DB::table('tax_classes')->insert($tax_detail);
        }
    }
}
