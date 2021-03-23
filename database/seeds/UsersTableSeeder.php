<?php

use App\Model\Suppliers\Supplier;
use Illuminate\Database\Seeder;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@admin.net',
            'login_id' => 'admin',
            'password' => bcrypt('123456789'),
            'department' => 'ADMIN',
            'salt' => 'vietvang',
            'authority' => 'admin',
            'rank' => 'QUAN TRI HE THONG',
            'type' => 0
        ]);
        foreach (range(1,45) as $index) { 
            DB::table('users')->insert([
                'name' => $faker->name(),
                'email' => $faker->email(),
                'login_id' => 'supplier'.$index,
                'password' => bcrypt('123456789'),
                'department' => 'SUPPLIER',
                'salt' => 'vietvang',
                'authority' => 'supplier',
                'rank' => 'SUPPLIER',
                'type' => 2,
                'supplier_id' => ($index == 1) ? 59 : Supplier::where('id', $faker->numberBetween(50,100))->pluck('id')->first()
            ]);
        }
    }
}
