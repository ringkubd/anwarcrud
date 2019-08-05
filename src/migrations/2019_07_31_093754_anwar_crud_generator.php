<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AnwarCrudGenerator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name",255);
            $table->string("controllers",255);
            $table->string("methods",255);
            $table->string("uri",255);
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
        Schema::table('anwar_crud_generator', function (Blueprint $table) {
            //
        });
    }
}
