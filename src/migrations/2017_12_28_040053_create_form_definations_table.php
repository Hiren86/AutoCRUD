<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormDefinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_definations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('max')->nullable();
            $table->string('min')->nullable();
            $table->string('step')->nullable();
            $table->string('pattern')->nullable();
            $table->string('rows')->nullable();
            $table->string('required')->nullable();
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
        Schema::dropIfExists('form_definations');
    }
}
