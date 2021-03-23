<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxDetailTableSeeder extends Seeder
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
        $this->tax_detail = $this->ec_connect->table('mtb_tax_detail');
        $tax_detail = $this->tax_detail->get()->toArray();
        foreach($tax_detail as $value){
            $tax_detail = array();
            $tax_detail['id'] = $value->id;
            $tax_detail['tax_class'] = $value->tax_class;
            $tax_detail['apply_date'] = $value->apply_date;
            $tax_detail['tax_rate'] = $value->tax_rate;
            $tax_detail['tax_rule'] = $value->tax_rule;
            $tax_detail['mark'] = $value->mark;
            $tax_detail['memo'] = $value->memo;
            $tax_detail['create_date'] = $value->create_date;
            $tax_detail['update_date'] = $value->update_date;
            DB::table('tax_details')->insert($tax_detail);
        }
    }
}
