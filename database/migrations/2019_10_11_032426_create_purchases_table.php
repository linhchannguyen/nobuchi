<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_code',255)->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('supplier_id')->nullable();
            // $table->double('original_cost',8,2)->nullable();
            // $table->double('total', 8,2)->nullable();
            $table->integer('status')->default('1'); //B-1: chưa xử lý, B-2: đã đặt hàng (đã in), B-3: đã tạo shipment, B-4: đã thông báo xuất hàng, B-5: hủy
            $table->double('purchase_quantity', 8,2)->nullable();//Tiền mua hàng chưa thuế
            $table->double('cost_price', 8,2)->nullable();//Tiền mua hàng chưa thuế
            $table->double('cost_price_tax', 8,2)->nullable();//Tiền mua hàng có thuế
            $table->double('total_cost_price', 8,2)->nullable();//Tổng tiền mua hàng chưa thuế
            $table->double('total_cost_price_tax', 8,2)->nullable();//Tổng tiền mua hàng có thuế
            $table->double('price_edit', 8,2)->nullable()->default('0');
            // $table->timestamp('confirm_date')->nullable();
            // $table->string('confirm_by',255)->nullable();
            $table->integer('flag_download')->nullable()->default('0');//Cờ xác nhận download
            // $table->integer('flag_confirm_supplier')->nullable()->default('0');
            $table->timestamp('purchase_date')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
