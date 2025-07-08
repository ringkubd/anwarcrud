<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AnwarCrudMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ANWAR_CRUD_MENUS, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name", 255);
            $table->string("controllers", 255);
            $table->string("methods", 255);
            $table->string("uri", 255);
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
        Schema::dropIfExists('anwar_crud_menus');
    }
}
