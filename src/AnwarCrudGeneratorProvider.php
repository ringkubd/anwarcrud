<?php

namespace Anwar\CrudGenerator;

use Anwar\CrudGenerator\Commands\CrudGeneratorCommand;
use Anwar\CrudGenerator\Commands\CreateModuleCommand;
use Illuminate\Support\Facades\Artisan;
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
        // Load constants file
        require_once __DIR__ . '/constants.php';
        require_once __DIR__ . '/web.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/views', 'CRUDGENERATOR');
        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/crudgenerator'),
        ], 'CRUDGENERATOR');

        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations')
        ], 'CRUDGENERATOR');

        /**
         * @desc Register Configs file
         */

        $configFile = [];
        foreach (new \DirectoryIterator(__DIR__ . '/Configs') as $file) {
            if ($file->isFile()) {
                $configFile[__DIR__ . '/Configs/' . $file->getFilename()] = config_path($file->getFilename());
            }
        }

        $this->publishes($configFile, 'CRUDGENERATOR');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGeneratorCommand::class,
                CreateModuleCommand::class,
            ]);
        }
    }
}
