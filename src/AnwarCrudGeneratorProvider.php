<?php

namespace Anwar\CrudGenerator;

use Anwar\CrudGenerator\Commands\CrudGeneratorCommand;
use Anwar\CrudGenerator\Commands\CreateModuleCommand;
use Illuminate\Support\ServiceProvider;

class AnwarCrudGeneratorProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__."/web.php";
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__."/migrations");
        $this->loadViewsFrom(__DIR__."/views","CRUDGENERATOR");
        $this->publishes([
            __DIR__.'/assets' => public_path('vendor/crudgenerator'),
        ], 'CRUDGENERATOR');

        /**
         * @desc Register Artisan console commands
         */
       if ($this->app->runningInConsole()){
            $this->commands([
                CrudGeneratorCommand::class,
                CreateModuleCommand::class
            ]);
       }

        /**
         * @desc Register Configs file
         */

        $configFile = [];
        foreach (new \DirectoryIterator(__DIR__.'/configs') as $file){
            if ($file->isFile()){
                $configFile[__DIR__."/configs/".$file->getFilename()] = config_path($file->getFilename());
            }
        }

        $this->publishes($configFile,"CRUDGENERATOR");
    }
}
