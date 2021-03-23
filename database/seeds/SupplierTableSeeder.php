<?php

use App\Model\Suppliers\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private $dtb_order;
    private $order_model;
    private $ec_connect;
    public function run()
    {
        $faker = Faker\Factory::create();
        $this->dtb_order = 'dtb_supplier';
        $this->ec_connect = DB::connection('eccube');
        $this->order_model = $this->ec_connect->table($this->dtb_order);
        $supplier = $this->order_model->selectRaw('dtb_supplier.*, mtb_pref.name as addr')->leftJoin('mtb_pref', 'mtb_pref.id', 'dtb_supplier.pref')->get();
            DB::table('suppliers')->insert([
                'id' => 0,
                'name' => 'NPO法人クローバープロジェクト21',
                'zip01' => '733',
                'zip02' => '0832',
                'addr01' => '広島県広島市西区草津港1丁目8-1',
                'addr02' => '広島市中央卸売市場関連棟238番',
                'tel01' => '082',
                'tel02' => '276',
                'tel03' => '7500',
                'fax01' => '082',
                'fax02' => '276',
                'fax03' => '7500',
                ]);
        foreach($supplier as $value){
            DB::table('suppliers')->insert([
                'id' => $value->supplier_id,
                'name' => $value->name,
                'zip01' => $value->zip01,
                'zip02' => $value->zip02,
                'pref' => $value->addr,
                'addr01' => $value->addr01,
                'addr02' => $value->addr02,
                'email' => $value->email,
                'tel01' => $value->tel01,
                'tel02' => $value->tel02,
                'tel03' => $value->tel03,
                'fax01' => $value->fax01,
                'fax02' => $value->fax02,
                'fax03' => $value->fax03,
                'staff' => $value->staff,
                'supplier_code_sagawa' => $value->supplier_code_sagawa,
                'rank' => $value->rank,
                'creator_id' => $value->creator_id,
                'create_date' => $value->create_date,
                'update_date' => $value->update_date,
                'del_flg' => $value->del_flg,
                'supplier_code_kuroneko' => $value->supplier_code_kuroneko,
                'cargo_schedule_day' => $value->cargo_schedule_day,
                'cargo_schedule_time_from' => $value->cargo_schedule_time_from,
                'cargo_schedule_time_to' => $value->cargo_schedule_time_to,
                'edi_type' => $value->edi_type,
                'holiday_sun' => $value->holiday_sun,
                'holiday_mon' => $value->holiday_mon,
                'holiday_tue' => $value->holiday_tue,
                'holiday_wed' => $value->holiday_wed,
                'holiday_thu' => $value->holiday_thu,
                'holiday_fri' => $value->holiday_fri,
                'holiday_sat' => $value->holiday_sat,
                'supplier_class' => $value->supplier_class,
                'shipping_method' => $faker->numberBetween(1,8)
            ]);
        }
    }
}
