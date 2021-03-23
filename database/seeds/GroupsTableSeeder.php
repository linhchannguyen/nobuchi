<?php

use App\Model\Groups\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private $groups_model;
    private $ec_connect;
    public function run()
    {
        // 
        $group = new Group();       
        $faker = Faker\Factory::create();
        $this->ec_connect = DB::connection('eccube');
        $this->groups_model = $this->ec_connect->table('dtb_group');
        $groups = $this->groups_model->select('id', 'name', 'level', 'del_flg', 'create_date')->orderBy('id')->get();
        foreach($groups as $key => $value){
            $groups = array();
            $groups['id'] = $value->id;
            $groups['name'] = $value->name;
            $groups['level'] = $value->level;
            $groups['del_flg'] = $value->del_flg;
            $groups['create_date'] = $value->create_date;
            $group->insert($groups);
        }
    }
}
