<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('category_id');
            $table->string('name', 255)->nullable();
            $table->string('short_name', 255)->nullable();
            $table->integer('product_class_id')-> nullable();
            $table->integer('product_id')-> nullable();
            $table->string('code', 255)-> nullable();
            // $table->double('price_buy', 8,2)->nullable();
            $table->double('price_sale', 8,2)->nullable();
            $table->double('price_sale_2', 8,2)->nullable();
            // $table->double('cost_price_buy', 8,2)->nullable();
            // $table->double('cost_price_sale', 8,2)->nullable();
            // $table->double('price', 8,2)->nullable();
            $table->double('cost_price', 8,2)->nullable();
            $table->integer('point_rate')->nullable();
            $table->double('fee', 8,2)->nullable();
            $table->integer('supplied_id')->nullable();
            $table->integer('site_type')->nullable();
            $table->string('size')->nullable();
            $table->string('comment')->nullable();
            // $table->integer('delivery_method')->nullable();
            // $table->integer('deliv_date_id')->nullable();
            $table->string('sku', 255)->nullable();
            $table->string('maker_id', 255)->nullable();
            $table->string('maker_code', 255)->nullable();
            $table->string('note')->nullable();
            $table->integer('status')->nullable();
            // $table->integer('type')->nullable();
            $table->integer('group1_id')->nullable();
            $table->integer('group2_id')->nullable();
            $table->integer('group3_id')->nullable();
            $table->integer('group4_id')->nullable();
            $table->integer('group5_id')->nullable();
            $table->integer('product_del_flg')->nullable();
            $table->integer('product_class_del_flg')->nullable();
            $table->integer('tax_class')->nullable()->default('1');
            $table->smallInteger('handling_flg')->nullable()->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
