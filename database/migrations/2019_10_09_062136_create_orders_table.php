<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('site_type')->nullable(); 
            $table->integer('import_id')->nullable(); 
            $table->string('order_code', 255)->nullable(); 
            $table->timestamp('order_date')->nullable(); 
            $table->string('buyer_name1', 255)->nullable(); 
            $table->string('buyer_name2')->nullable(); 
            $table->string('buyer_name1_kana',255)->nullable(); 
            $table->string('buyer_name2_kana',255)->nullable(); 
            $table->string('buyer_country',255)->nullable(); 
            $table->string('buyer_address_1',255)->nullable(); 
            $table->string('buyer_address_2',255)->nullable(); 
            $table->string('buyer_address_3',255)->nullable(); 
            $table->string('buyer_address_1_kana',255)->nullable(); 
            $table->string('buyer_address_2_kana',255)->nullable(); 
            $table->string('buyer_address_3_kana',255)->nullable(); 
            $table->string('buyer_email',255)->nullable(); 
            $table->string('buyer_zip1',255)->nullable(); 
            $table->string('buyer_zip2',255)->nullable(); 
            $table->string('buyer_tel1',255)->nullable(); 
            $table->string('buyer_tel2',255)->nullable(); 
            $table->string('buyer_tel3',255)->nullable(); 
            $table->string('buyer_sex',255)->nullable(); 
            $table->timestamp('buyer_birthday')->nullable(); 
            $table->string('tax',255)->nullable(); 
            $table->string('fax',255)->nullable();
            $table->string('charge',255)->nullable(); 
            $table->double('sub_total',8,2)->nullable(); 
            $table->double('price_untax',8,2)->nullable(); 
            $table->double('order_delivery_fee', 8,2)->nullable(); 
            $table->double('order_gift_wrap_price',8,2)->nullable(); 
            $table->double('order_discount',8,2)->nullable(); 
            $table->double('order_total',8,2)->nullable(); 
            $table->integer('use_point')->nullable(); 
            $table->double('payment_total',8,2)->nullable(); 
            $table->string('order_site_charge',255)->nullable(); 
            $table->text('comments')->nullable(); 
            $table->string('noshi_type',255)->nullable(); 
            $table->string('noshi_name',255)->nullable();  
            $table->string('payment_id',255)->nullable(); 
            $table->string('payment_method',255)->nullable(); 
            $table->string('credit_type',255)->nullable(); 
            $table->timestamp('cargo_schedule_day')->nullable(); 
            $table->timestamp('cargo_schedule_time_from')->nullable(); 
            $table->timestamp('cargo_schedule_time_to')->nullable(); 
            $table->integer('status')->nullable(); //A-1: order mới, A-2: chờ nhập tiền, A-3: đang xử lý nhận order, A-4: cần xác nhận, A-5: đang bảo lưu, A-6: hoàn thành, A-7: hủy
            $table->integer('support_cus')->nullable();
            $table->integer('flag_confirm')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->double('money_daibiki', 8,2)->nullable();
            $table->integer('quantity_service')->nullable();
            $table->integer('price_service')->nullable();
            $table->integer('total_service')->nullable();
            $table->integer('number_of_copies')->nullable(); // số lần copy của order
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
        Schema::dropIfExists('orders');
    }
}
