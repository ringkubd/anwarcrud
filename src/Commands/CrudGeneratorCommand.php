<?php

namespace Anwar\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Anwar\CrudGenerator\Supports\ScaffoldGenerator;
use Anwar\CrudGenerator\Supports\Constants;

class CrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anwar:crudgenerator
    {module? : The name of the module (e.g. Post)}
    {--fields= : Fields for the model (e.g. title:string,body:text)}
    {--api : Generate API controller and routes}
    {--softdeletes : Use soft deletes in the model}
    {--relationships= : Relationships (e.g. user:belongsTo,comments:hasMany)}
';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install laravel simple crud generator';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $this->info("Laravel CRUD Generator");
        $this->info(str_repeat("-", 40));

        // Step 1: Get module name
        $module = $this->argument('module');
        if (!$module) {
            $module = $this->ask('Enter the module name (e.g. Post)');
        }

        // Step 2: Get fields
        $fields = $this->option('fields');
        if (!$fields) {
            $fields = $this->ask('Enter fields (e.g. title:string,body:text)');
        }

        // Step 3: API option
        $api = $this->option('api');
        if ($api === null) {
            $api = $this->confirm('Generate API controller and routes?', false);
        }

        // Step 4: Soft deletes
        $softdeletes = $this->option('softdeletes');
        if ($softdeletes === null) {
            $softdeletes = $this->confirm('Use soft deletes in the model?', false);
        }

        // Step 5: Relationships
        $relationships = $this->option('relationships');
        if (!$relationships) {
            $relationships = $this->ask('Enter relationships (e.g. user:belongsTo,comments:hasMany)', '');
        }

        $this->info("");
        $this->info("Summary:");
        $this->info("Module: $module");
        $this->info("Fields: $fields");
        $this->info("API: " . ($api ? 'Yes' : 'No'));
        $this->info("Soft Deletes: " . ($softdeletes ? 'Yes' : 'No'));
        $this->info("Relationships: $relationships");
        $this->info(str_repeat("-", 40));

        // Parse fields
        $fieldsArray = [];
        if ($fields) {
            foreach (explode(',', $fields) as $field) {
                $parts = explode(':', $field);
                $fieldsArray[] = [
                    'name' => trim($parts[0]),
                    'type' => isset($parts[1]) ? trim($parts[1]) : 'string',
                ];
            }
        }

        // Parse relationships
        $relationshipsArray = [];
        if ($relationships) {
            foreach (explode(',', $relationships) as $rel) {
                $parts = explode(':', $rel);
                if (count($parts) === 2) {
                    $relationshipsArray[] = [
                        'name' => trim($parts[0]),
                        'type' => trim($parts[1]),
                    ];
                }
            }
        }

        // Prepare data for scaffolding
        $scaffold = [
            'module' => $module,
            'fields' => $fieldsArray,
            'api' => $api,
            'softdeletes' => $softdeletes,
            'relationships' => $relationshipsArray,
        ];

        $this->info('Scaffold data preview:');
        $this->line(print_r($scaffold, true));
        $this->info('Ready for file generation in the next step.');

        // === File Generation Step 1: Model ===
        $modelPath = base_path('app/Models/' . ucfirst($scaffold['module']) . '.php');
        $modelStub = __DIR__ . '/../stubs/model.stub';
        ScaffoldGenerator::generateModel($scaffold, $modelStub, $modelPath);
        $this->info('Model generated: ' . $modelPath);

        // === File Generation Step 2: Migration ===
        $migrationStub = __DIR__ . '/../stubs/migration.stub';
        $timestamp = date('Y_m_d_His');
        $migrationName = $timestamp . '_create_' . strtolower(str_plural($scaffold['module'])) . '_table.php';
        $migrationPath = base_path('database/migrations/' . $migrationName);
        ScaffoldGenerator::generateMigration($scaffold, $migrationStub, $migrationPath);
        $this->info('Migration generated: ' . $migrationPath);

        // === File Generation Step 3: Controller ===
        $controllerStub = __DIR__ . '/../stubs/controller.stub';
        $controllerDir = $api ? base_path('app/Http/Controllers/Api') : base_path('app/Http/Controllers');
        if (!is_dir($controllerDir)) mkdir($controllerDir, 0777, true);
        $controllerPath = $controllerDir . '/' . ucfirst($scaffold['module']) . 'Controller.php';
        ScaffoldGenerator::generateController($scaffold, $controllerStub, $controllerPath);
        $this->info('Controller generated: ' . $controllerPath);

        // === File Generation Step 4: API Request/Resource (if API) ===
        if ($api) {
            $requestStub = __DIR__ . '/../stubs/request.stub';
            $requestDir = base_path('app/Http/Requests');
            if (!is_dir($requestDir)) mkdir($requestDir, 0777, true);
            $requestPath = $requestDir . '/' . ucfirst($scaffold['module']) . 'Request.php';
            \Anwar\CrudGenerator\Supports\ScaffoldGenerator::generateRequest($scaffold, $requestStub, $requestPath);
            $this->info('FormRequest generated: ' . $requestPath);

            $resourceStub = __DIR__ . '/../stubs/resource.stub';
            $resourceDir = base_path('app/Http/Resources');
            if (!is_dir($resourceDir)) mkdir($resourceDir, 0777, true);
            $resourcePath = $resourceDir . '/' . ucfirst($scaffold['module']) . 'Resource.php';
            \Anwar\CrudGenerator\Supports\ScaffoldGenerator::generateResource($scaffold, $resourceStub, $resourcePath);
            $this->info('Resource generated: ' . $resourcePath);
        }

        // === File Generation Step 5: Views (if not API) ===
        if (!$api) {
            $viewStubsDir = __DIR__ . '/../stubs';
            $viewDir = base_path('resources/views/' . strtolower($scaffold['module']));
            \Anwar\CrudGenerator\Supports\ScaffoldGenerator::generateViews($scaffold, $viewStubsDir, $viewDir);
            $this->info('Views generated in: ' . $viewDir);
        }

        // === File Generation Step 6: Routes ===
        $routeFile = base_path('routes/web.php');
        $apiRouteFile = base_path('routes/api.php');
        $routeName = strtolower($scaffold['module']);
        $controllerClass = $api ? "Api\\" . ucfirst($scaffold['module']) . "Controller" : ucfirst($scaffold['module']) . "Controller";
        $routeEntry = $api
            ? "Route::apiResource('$routeName', App\\Http\\Controllers\\$controllerClass::class);\n"
            : "Route::resource('$routeName', App\\Http\\Controllers\\$controllerClass::class);\n";
        $routePath = $api ? $apiRouteFile : $routeFile;
        if (strpos(file_get_contents($routePath), $routeEntry) === false) {
            file_put_contents($routePath, $routeEntry, FILE_APPEND);
            $this->info('Route added to: ' . $routePath);
        } else {
            $this->info('Route already exists in: ' . $routePath);
        }

        $this->info("");
        $this->info("✅ CRUD generation completed successfully!");
        $this->info("📝 Don't forget to run 'php artisan migrate' to create the database table.");
    }
}
