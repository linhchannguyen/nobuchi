<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('pref', 255)->nullable();
            $table->string('fax01', 255)->nullable();
            $table->string('fax02', 255)->nullable();
            $table->string('fax03', 255)->nullable();
            $table->string('tel01', 255)->nullable(); 
            $table->string('tel02', 255)->nullable();
            $table->string('tel03', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('addr01', 255)->nullable();
            $table->string('addr02', 255)->nullable();
            $table->string('zip01',255)->nullable();
            $table->string('zip02',255)->nullable();
            $table->integer('del_flg')->nullable();
            $table->integer('creator_id')->nullable();
            $table->timestamp('create_date')->nullable();
            $table->timestamp('update_date')->nullable();
            $table->string('staff', 255)->nullable();
            $table->string('rank', 255)->nullable();
            $table->string('supplier_code_sagawa',255)->nullable();
            $table->string('supplier_code_kuroneko',255)->nullable();
            $table->string('cargo_schedule_day',255)->nullable();
            $table->string('cargo_schedule_time_from',255)->nullable();
            $table->string('cargo_schedule_time_to',255)->nullable();
            $table->integer('edi_type')->nullable();
            $table->integer('holiday_sun')->nullable();
            $table->integer('holiday_mon')->nullable();
            $table->integer('holiday_tue')->nullable();
            $table->integer('holiday_wed')->nullable();
            $table->integer('holiday_thu')->nullable();
            $table->integer('holiday_fri')->nullable();
            $table->integer('holiday_sat')->nullable();
            $table->integer('supplier_class')->nullable();
            $table->integer('shipping_method')->nullable();
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
        Schema::dropIfExists('suppliers');
    }
}
