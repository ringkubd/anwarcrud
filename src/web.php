<?php
Route::get("controller_list","Anwar\CrudGenerator\Controllers\ModuleGenerator@index");
Route::post("getColumns","Anwar\CrudGenerator\Controllers\ModuleGenerator@getCollumns");
Route::post("getFormView","Anwar\CrudGenerator\Controllers\ModuleGenerator@getFormView");
Route::post("final","Anwar\CrudGenerator\Controllers\ModuleGenerator@finalSubmit");

$routelist =  \DB::table("anwar_crud_generator")->select(["*"])->get();


$method = ["index"=>"get","create"=>"get","store"=>"post","edit"=>"get","delete"=>"get"];

if (!function_exists("makeRoute")){
    function makeRoute($class,$modulename){
        $classfile = "\\App\\Http\Controllers\\".$class;
        $method = ["index"=>"get","create"=>"get","store"=>"post","edit"=>"get","delete"=>"delete","update"=>"update"];
        $route =  new Route();
        if (class_exists($classfile)){
            foreach (array_keys($method) as $meth){
                if (method_exists(new $classfile(),$meth)){
                    switch ($method[$meth]){
                        case "put":
                            Route::put($modulename."/$meth",$classfile."@$meth");
                        case "patch":
                            Route::patch($modulename."/$meth",$classfile."@$meth");
                        case "post":
                            Route::post($modulename."/$meth",$classfile."@$meth");
                        case "get":
                            if ($meth ==  "edit"){
                                Route::get($modulename."/{id}"."/$meth",$classfile."@$meth");
                            }else{
                                Route::get($modulename."/$meth",$classfile."@$meth");
                            }
                        case "update":
                            Route::post($modulename."/{id}"."/$meth",$classfile."@$meth");
                        case "delete":
                            Route::get($modulename."/{id}"."/$meth",$classfile."@$meth");

                        default:
                            Route::get($modulename."/$meth",$classfile."@$meth");
                            //Route::
                    }

                }

            }
        }


    }
}

Route::prefix("admin")->group(function ()use($routelist){
    foreach ($routelist as $route){
        makeRoute($route->controllers,$route->name);
    }
});

