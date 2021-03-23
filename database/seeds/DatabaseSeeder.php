<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SupplierTableSeeder::class,
            UsersTableSeeder::class,
            OrderTableSeeder::class,
            SiteTypeTableSeeder::class,
            ImportTableSeeder::class,
            TaxClassTableSeeder::class,
            TaxDetailTableSeeder::class,
            ProductTableSeeder::class,
            OrderDetailTableSeeder::class,
            DeliveryMethodTableSeeder::class,
            PurchaseTableSeeder::class,
            ShipmentsTableSeeder::class,
            ProductStatusTableSeeder::class,
            GroupsTableSeeder::class,
            // InvoiceTableSeeder::class
            // ImportAmazonFbaHiroshimaTableSeeder::class,
            // ImportAmazonFbaWorldLiquorsTableSeeder::class,
            // ImportAmazonHiroshimaTableSeeder::class,
            // ImportYahooWorldOrderTableSeeder::class,
            // ImportAmazonFbaWorldTableSeeder::class,
            // ImportYahooWorlItemTableSeeder::class,
            // ImportEccubeTableSeeder::class,
            // ImportRakutenTableSeeder::class,
            // ShipmentTypeTableSeeder::class,
            // ProductCategoryTableSeeder::class,
            // OffWorkDateTableSeeder::class,
        ]);
    }
}
