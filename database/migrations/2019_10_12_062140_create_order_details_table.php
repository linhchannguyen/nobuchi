<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id');
            $table->string('order_code', 255);
            $table->integer('shipment_id')->nullable();
            $table->integer('purchase_id')->nullable();
            $table->string('product_code', 255)->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_name', 255)->nullable();
            $table->string('product_info')->nullable();
            $table->double('quantity', 8,2)->nullable();
            $table->integer('quantity_set')->nullable();
            $table->double('price_sale', 8,2)->nullable();
            $table->double('price_sale_tax', 8,2)->nullable();
            $table->double('total_price_sale', 8,2)->nullable();
            $table->double('total_price_sale_tax', 8,2)->nullable();
            $table->double('cost_price', 8,2)->nullable();//Giá mua
            $table->double('total_price', 8,2)->nullable();//Tổng giá mua
            $table->double('cost_price_tax', 8,2)->nullable();//Giá mua có thuế
            $table->double('total_price_tax', 8,2)->nullable();//Tổng giá mua có thuế
            $table->double('tax', 8,2)->nullable();
            $table->double('discount', 8,2)->nullable();
            $table->string('type', 255)-> nullable();
            $table->integer('site_type')->nullable();
            $table->integer('supplied_id')->nullable();
            $table->string('supplied', 255)->nullable();//name
            $table->string('supplier_zip1',255)->nullable(); 
            $table->string('supplier_zip2',255)->nullable(); 
            $table->string('supplier_addr1',255)->nullable(); 
            $table->string('supplier_addr2',255)->nullable(); 
            $table->string('supplier_addr3',255)->nullable(); 
            $table->string('supplier_tel1',255)->nullable(); 
            $table->string('supplier_tel2',255)->nullable(); 
            $table->string('supplier_tel3',255)->nullable(); 
            $table->string('supplier_code_sagawa',255)->nullable(); 
            $table->string('supplier_code_kuroneko',255)->nullable(); 
            $table->text('product_comments')->nullable();
            $table->string('product_name_sub', 255)->nullable();
            $table->string('product_name_org', 255)->nullable();
            $table->string('sku', 255)->nullable();
            $table->string('maker_id', 255)->nullable();
            $table->string('maker_code', 255)->nullable();
            $table->integer('delivery_method')->nullable();
            $table->integer('delivery_way')->nullable();
            $table->string('ship_name1')->nullable();
            $table->string('ship_name2')->nullable();
            $table->string('ship_name1_kana')->nullable();
            $table->string('ship_name2_kana')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('ship_address1')->nullable();
            $table->string('ship_address2')->nullable();
            $table->string('ship_address3')->nullable();
            $table->string('ship_address1_kana')->nullable();
            $table->string('ship_zip')->nullable();
            $table->string('ship_phone')->nullable();
            $table->double('delivery_fee', 8,2)->nullable();
            $table->double('delivery_payment', 8,2)->nullable();
            $table->timestamp('es_delivery_date')->nullable();
            $table->timestamp('es_delivery_date_from')->nullable();
            $table->timestamp('es_delivery_date_to')->nullable();
            $table->timestamp('delivery_date_from')->nullable();
            $table->timestamp('delivery_date_to')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->string('purchase_code')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->timestamp('receive_date')->nullable();
            $table->string('receive_time', 255)->nullable();
            // $table->string('comments')->nullable();
            $table->string('wrapping_paper_type')->nullable();
            $table->string('wrapping_ribbon_type')->nullable();
            $table->string('gift_wrap')->nullable();
            $table->string('gift_wrap_kind')->nullable();
            $table->string('gift_message')->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('pay_request')->nullable();
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
        Schema::dropIfExists('order_details');
    }
}
