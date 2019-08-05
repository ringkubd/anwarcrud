<?php
Route::get("controller_list","Anwar\CrudGenerator\Controllers\ModuleGenerator@index");
Route::post("getColumns","Anwar\CrudGenerator\Controllers\ModuleGenerator@getCollumns");
Route::post("final","Anwar\CrudGenerator\Controllers\ModuleGenerator@finalSubmit");

$routelist =  \DB::table("anwar_crud_generator")->select(["*"])->get();


$method = ["index"=>"get","create"=>"get","store"=>"post","edit"=>"get","delete"=>"get"];

if (!function_exists("makeRoute")){
    function makeRoute($class,$modulename){
        $classfile = "App\Http\Controller\\".$class;
        $method = ["index"=>"get","create"=>"get","store"=>"post","edit"=>"get","delete"=>"get"];
        if (class_exists($classfile)){
            foreach (array_keys($method) as $meth){
                if (method_exists(new $classfile(),$meth)){
                    Route::$method[$meth]("$modulename",$classfile."@$meth");
                }
            }

        }
    }
}


foreach ($routelist as $route){
    makeRoute($route->controllers,$route->name);
}
