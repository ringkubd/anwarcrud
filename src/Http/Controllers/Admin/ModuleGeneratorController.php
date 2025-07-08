<?php

namespace Anwar\CrudGenerator\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controller;
use Anwar\CrudGenerator\Supports\GetTableList;
use Anwar\CrudGenerator\Supports\ScaffoldGenerator;

class ModuleGeneratorController extends Controller
{
    /**
     * Display the main CRUD generator admin interface.
     */
    public function index()
    {
        $tableObject = new GetTableList();
        $data['tableOption'] = $tableObject->getTableFromDB()->makeOption();
        $modules = DB::table('anwar_crud_generator')->orderBy('created_at', 'desc')->get();

        return view('CRUDGENERATOR::admin.module.index', compact('data', 'modules'));
    }

    /**
     * Get columns for a selected table via AJAX.
     */
    public function getCollumns(Request $request)
    {
        $tableObject = new GetTableList();
        return response()->json($tableObject->getCollumns($request->table)->makeTableView());
    }

    /**
     * Get form fields for a selected table via AJAX.
     */
    public function getFormView(Request $request)
    {
        $tableObject = new GetTableList();
        return response()->json($tableObject->getCollumns($request->table)->makeValidationForm());
    }

    /**
     * Generate a CRUD module via the web interface.
     */
    public function finalSubmit(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'module_name' => 'required|string',
            'clumn' => 'required|array',
            'third.label.*' => 'required',
            'third.name.*' => 'required',
            'third.type.*' => 'required',
        ]);

        // Prepare scaffold data
        $scaffold = [
            'module' => str_replace(' ', '', ucwords(str_replace('_', ' ', $request->module_name))),
            'table' => $request->table,
            'fields' => $this->parseFields($request),
            'api' => $request->has('api'),
            'softdeletes' => $request->has('softdeletes'),
            'relationships' => $this->parseRelationships($request),
        ];

        // Generate files
        $this->generateAllFiles($scaffold);

        // Store in database
        $this->storeModuleRecord($scaffold);

        // Generate documentation
        $this->generateDocumentation($scaffold['module']);

        return redirect()->route('admin.crudgenerator.index')
            ->with('success', 'CRUD module generated successfully!');
    }

    /**
     * Generate module via admin interface with enhanced options.
     */
    public function runGenerator(Request $request)
    {
        $this->checkPermissions();

        $scaffold = [
            'module' => $request->input('module'),
            'fields' => $this->parseFieldsFromString($request->input('fields')),
            'relationships' => $this->parseRelationshipsFromString($request->input('relationships')),
            'api' => $request->has('api'),
            'softdeletes' => $request->has('softdeletes'),
        ];

        $this->generateAllFiles($scaffold);
        $this->storeModuleRecord($scaffold);
        $this->logActivity('run_generator', $scaffold, $request);

        return redirect()->route('admin.crudgenerator.index')
            ->with('success', 'CRUD Generator executed successfully!');
    }

    /**
     * Preview generated files without writing to disk.
     */
    public function previewGenerator(Request $request)
    {
        $this->checkPermissions();

        $scaffold = [
            'module' => $request->input('module'),
            'fields' => $this->parseFieldsFromString($request->input('fields')),
            'relationships' => $this->parseRelationshipsFromString($request->input('relationships')),
            'api' => $request->has('api'),
            'softdeletes' => $request->has('softdeletes'),
        ];

        $files = $this->generatePreviewFiles($scaffold);

        return response()->json([
            'files' => $files,
            'scaffold' => $scaffold,
        ]);
    }

    /**
     * Delete a generated module and its files.
     */
    public function deleteModule($module)
    {
        $this->checkPermissions();

        // Remove from database
        DB::table('anwar_crud_generator')->where('name', $module)->delete();

        // Remove generated files
        $this->deleteModuleFiles($module);

        $this->logActivity('delete_module', ['module' => $module], request());

        return redirect()->route('admin.crudgenerator.index')
            ->with('success', 'Module deleted successfully!');
    }

    /**
     * List available custom stubs.
     */
    public function listStubs()
    {
        $stubDir = resource_path('crud-stubs');
        $stubs = [];

        if (is_dir($stubDir)) {
            foreach (scandir($stubDir) as $file) {
                if ($file === '.' || $file === '..') continue;
                if (is_file($stubDir . DIRECTORY_SEPARATOR . $file)) {
                    $stubs[] = [
                        'name' => $file,
                        'path' => $stubDir . DIRECTORY_SEPARATOR . $file,
                        'size' => filesize($stubDir . DIRECTORY_SEPARATOR . $file),
                        'modified' => date('Y-m-d H:i:s', filemtime($stubDir . DIRECTORY_SEPARATOR . $file))
                    ];
                }
            }
        }

        return response()->json(['stubs' => $stubs]);
    }

    /**
     * Upload a custom stub file.
     */
    public function uploadStub(Request $request)
    {
        $request->validate([
            'stub_file' => 'required|file|mimes:txt,stub,php|max:1024',
            'stub_name' => 'required|string|max:255',
        ]);

        $stubDir = resource_path('crud-stubs');
        if (!is_dir($stubDir)) {
            mkdir($stubDir, 0777, true);
        }

        $file = $request->file('stub_file');
        $stubName = $request->input('stub_name');

        // Ensure proper extension
        if (!str_ends_with($stubName, '.stub')) {
            $stubName .= '.stub';
        }

        $file->move($stubDir, $stubName);

        return redirect()->back()->with('success', 'Stub uploaded: ' . $stubName);
    }

    /**
     * Generate documentation for a module.
     */
    public function generateDocumentation($module)
    {
        try {
            $moduleData = DB::table('anwar_crud_generator')->where('name', $module)->first();
            if (!$moduleData) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            $scaffold = $this->getModuleScaffold($module);
            $documentation = $this->buildDocumentation($scaffold);

            // Save documentation
            $docsDir = resource_path('crud-docs');
            if (!is_dir($docsDir)) {
                mkdir($docsDir, 0777, true);
            }

            file_put_contents($docsDir . '/' . $module . '.md', $documentation['markdown']);
            file_put_contents($docsDir . '/' . $module . '.html', $documentation['html']);

            return response()->json([
                'success' => true,
                'message' => 'Documentation generated successfully',
                'paths' => [
                    'markdown' => 'resources/crud-docs/' . $module . '.md',
                    'html' => 'resources/crud-docs/' . $module . '.html'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Documentation generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Documentation generation failed'], 500);
        }
    }

    /**
     * View generated documentation.
     */
    public function viewDocumentation($module)
    {
        $docsPath = resource_path('crud-docs/' . $module . '.html');

        if (!file_exists($docsPath)) {
            abort(404, 'Documentation not found');
        }

        return response(file_get_contents($docsPath))
            ->header('Content-Type', 'text/html');
    }

    // === API ENDPOINTS ===

    /**
     * API: List all modules.
     */
    public function apiListModules()
    {
        $modules = DB::table('anwar_crud_generator')->get();
        return response()->json(['modules' => $modules]);
    }

    /**
     * API: Generate a module.
     */
    public function apiGenerateModule(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'fields' => 'required|string',
        ]);

        $scaffold = [
            'module' => $request->input('module'),
            'fields' => $this->parseFieldsFromString($request->input('fields')),
            'relationships' => $this->parseRelationshipsFromString($request->input('relationships', '')),
            'api' => $request->boolean('api'),
            'softdeletes' => $request->boolean('softdeletes'),
        ];

        try {
            $this->generateAllFiles($scaffold);
            $this->storeModuleRecord($scaffold);

            return response()->json([
                'success' => true,
                'message' => 'Module generated successfully',
                'module' => $scaffold['module']
            ]);
        } catch (\Exception $e) {
            Log::error('API module generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Module generation failed'], 500);
        }
    }

    /**
     * API: Delete a module.
     */
    public function apiDeleteModule($module)
    {
        try {
            DB::table('anwar_crud_generator')->where('name', $module)->delete();
            $this->deleteModuleFiles($module);

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Module deletion failed'], 500);
        }
    }

    // === PRIVATE HELPER METHODS ===

    private function checkPermissions()
    {
        // Optional permission check - only if authentication is configured
        if (auth()->check()) {
            $permission = config('anwarcrud.admin_permission', 'manage_crud');
            if (method_exists(auth()->user(), 'can') && !auth()->user()->can($permission)) {
                abort(403, 'Unauthorized action.');
            }
        }
    }

    private function parseFields($request)
    {
        $fields = [];
        foreach ($request->third['name'] as $index => $name) {
            $fields[] = [
                'name' => $name,
                'type' => $request->third['type'][$index] ?? 'string',
                'label' => $request->third['label'][$index] ?? ucwords(str_replace('_', ' ', $name)),
                'validation' => $request->third['validationrule'][$index] ?? 'nullable',
                'relation' => $request->third['relation'][$index] ?? null,
            ];
        }
        return $fields;
    }

    private function parseFieldsFromString($fieldsString)
    {
        $fields = [];
        if ($fieldsString) {
            foreach (explode(',', $fieldsString) as $field) {
                $parts = explode(':', $field);
                $fields[] = [
                    'name' => trim($parts[0]),
                    'type' => isset($parts[1]) ? trim($parts[1]) : 'string',
                    'validation' => 'nullable',
                ];
            }
        }
        return $fields;
    }

    private function parseRelationshipsFromString($relationshipsString)
    {
        $relationships = [];
        if ($relationshipsString) {
            foreach (explode(',', $relationshipsString) as $rel) {
                $parts = explode(':', $rel);
                if (count($parts) === 2) {
                    $relationships[] = [
                        'name' => trim($parts[0]),
                        'type' => trim($parts[1]),
                    ];
                }
            }
        }
        return $relationships;
    }

    private function parseRelationships($request)
    {
        // Parse relationships from form data
        return [];
    }

    private function generateAllFiles($scaffold)
    {
        $stubsDir = ANWAR_CRUD_STUBS_PATH;

        // Generate Model
        $modelStub = $stubsDir . '/model.stub';
        $modelPath = base_path('app/Models/' . ucfirst($scaffold['module']) . '.php');
        ScaffoldGenerator::generateModel($scaffold, $modelStub, $modelPath);

        // Generate Controller
        $controllerStub = $stubsDir . '/controller.stub';
        $controllerDir = $scaffold['api'] ?
            base_path('app/Http/Controllers/Api') :
            base_path('app/Http/Controllers');
        if (!is_dir($controllerDir)) mkdir($controllerDir, 0777, true);
        $controllerPath = $controllerDir . '/' . ucfirst($scaffold['module']) . 'Controller.php';
        ScaffoldGenerator::generateController($scaffold, $controllerStub, $controllerPath);

        // Generate Migration
        $migrationStub = $stubsDir . '/migration.stub';
        $timestamp = date('Y_m_d_His');
        $migrationName = $timestamp . '_create_' . strtolower(str_plural($scaffold['module'])) . '_table.php';
        $migrationPath = base_path('database/migrations/' . $migrationName);
        ScaffoldGenerator::generateMigration($scaffold, $migrationStub, $migrationPath);

        // Generate Views (if not API)
        if (!$scaffold['api']) {
            $viewDir = base_path('resources/views/' . strtolower($scaffold['module']));
            ScaffoldGenerator::generateViews($scaffold, $stubsDir, $viewDir);
        }

        // Generate API Resources (if API)
        if ($scaffold['api']) {
            $requestStub = $stubsDir . '/request.stub';
            $requestDir = base_path('app/Http/Requests');
            if (!is_dir($requestDir)) mkdir($requestDir, 0777, true);
            $requestPath = $requestDir . '/' . ucfirst($scaffold['module']) . 'Request.php';
            ScaffoldGenerator::generateRequest($scaffold, $requestStub, $requestPath);

            $resourceStub = $stubsDir . '/resource.stub';
            $resourceDir = base_path('app/Http/Resources');
            if (!is_dir($resourceDir)) mkdir($resourceDir, 0777, true);
            $resourcePath = $resourceDir . '/' . ucfirst($scaffold['module']) . 'Resource.php';
            ScaffoldGenerator::generateResource($scaffold, $resourceStub, $resourcePath);
        }

        // Add routes
        $this->addRoutes($scaffold);
    }

    private function generatePreviewFiles($scaffold)
    {
        $files = [];
        $studlyModule = ucfirst($scaffold['module']);
        $snakeModule = strtolower($scaffold['module']);

        // Model preview
        $files['Model'] = [
            'path' => "app/Models/{$studlyModule}.php",
            'code' => $this->generateModelPreview($scaffold),
        ];

        // Controller preview
        $controllerPath = $scaffold['api'] ?
            "app/Http/Controllers/Api/{$studlyModule}Controller.php" :
            "app/Http/Controllers/{$studlyModule}Controller.php";
        $files['Controller'] = [
            'path' => $controllerPath,
            'code' => $this->generateControllerPreview($scaffold),
        ];

        // Migration preview
        $files['Migration'] = [
            'path' => "database/migrations/xxxx_xx_xx_xxxxxx_create_{$snakeModule}s_table.php",
            'code' => $this->generateMigrationPreview($scaffold),
        ];

        if (!$scaffold['api']) {
            $files['Views'] = [
                'path' => "resources/views/{$snakeModule}/",
                'code' => $this->generateViewsPreview($scaffold),
            ];
        }

        return $files;
    }

    private function generateModelPreview($scaffold)
    {
        $stubPath = ANWAR_CRUD_STUBS_PATH . '/model.stub';
        $stub = file_get_contents($stubPath);

        $fillable = collect($scaffold['fields'])->pluck('name')->map(function ($f) {
            return "'{$f}'";
        })->implode(', ');

        return str_replace([
            '@modelNameSpace',
            '@modelName',
            '@modeltable',
            '@modelFillable',
            '@modelUse',
            '@modelDocProperties',
            '@modelSoftDeletes',
            '@modelrelation'
        ], [
            'App\\Models',
            ucfirst($scaffold['module']),
            strtolower(str_plural($scaffold['module'])),
            "[{$fillable}]",
            $scaffold['softdeletes'] ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n" : '',
            $this->generateDocProperties($scaffold['fields']),
            $scaffold['softdeletes'] ? "    use SoftDeletes;\n" : '',
            $this->generateRelations($scaffold['relationships'] ?? [])
        ], $stub);
    }

    private function generateControllerPreview($scaffold)
    {
        $stubPath = ANWAR_CRUD_STUBS_PATH . '/controller.stub';
        $stub = file_get_contents($stubPath);

        $namespace = $scaffold['api'] ? 'App\\Http\\Controllers\\Api' : 'App\\Http\\Controllers';

        return str_replace([
            '@controllerNamespace',
            '@modelNamespace',
            '@modelName',
            '@controllerName',
            '@apiUse',
        ], [
            $namespace,
            'App\\Models',
            ucfirst($scaffold['module']),
            ucfirst($scaffold['module']) . 'Controller',
            $scaffold['api'] ? "use App\\Http\\Resources\\" . ucfirst($scaffold['module']) . "Resource;" : '',
        ], $stub);
    }

    private function generateMigrationPreview($scaffold)
    {
        $stubPath = ANWAR_CRUD_STUBS_PATH . '/migration.stub';
        $stub = file_get_contents($stubPath);

        $fields = '';
        foreach ($scaffold['fields'] as $field) {
            $type = $field['type'];
            $name = $field['name'];
            $fields .= "            \$table->{$type}('{$name}');\n";
        }

        return str_replace([
            '@table',
            '@fields',
            '@softdeletes',
        ], [
            strtolower(str_plural($scaffold['module'])),
            $fields,
            $scaffold['softdeletes'] ? "            \$table->softDeletes();\n" : '',
        ], $stub);
    }

    private function generateViewsPreview($scaffold)
    {
        return "{{-- Index, Create, Edit, Show views for " . $scaffold['module'] . " --}}\n" .
            "{{-- Generated with Bootstrap 4 styling --}}\n" .
            "{{-- Fields: " . collect($scaffold['fields'])->pluck('name')->implode(', ') . " --}}";
    }

    private function generateDocProperties($fields)
    {
        $properties = '';
        foreach ($fields as $field) {
            $properties .= " * @property {$field['type']} \$" . $field['name'] . "\n";
        }
        return $properties;
    }

    private function generateRelations($relationships)
    {
        $relations = '';
        foreach ($relationships as $rel) {
            $relations .= "    public function {$rel['name']}() { return \$this->{$rel['type']}(); }\n";
        }
        return $relations;
    }

    private function storeModuleRecord($scaffold)
    {
        DB::table('anwar_crud_generator')->updateOrInsert(
            ['name' => $scaffold['module']],
            [
                'name' => $scaffold['module'],
                'controllers' => $scaffold['module'] . 'Controller',
                'uri' => strtolower($scaffold['module']),
                'table_name' => $scaffold['table'] ?? strtolower(str_plural($scaffold['module'])),
                'fields' => json_encode($scaffold['fields']),
                'relationships' => json_encode($scaffold['relationships'] ?? []),
                'api' => $scaffold['api'] ? 1 : 0,
                'softdeletes' => $scaffold['softdeletes'] ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function addRoutes($scaffold)
    {
        $routeFile = $scaffold['api'] ? base_path('routes/api.php') : base_path('routes/web.php');
        $routeName = strtolower($scaffold['module']);
        $controllerClass = $scaffold['api'] ?
            "Api\\" . ucfirst($scaffold['module']) . "Controller" :
            ucfirst($scaffold['module']) . "Controller";

        $routeEntry = $scaffold['api']
            ? "Route::apiResource('$routeName', App\\Http\\Controllers\\$controllerClass::class);\n"
            : "Route::resource('$routeName', App\\Http\\Controllers\\$controllerClass::class);\n";

        if (strpos(file_get_contents($routeFile), $routeEntry) === false) {
            file_put_contents($routeFile, $routeEntry, FILE_APPEND);
        }
    }

    private function deleteModuleFiles($module)
    {
        $paths = [
            base_path('app/Models/' . ucfirst($module) . '.php'),
            base_path('app/Http/Controllers/' . ucfirst($module) . 'Controller.php'),
            base_path('app/Http/Controllers/Api/' . ucfirst($module) . 'Controller.php'),
            base_path('app/Http/Requests/' . ucfirst($module) . 'Request.php'),
            base_path('app/Http/Resources/' . ucfirst($module) . 'Resource.php'),
            base_path('resources/views/' . strtolower($module)),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    @unlink($path);
                }
            }
        }

        // Delete migrations
        $migrationPattern = base_path('database/migrations/*_' . strtolower($module) . 's_table.php');
        foreach (glob($migrationPattern) as $migrationFile) {
            @unlink($migrationFile);
        }
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private function logActivity($action, $data, $request)
    {
        $userId = auth()->check() ? auth()->id() : null;
        $userEmail = auth()->check() ? optional(auth()->user())->email : 'anonymous';

        Log::info("CRUD Generator: {$action}", [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'action' => $action,
            'data' => $data,
            'ip' => $request->ip(),
        ]);

        // Only log to database if the table exists
        try {
            DB::table('admin_activity_logs')->insert([
                'user_id' => $userId,
                'user_email' => $userEmail,
                'action' => $action,
                'module' => $data['module'] ?? null,
                'details' => json_encode($data),
                'ip' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Table might not exist, log to file only
            Log::warning('Could not log to admin_activity_logs table: ' . $e->getMessage());
        }
    }

    private function getModuleScaffold($module)
    {
        $moduleData = DB::table('anwar_crud_generator')->where('name', $module)->first();

        return [
            'module' => $moduleData->name,
            'table' => $moduleData->table_name,
            'fields' => json_decode($moduleData->fields, true) ?: [],
            'relationships' => json_decode($moduleData->relationships, true) ?: [],
            'api' => (bool) $moduleData->api,
            'softdeletes' => (bool) $moduleData->softdeletes,
        ];
    }

    private function buildDocumentation($scaffold)
    {
        $markdown = $this->generateMarkdownDocs($scaffold);
        $html = $this->generateHtmlDocs($scaffold);

        return [
            'markdown' => $markdown,
            'html' => $html,
        ];
    }

    private function generateMarkdownDocs($scaffold)
    {
        $md = "# {$scaffold['module']} Module Documentation\n\n";
        $md .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        $md .= "## Overview\n";
        $md .= "This module manages {$scaffold['module']} resources.\n\n";

        $md .= "## Database Table: `{$scaffold['table']}`\n\n";

        $md .= "### Fields\n";
        $md .= "| Field | Type | Validation |\n";
        $md .= "|-------|------|------------|\n";
        foreach ($scaffold['fields'] as $field) {
            $md .= "| {$field['name']} | {$field['type']} | {$field['validation']} |\n";
        }
        $md .= "\n";

        if (!empty($scaffold['relationships'])) {
            $md .= "### Relationships\n";
            foreach ($scaffold['relationships'] as $rel) {
                $md .= "- **{$rel['name']}**: {$rel['type']}\n";
            }
            $md .= "\n";
        }

        $md .= "### Features\n";
        $md .= "- " . ($scaffold['api'] ? "✅" : "❌") . " API endpoints\n";
        $md .= "- " . ($scaffold['softdeletes'] ? "✅" : "❌") . " Soft deletes\n";
        $md .= "- " . (!$scaffold['api'] ? "✅" : "❌") . " Web interface\n\n";

        $md .= "### Generated Files\n";
        $md .= "- Model: `app/Models/{$scaffold['module']}.php`\n";
        $md .= "- Controller: `app/Http/Controllers/" . ($scaffold['api'] ? 'Api/' : '') . "{$scaffold['module']}Controller.php`\n";
        $md .= "- Migration: `database/migrations/*_create_" . strtolower(str_plural($scaffold['module'])) . "_table.php`\n";
        if (!$scaffold['api']) {
            $md .= "- Views: `resources/views/" . strtolower($scaffold['module']) . "/`\n";
        }
        if ($scaffold['api']) {
            $md .= "- Request: `app/Http/Requests/{$scaffold['module']}Request.php`\n";
            $md .= "- Resource: `app/Http/Resources/{$scaffold['module']}Resource.php`\n";
        }

        return $md;
    }

    private function generateHtmlDocs($scaffold)
    {
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<title>{$scaffold['module']} Module Documentation</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }\n";
        $html .= "table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n";
        $html .= "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n";
        $html .= "th { background-color: #f2f2f2; }\n";
        $html .= "h1, h2, h3 { color: #333; }\n";
        $html .= ".badge { padding: 2px 8px; border-radius: 4px; font-size: 12px; }\n";
        $html .= ".badge.success { background: #d4edda; color: #155724; }\n";
        $html .= ".badge.danger { background: #f8d7da; color: #721c24; }\n";
        $html .= "</style>\n</head>\n<body>\n";

        $html .= "<h1>{$scaffold['module']} Module Documentation</h1>\n";
        $html .= "<p><em>Generated on: " . date('Y-m-d H:i:s') . "</em></p>\n";

        $html .= "<h2>Overview</h2>\n";
        $html .= "<p>This module manages {$scaffold['module']} resources.</p>\n";

        $html .= "<h2>Database Table: <code>{$scaffold['table']}</code></h2>\n";

        $html .= "<h3>Fields</h3>\n";
        $html .= "<table>\n<tr><th>Field</th><th>Type</th><th>Validation</th></tr>\n";
        foreach ($scaffold['fields'] as $field) {
            $html .= "<tr><td>{$field['name']}</td><td>{$field['type']}</td><td>{$field['validation']}</td></tr>\n";
        }
        $html .= "</table>\n";

        if (!empty($scaffold['relationships'])) {
            $html .= "<h3>Relationships</h3>\n<ul>\n";
            foreach ($scaffold['relationships'] as $rel) {
                $html .= "<li><strong>{$rel['name']}</strong>: {$rel['type']}</li>\n";
            }
            $html .= "</ul>\n";
        }

        $html .= "<h3>Features</h3>\n<ul>\n";
        $html .= "<li>API endpoints: " . ($scaffold['api'] ? '<span class="badge success">✅ Enabled</span>' : '<span class="badge danger">❌ Disabled</span>') . "</li>\n";
        $html .= "<li>Soft deletes: " . ($scaffold['softdeletes'] ? '<span class="badge success">✅ Enabled</span>' : '<span class="badge danger">❌ Disabled</span>') . "</li>\n";
        $html .= "<li>Web interface: " . (!$scaffold['api'] ? '<span class="badge success">✅ Enabled</span>' : '<span class="badge danger">❌ Disabled</span>') . "</li>\n";
        $html .= "</ul>\n";

        $html .= "</body>\n</html>";

        return $html;
    }
}
