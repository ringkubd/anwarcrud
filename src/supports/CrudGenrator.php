<?php

namespace Anwar\CrudGenerator\Supports;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CrudGenrator
{
    /**
     * @var string
     */
    private $controller_name = "";
    /**
     * @var string
     */
    private $table = "";

    public function createModule(Request $request){
        $this->controller_name = $request->controller;
        $this->table = $request->table;
    }

    public function create_controller(){
        $artisan = Artisan::call("make:model $this->controller_name --cr");
    }


}
