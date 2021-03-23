<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {     
        DB::table('site_type')->insert([
            'id' => 0,
            'name' => 'Rimac'
        ]);   
        DB::table('site_type')->insert([
            'name' => '自社'
        ]);
        DB::table('site_type')->insert([
            'name' => '楽天'
        ]);
        DB::table('site_type')->insert([
            'name' => 'Yahoo'
        ]);
        DB::table('site_type')->insert([
            'name' => 'Amazonひろしま'
        ]);
        DB::table('site_type')->insert([
            'name' => 'Amazonワールド'
        ]);
        DB::table('site_type')->insert([
            'name' => 'AmazonひろしまFBA'
        ]);
        DB::table('site_type')->insert([
            'name' => 'AmazonワールドFBA'
        ]);
        DB::table('site_type')->insert([
            'name' => 'Amazonリカー'
        ]);
    }
}
