<?php

namespace Tests\Unit;

use Tests\TestCase;
use Anwar\CrudGenerator\Supports\ScaffoldGenerator;
use Illuminate\Support\Facades\File;

class ScaffoldGeneratorTest extends TestCase
{
    protected array $testScaffold;
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/crud-generator-test-' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $this->testScaffold = [
            'module' => 'TestModel',
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'string',
                    'validation' => 'required|string|max:255',
                    'label' => 'Title',
                ],
                [
                    'name' => 'description',
                    'type' => 'text',
                    'validation' => 'nullable|string',
                    'label' => 'Description',
                ],
                [
                    'name' => 'status',
                    'type' => 'boolean',
                    'validation' => 'boolean',
                    'label' => 'Status',
                ],
            ],
            'relationships' => [
                [
                    'name' => 'user',
                    'type' => 'belongsTo',
                    'model' => 'User',
                ],
            ],
            'api' => false,
            'softdeletes' => false,
        ];
    }

    /** @test */
    public function it_can_generate_model()
    {
        $stubPath = $this->createModelStub();
        $outputPath = $this->tempDir . '/TestModel.php';

        ScaffoldGenerator::generateModel($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class TestModel extends Model', $content);
        $this->assertStringContainsString('protected $table = \'testmodels\';', $content);
        $this->assertStringContainsString('\'title\'', $content);
        $this->assertStringContainsString('\'description\'', $content);
        $this->assertStringContainsString('\'status\'', $content);
        $this->assertStringContainsString('public function user()', $content);
    }

    /** @test */
    public function it_can_generate_model_with_soft_deletes()
    {
        $this->testScaffold['softdeletes'] = true;
        $stubPath = $this->createModelStub();
        $outputPath = $this->tempDir . '/TestModelSoft.php';

        ScaffoldGenerator::generateModel($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('use SoftDeletes;', $content);
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\SoftDeletes;', $content);
    }

    /** @test */
    public function it_can_generate_migration()
    {
        $stubPath = $this->createMigrationStub();
        $outputPath = $this->tempDir . '/create_testmodels_table.php';

        ScaffoldGenerator::generateMigration($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('$table->string(\'title\');', $content);
        $this->assertStringContainsString('$table->text(\'description\');', $content);
        $this->assertStringContainsString('$table->boolean(\'status\');', $content);
    }

    /** @test */
    public function it_can_generate_migration_with_soft_deletes()
    {
        $this->testScaffold['softdeletes'] = true;
        $stubPath = $this->createMigrationStub();
        $outputPath = $this->tempDir . '/create_testmodels_table_soft.php';

        ScaffoldGenerator::generateMigration($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('$table->softDeletes();', $content);
    }

    /** @test */
    public function it_can_generate_controller()
    {
        $stubPath = $this->createControllerStub();
        $outputPath = $this->tempDir . '/TestModelController.php';

        ScaffoldGenerator::generateController($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class TestModelController extends Controller', $content);
        $this->assertStringContainsString('public function index()', $content);
        $this->assertStringContainsString('public function create()', $content);
        $this->assertStringContainsString('public function store(', $content);
        $this->assertStringContainsString('public function show(', $content);
        $this->assertStringContainsString('public function edit(', $content);
        $this->assertStringContainsString('public function update(', $content);
        $this->assertStringContainsString('public function destroy(', $content);
    }

    /** @test */
    public function it_can_generate_api_controller()
    {
        $this->testScaffold['api'] = true;
        $stubPath = $this->createControllerStub();
        $outputPath = $this->tempDir . '/TestModelApiController.php';

        ScaffoldGenerator::generateController($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('TestModelResource::collection', $content);
        $this->assertStringContainsString('new TestModelResource', $content);
        $this->assertStringContainsString('response()->noContent()', $content);
    }

    /** @test */
    public function it_can_generate_request()
    {
        $stubPath = $this->createRequestStub();
        $outputPath = $this->tempDir . '/TestModelRequest.php';

        ScaffoldGenerator::generateRequest($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class TestModelRequest extends FormRequest', $content);
        $this->assertStringContainsString('\'title\' => \'required|string|max:255\'', $content);
        $this->assertStringContainsString('\'description\' => \'nullable|string\'', $content);
        $this->assertStringContainsString('\'status\' => \'boolean\'', $content);
    }

    /** @test */
    public function it_can_generate_resource()
    {
        $stubPath = $this->createResourceStub();
        $outputPath = $this->tempDir . '/TestModelResource.php';

        ScaffoldGenerator::generateResource($this->testScaffold, $stubPath, $outputPath);

        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('class TestModelResource extends JsonResource', $content);
        $this->assertStringContainsString('\'title\' => $this->title', $content);
        $this->assertStringContainsString('\'description\' => $this->description', $content);
        $this->assertStringContainsString('\'status\' => $this->status', $content);
    }

    /** @test */
    public function it_can_generate_views()
    {
        $viewStubsDir = $this->createViewStubs();
        $outputDir = $this->tempDir . '/views';

        ScaffoldGenerator::generateViews($this->testScaffold, $viewStubsDir, $outputDir);

        $this->assertFileExists($outputDir . '/index.blade.php');
        $this->assertFileExists($outputDir . '/create.blade.php');
        $this->assertFileExists($outputDir . '/edit.blade.php');
        $this->assertFileExists($outputDir . '/show.blade.php');

        // Test index view
        $indexContent = file_get_contents($outputDir . '/index.blade.php');
        $this->assertStringContainsString('<th>Title</th>', $indexContent);
        $this->assertStringContainsString('<th>Description</th>', $indexContent);
        $this->assertStringContainsString('<th>Status</th>', $indexContent);

        // Test create view
        $createContent = file_get_contents($outputDir . '/create.blade.php');
        $this->assertStringContainsString('name="title"', $createContent);
        $this->assertStringContainsString('name="description"', $createContent);
        $this->assertStringContainsString('name="status"', $createContent);
    }

    protected function createModelStub(): string
    {
        $stubPath = $this->tempDir . '/model.stub';
        $content = '<?php

namespace @modelNameSpace;

use Illuminate\Database\Eloquent\Model;
@modelUse

/**
@modelDocProperties
 */
class @modelName extends Model
{
@modelSoftDeletes
    protected $table = \'@modeltable\';

    protected $fillable = @modelFillable;

@modelrelation
}';
        file_put_contents($stubPath, $content);
        return $stubPath;
    }

    protected function createMigrationStub(): string
    {
        $stubPath = $this->tempDir . '/migration.stub';
        $content = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(\'@table\', function (Blueprint $table) {
            $table->id();
@fields
            $table->timestamps();
@softdeletes
        });
    }

    public function down()
    {
        Schema::dropIfExists(\'@table\');
    }
};';
        file_put_contents($stubPath, $content);
        return $stubPath;
    }

    protected function createControllerStub(): string
    {
        $stubPath = $this->tempDir . '/controller.stub';
        $content = '<?php

namespace @controllerNamespace;

use @modelNamespace\@modelName;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
@apiUse

class @controllerName extends Controller
{
    /**
     * @return @indexReturnType
     */
    public function index()
    {
        @indexReturn
    }

    public function create()
    {
        return view(\'@modelVar.create\');
    }

    /**
     * @param @requestClass $request
     * @return @storeReturnType
     */
    public function store(@requestClass $request)
    {
        @storeReturn
    }

    /**
     * @param @modelName $@modelVar
     * @return @showReturnType
     */
    public function show(@modelName $@modelVar)
    {
        @showReturn
    }

    public function edit(@modelName $@modelVar)
    {
        return view(\'@modelVar.edit\', compact(\'@modelVar\'));
    }

    /**
     * @param @requestClass $request
     * @param @modelName $@modelVar
     * @return @updateReturnType
     */
    public function update(@requestClass $request, @modelName $@modelVar)
    {
        @updateReturn
    }

    /**
     * @param @modelName $@modelVar
     * @return @destroyReturnType
     */
    public function destroy(@modelName $@modelVar)
    {
        @destroyReturn
    }
}';
        file_put_contents($stubPath, $content);
        return $stubPath;
    }

    protected function createRequestStub(): string
    {
        $stubPath = $this->tempDir . '/request.stub';
        $content = '<?php

namespace @requestNamespace;

use Illuminate\Foundation\Http\FormRequest;

class @requestClass extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
@validationRules
        ];
    }

    public function attributes()
    {
        return [
@fieldAttributes
        ];
    }
}';
        file_put_contents($stubPath, $content);
        return $stubPath;
    }

    protected function createResourceStub(): string
    {
        $stubPath = $this->tempDir . '/resource.stub';
        $content = '<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class @resourceClass extends JsonResource
{
    public function toArray($request)
    {
        return [
            \'id\' => $this->id,
@resourceFields
            \'created_at\' => $this->created_at,
            \'updated_at\' => $this->updated_at,
        ];
    }
}';
        file_put_contents($stubPath, $content);
        return $stubPath;
    }

    protected function createViewStubs(): string
    {
        $viewStubsDir = $this->tempDir . '/view_stubs';
        mkdir($viewStubsDir, 0777, true);

        // Index view stub
        $indexStub = '@extends(\'layout.app\')
@section(\'content\')
<div class="container">
    <h1>@modelName</h1>
    <table class="table">
        <thead>
            <tr>
@thead
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
@tbody
        </tbody>
    </table>
</div>
@endsection';
        file_put_contents($viewStubsDir . '/view_index.stub', $indexStub);

        // Create view stub
        $createStub = '@extends(\'layout.app\')
@section(\'content\')
<div class="container">
    <h1>Create @modelName</h1>
    <form action="{{ route(\'@routeName.store\') }}" method="POST">
        @csrf
@fields
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection';
        file_put_contents($viewStubsDir . '/view_create.stub', $createStub);

        // Edit view stub
        $editStub = '@extends(\'layout.app\')
@section(\'content\')
<div class="container">
    <h1>Edit @modelName</h1>
    <form action="{{ route(\'@routeName.update\', $@modelVar) }}" method="POST">
        @csrf
        @method(\'PUT\')
@fields
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection';
        file_put_contents($viewStubsDir . '/view_edit.stub', $editStub);

        // Show view stub
        $showStub = '@extends(\'layout.app\')
@section(\'content\')
<div class="container">
    <h1>@modelName Details</h1>
    <table class="table">
@fields
    </table>
</div>
@endsection';
        file_put_contents($viewStubsDir . '/view_show.stub', $showStub);

        return $viewStubsDir;
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
