<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tax_class')->nullable();
            $table->string('name', 255)->nullable();
            $table->integer('default_flg')->nullable();
            $table->date('apply_date')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('del_flg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_classes');
    }
}
