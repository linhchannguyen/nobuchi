<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;

class ImportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        foreach (range(1,5) as $index) { 
            $number = $faker->randomNumber(3);
            DB::table('imports')->insert([
                'type' => $faker->numberBetween(1,8),
                'date_import' => $faker->dateTimeBetween('2019-1-01', Carbon::now()),
                'website' => 'EC',
                'number_order' => $number, 
                'number_success'=> $number,
                'number_error'=> 0,
                'number_duplicate'=> 0,
            ]);
        }
    }
}
