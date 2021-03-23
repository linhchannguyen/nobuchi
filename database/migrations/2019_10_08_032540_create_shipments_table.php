<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shipment_code', 255)->nullable();
            // $table->integer('order_id')->nullable();
            $table->integer('shipment_quantity')->nullable();
            // $table->double('cost_price', 8,2)->nullable();//Tiền mua hàng chưa thuế
            // $table->double('cost_price_tax', 8,2)->nullable();//Tiền mua hàng có thuế
            // $table->double('total_cost_price', 8,2)->nullable();//Tổng tiền mua hàng chưa thuế
            // $table->double('total_cost_price_tax', 8,2)->nullable();//Tổng tiền mua hàng có thuế
            // $table->double('price_edit', 8,2)->nullable()->default('0');
            // $table->timestamp('confirm_date')->nullable();
            // $table->string('confirm_by',255)->nullable();
            // $table->integer('flag_download')->nullable()->default('0');
            // $table->integer('flag_confirm_supplier')->nullable()->default('0');
            $table->string('shipment_customer', 255)->nullable();
            $table->string('shipment_address', 255)->nullable();
            $table->string('shipment_email', 255)->nullable();
            $table->string('shipment_fax', 255)->nullable();
            $table->string('shipment_phone', 255)->nullable();
            $table->string('shipment_at', 255)->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('status')->nullable();//Trạng thái shipment dùng khi xuất file yahoo
            $table->timestamp('shipment_date')->nullable();//Ngày giao hàng
            $table->timestamp('receive_date')->nullable();//Ngày nhận hàng
            $table->string('receive_time', 255)->nullable();//Giờ nhận hàng
            $table->string('shipment_time', 255)->nullable();//Giờ giao hàng
            $table->integer('delivery_method')->nullable();
            $table->integer('delivery_way')->nullable();
            $table->string('shipment_zip', 255)->nullable();
            $table->string('shipping_pref', 255)->nullable();
            $table->double('shipment_fee', 8,2)->nullable();
            $table->double('shipment_payment', 8,2)->nullable();
            $table->string('created_by', 255)->nullable();
            $table->string('updated_by', 255)->nullable();
            $table->tinyInteger('del_flg')->nullable();
            $table->tinyInteger('pay_request')->nullable();
            $table->string('deleted_by', 255)->nullable();
            $table->integer('invoice_id')->nullable(); // loại công ty shipment
            $table->integer('supplied_id')->nullable();//Nhà cung cấp
            $table->timestamp('es_shipment_date')->nullable();//Ngày dự định giao hàng
            $table->string('es_shipment_time', 255)->nullable();//Giờ dự định giao hàng
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
        Schema::dropIfExists('shipments');
    }
}
