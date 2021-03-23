<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_processes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('process_user', 255)->nullable();
            $table->integer('process_permission')->nullable();
            $table->string('process_screen', 255)->nullable();
            $table->text('process_description')->nullable();
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
        Schema::dropIfExists('history_processes');
    }
}
