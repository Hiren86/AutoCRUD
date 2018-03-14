<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_layouts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table');
            $table->string('layout');
            $table->string('form_type');
            $table->string('offset');
            $table->string('form_width');
            $table->string('side_div');
            $table->string('no_of_cols');
            $table->string('view_name');
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
        Schema::dropIfExists('page_layouts');
    }
}
