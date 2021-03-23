<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type');
            $table->timestamp('date_import');
            $table->string('website',255);
            $table->integer('number_order');
            $table->integer('number_success')->nullable();
            $table->integer('number_error')->nullable();
            $table->integer('number_duplicate')->nullable();
            $table->date('import_set_from')->nullable();
            $table->date('import_set_to')->nullable();
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
        Schema::dropIfExists('imports');
    }
}
