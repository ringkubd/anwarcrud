<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Anwar\CrudGenerator\Http\Controllers\Admin\ModuleGeneratorController;

class ModuleGeneratorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected ModuleGeneratorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ModuleGeneratorController();

        // Create required database tables
        $this->createCrudGeneratorTable();
    }

    /** @test */
    public function it_can_display_index_page()
    {
        $response = $this->controller->index();

        $this->assertNotNull($response);
    }

    /** @test */
    public function it_can_list_stubs()
    {
        // Create test stub directory
        $stubDir = resource_path('crud-stubs');
        if (!is_dir($stubDir)) {
            mkdir($stubDir, 0777, true);
        }

        // Create test stub file
        file_put_contents($stubDir . '/test.stub', 'Test stub content');

        $response = $this->controller->listStubs();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('stubs', $data);
        $this->assertGreaterThan(0, count($data['stubs']));

        // Cleanup
        unlink($stubDir . '/test.stub');
        rmdir($stubDir);
    }

    /** @test */
    public function it_can_preview_generator()
    {
        $request = new Request();
        $request->merge([
            'module' => 'TestModule',
            'fields' => 'name:string,email:string',
            'relationships' => '',
            'api' => false,
            'softdeletes' => false,
        ]);

        // Mock user authentication
        $this->actingAs($this->createTestUser());

        $response = $this->controller->previewGenerator($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('scaffold', $data);
        $this->assertArrayHasKey('Model', $data['files']);
        $this->assertArrayHasKey('Controller', $data['files']);
    }

    /** @test */
    public function it_can_handle_api_list_modules()
    {
        // Insert test module
        DB::table('anwar_crud_generator')->insert([
            'name' => 'TestModule',
            'controllers' => 'TestModuleController',
            'uri' => 'testmodule',
            'table_name' => 'test_modules',
            'fields' => json_encode([]),
            'relationships' => json_encode([]),
            'api' => 0,
            'softdeletes' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->controller->apiListModules();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('modules', $data);
        $this->assertGreaterThan(0, count($data['modules']));
    }

    /** @test */
    public function it_can_generate_module_via_api()
    {
        $request = new Request();
        $request->merge([
            'module' => 'ApiTestModule',
            'fields' => 'title:string,content:text',
            'relationships' => '',
            'api' => true,
            'softdeletes' => false,
        ]);

        // Mock user authentication
        $this->actingAs($this->createTestUser());

        $response = $this->controller->apiGenerateModule($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('module', $data);
        $this->assertEquals('ApiTestModule', $data['module']);
    }

    /** @test */
    public function it_can_delete_module_via_api()
    {
        // Insert test module
        DB::table('anwar_crud_generator')->insert([
            'name' => 'ToDeleteModule',
            'controllers' => 'ToDeleteModuleController',
            'uri' => 'todeletemodule',
            'table_name' => 'to_delete_modules',
            'fields' => json_encode([]),
            'relationships' => json_encode([]),
            'api' => 0,
            'softdeletes' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->controller->apiDeleteModule('ToDeleteModule');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        // Verify module was deleted from database
        $module = DB::table('anwar_crud_generator')->where('name', 'ToDeleteModule')->first();
        $this->assertNull($module);
    }

    /** @test */
    public function it_validates_required_fields_for_api_generation()
    {
        $request = new Request();
        $request->merge([
            // Missing required 'module' field
            'fields' => 'name:string',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->apiGenerateModule($request);
    }

    /** @test */
    public function it_can_generate_documentation()
    {
        // Insert test module
        DB::table('anwar_crud_generator')->insert([
            'name' => 'DocumentedModule',
            'controllers' => 'DocumentedModuleController',
            'uri' => 'documentedmodule',
            'table_name' => 'documented_modules',
            'fields' => json_encode([
                ['name' => 'title', 'type' => 'string', 'validation' => 'required|string|max:255'],
                ['name' => 'content', 'type' => 'text', 'validation' => 'required|string'],
            ]),
            'relationships' => json_encode([]),
            'api' => 1,
            'softdeletes' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->controller->generateDocumentation('DocumentedModule');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('paths', $data);
        $this->assertArrayHasKey('markdown', $data['paths']);
        $this->assertArrayHasKey('html', $data['paths']);
    }

    protected function createCrudGeneratorTable()
    {
        if (!Schema::hasTable('anwar_crud_generator')) {
            Schema::create('anwar_crud_generator', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('controllers');
                $table->string('uri');
                $table->string('table_name')->nullable();
                $table->text('fields')->nullable();
                $table->text('relationships')->nullable();
                $table->boolean('api')->default(false);
                $table->boolean('softdeletes')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_email')->nullable();
                $table->string('action');
                $table->string('module')->nullable();
                $table->text('details')->nullable();
                $table->ipAddress('ip')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function createTestUser()
    {
        // Try to use the available User model
        $userModel = class_exists('App\\Models\\User') ? 'App\\Models\\User' : 'App\\User';

        if (class_exists($userModel) && method_exists($userModel, 'factory')) {
            return $userModel::factory()->create([
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Fallback for testing without User model
        return (object) [
            'id' => 1,
            'email' => 'test@example.com',
        ];
    }
}
