<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJoinTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('field_name');
            $table->integer('order');
            $table->string('common_key');
            $table->string('table_name');
            $table->string('view_name');
            $table->boolean('is_unique');
            $table->string('union_ref_id');
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
        Schema::dropIfExists('join_tables');
    }
}
