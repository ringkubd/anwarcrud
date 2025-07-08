<?php

namespace Anwar\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Anwar\CrudGenerator\Commands\CrudGeneratorCommand;
use Anwar\CrudGenerator\Commands\CreateModuleCommand;

class AnwarCrudGeneratorProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Load constants file
        require_once __DIR__ . '/constants.php';

        // Merge package configuration
        $this->mergeConfigFrom(__DIR__ . '/Configs/anwarcrud.php', 'anwarcrud');
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/views', 'CRUDGENERATOR');

        // Publish assets
        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/crudgenerator'),
        ], 'crudgenerator-assets');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations')
        ], 'crudgenerator-migrations');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/Configs/anwarcrud.php' => config_path('anwarcrud.php'),
        ], 'crudgenerator-config');

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/crudgenerator'),
        ], 'crudgenerator-views');

        // Publish stubs for customization
        $this->publishes([
            __DIR__ . '/stubs' => resource_path('crud-stubs'),
        ], 'crudgenerator-stubs');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGeneratorCommand::class,
                CreateModuleCommand::class,
            ]);
        }
    }
}
