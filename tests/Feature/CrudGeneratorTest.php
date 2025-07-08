<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Anwar\CrudGenerator\Supports\ScaffoldGenerator;

class CrudGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test database tables
        $this->createTestTable();
    }

    /** @test */
    public function it_can_generate_a_basic_model()
    {
        $scaffold = [
            'module' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'content', 'type' => 'text'],
                ['name' => 'published', 'type' => 'boolean'],
            ],
            'api' => false,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/model.stub';
        $outputPath = sys_get_temp_dir() . '/Post.php';

        ScaffoldGenerator::generateModel($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class Post extends Model', $content);
        $this->assertStringContainsString("'title'", $content);
        $this->assertStringContainsString("'content'", $content);
        $this->assertStringContainsString("'published'", $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_a_model_with_soft_deletes()
    {
        $scaffold = [
            'module' => 'Product',
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'price', 'type' => 'decimal'],
            ],
            'api' => false,
            'softdeletes' => true,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/model.stub';
        $outputPath = sys_get_temp_dir() . '/Product.php';

        ScaffoldGenerator::generateModel($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('use SoftDeletes', $content);
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\SoftDeletes', $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_a_migration()
    {
        $scaffold = [
            'module' => 'Article',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'slug', 'type' => 'string'],
                ['name' => 'body', 'type' => 'text'],
                ['name' => 'published_at', 'type' => 'dateTime'],
                ['name' => 'view_count', 'type' => 'integer'],
            ],
            'api' => false,
            'softdeletes' => true,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/migration.stub';
        $outputPath = sys_get_temp_dir() . '/create_articles_table.php';

        ScaffoldGenerator::generateMigration($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('$table->string(\'title\')', $content);
        $this->assertStringContainsString('$table->string(\'slug\')', $content);
        $this->assertStringContainsString('$table->text(\'body\')', $content);
        $this->assertStringContainsString('$table->dateTime(\'published_at\')', $content);
        $this->assertStringContainsString('$table->integer(\'view_count\')', $content);
        $this->assertStringContainsString('$table->softDeletes()', $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_a_controller()
    {
        $scaffold = [
            'module' => 'User',
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
            ],
            'api' => false,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/controller.stub';
        $outputPath = sys_get_temp_dir() . '/UserController.php';

        ScaffoldGenerator::generateController($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class UserController extends Controller', $content);
        $this->assertStringContainsString('public function index()', $content);
        $this->assertStringContainsString('public function store(', $content);
        $this->assertStringContainsString('public function show(', $content);
        $this->assertStringContainsString('public function update(', $content);
        $this->assertStringContainsString('public function destroy(', $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_an_api_controller()
    {
        $scaffold = [
            'module' => 'Category',
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'description', 'type' => 'text'],
            ],
            'api' => true,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/controller.stub';
        $outputPath = sys_get_temp_dir() . '/CategoryController.php';

        ScaffoldGenerator::generateController($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('namespace App\\Http\\Controllers\\Api', $content);
        $this->assertStringContainsString('use App\\Http\\Resources\\CategoryResource', $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_a_form_request()
    {
        $scaffold = [
            'module' => 'Comment',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'validation' => 'required|string|max:255'],
                ['name' => 'email', 'type' => 'string', 'validation' => 'required|email'],
                ['name' => 'body', 'type' => 'text', 'validation' => 'required|string|min:10'],
            ],
            'api' => true,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/request.stub';
        $outputPath = sys_get_temp_dir() . '/CommentRequest.php';

        ScaffoldGenerator::generateRequest($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class CommentRequest extends FormRequest', $content);
        $this->assertStringContainsString("'name' => 'required|string|max:255'", $content);
        $this->assertStringContainsString("'email' => 'required|email'", $content);
        $this->assertStringContainsString("'body' => 'required|string|min:10'", $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_an_api_resource()
    {
        $scaffold = [
            'module' => 'Task',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'description', 'type' => 'text'],
                ['name' => 'completed', 'type' => 'boolean'],
            ],
            'api' => true,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubPath = __DIR__ . '/../../src/stubs/resource.stub';
        $outputPath = sys_get_temp_dir() . '/TaskResource.php';

        ScaffoldGenerator::generateResource($scaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class TaskResource extends JsonResource', $content);
        $this->assertStringContainsString("'title' => \$this->title", $content);
        $this->assertStringContainsString("'description' => \$this->description", $content);
        $this->assertStringContainsString("'completed' => \$this->completed", $content);

        unlink($outputPath);
    }

    /** @test */
    public function it_can_generate_views()
    {
        $scaffold = [
            'module' => 'Book',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'author', 'type' => 'string'],
                ['name' => 'isbn', 'type' => 'string'],
                ['name' => 'pages', 'type' => 'integer'],
            ],
            'api' => false,
            'softdeletes' => false,
            'relationships' => [],
        ];

        $stubsDir = __DIR__ . '/../../src/stubs';
        $outputDir = sys_get_temp_dir() . '/book_views';

        ScaffoldGenerator::generateViews($scaffold, $stubsDir, $outputDir);

        $this->assertDirectoryExists($outputDir);
        $this->assertFileExists($outputDir . '/index.blade.php');
        $this->assertFileExists($outputDir . '/create.blade.php');
        $this->assertFileExists($outputDir . '/edit.blade.php');
        $this->assertFileExists($outputDir . '/show.blade.php');

        // Check content
        $indexContent = file_get_contents($outputDir . '/index.blade.php');
        $this->assertStringContainsString('Title', $indexContent);
        $this->assertStringContainsString('Author', $indexContent);
        $this->assertStringContainsString('book.show', $indexContent);

        // Cleanup
        $this->deleteDirectory($outputDir);
    }

    protected function createTestTable()
    {
        Schema::create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    protected function deleteDirectory($dir)
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
}
