<?php

namespace Anwar\CrudGenerator\Supports;

class ScaffoldGenerator
{
    public static function generateModel($scaffold, $stubPath, $outputPath)
    {
        $stub = file_get_contents($stubPath);
        $modelName = ucfirst($scaffold['module']);
        $namespace = config('anwarcrud.model_namespace', 'App\\Models');
        $table = strtolower(str_plural($scaffold['module']));
        $fillable = collect($scaffold['fields'])->pluck('name')->map(function ($f) {
            return "'{$f}'";
        })->implode(', ');
        $fillable = "[{$fillable}]";
        $relations = '';
        $docProperties = '';
        foreach ($scaffold['fields'] as $field) {
            $docProperties .= " * @property {$field['type']} \$" . $field['name'] . "\n";
        }
        foreach ($scaffold['relationships'] as $rel) {
            $relName = $rel['name'];
            $relType = $rel['type'];
            $relModel = $namespace . '\\' . ucfirst($relName);
            $relations .= "    public function {$relName}() { return \$this->{$relType}('{$relModel}'); }\n";
            $docProperties .= " * @property-read \\Illuminate\\Database\\Eloquent\\Collection|{$namespace}\\" . ucfirst($relName) . "[] \$" . $relName . "\n";
        }
        $softDeletesUse = $scaffold['softdeletes'] ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n    use SoftDeletes;" : '';
        $softDeletesTrait = $scaffold['softdeletes'] ? "    use SoftDeletes;\n" : '';
        $stub = str_replace([
            '@modelNameSpace',
            '@modelName',
            '@modeltable',
            '@modelFillable',
            '@modelrelation',
            '@modelUse',
            '@modelDocProperties',
            '@modelSoftDeletes',
        ], [
            $namespace,
            $modelName,
            $table,
            $fillable,
            $relations,
            $softDeletesUse,
            $docProperties,
            $softDeletesTrait,
        ], $stub);

        // Create directory if it doesn't exist
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($outputPath, $stub);
    }

    public static function generateMigration($scaffold, $stubPath, $outputPath)
    {
        $stub = file_get_contents($stubPath);
        $table = strtolower(str_plural($scaffold['module']));
        $fields = '';
        foreach ($scaffold['fields'] as $field) {
            $type = $field['type'];
            $name = $field['name'];
            if ($type === 'string' || $type === 'varchar') {
                $fields .= "            \$table->string('{$name}');\n";
            } elseif ($type === 'text') {
                $fields .= "            \$table->text('{$name}');\n";
            } elseif ($type === 'integer' || $type === 'int') {
                $fields .= "            \$table->integer('{$name}');\n";
            } elseif ($type === 'boolean' || $type === 'bool') {
                $fields .= "            \$table->boolean('{$name}');\n";
            } elseif ($type === 'date') {
                $fields .= "            \$table->date('{$name}');\n";
            } elseif ($type === 'datetime') {
                $fields .= "            \$table->dateTime('{$name}');\n";
            } elseif ($type === 'float' || $type === 'double') {
                $fields .= "            \$table->float('{$name}');\n";
            } else {
                $fields .= "            \$table->{$type}('{$name}');\n";
            }
        }
        $softdeletes = $scaffold['softdeletes'] ? "            \$table->softDeletes();\n" : '';
        $stub = str_replace([
            '@table',
            '@fields',
            '@softdeletes',
        ], [
            $table,
            $fields,
            $softdeletes,
        ], $stub);
        file_put_contents($outputPath, $stub);
    }

    public static function generateViews($scaffold, $viewStubsDir, $outputDir)
    {
        $modelName = ucfirst($scaffold['module']);
        $modelVar = lcfirst($modelName);
        $routeName = strtolower($scaffold['module']);
        if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

        // Index view
        $indexStub = file_get_contents($viewStubsDir . '/view_index.stub');
        $thead = $tbody = '';
        foreach ($scaffold['fields'] as $field) {
            $thead .= "                <th>" . ucfirst($field['name']) . "</th>\n";
        }
        $tbody .= "@foreach(\$items as \$$modelVar)\n            <tr>\n";
        foreach ($scaffold['fields'] as $field) {
            $tbody .= "                <td>{{ \$$modelVar->" . $field['name'] . " }}</td>\n";
        }
        $tbody .= "                <td>\n                    <a href=\"{{ route('$routeName.show', \$$modelVar" . "->id) }}\" class=\"btn btn-info btn-sm\">View</a>\n                    <a href=\"{{ route('$routeName.edit', \$$modelVar" . "->id) }}\" class=\"btn btn-warning btn-sm\">Edit</a>\n                    <form action=\"{{ route('$routeName.destroy', \$$modelVar" . "->id) }}\" method=\"POST\" style=\"display:inline-block\">\n                        @csrf\n                        @method('DELETE')\n                        <button type=\"submit\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm('Are you sure?')\">Delete</button>\n                    </form>\n                </td>\n            </tr>\n@endforeach";
        $indexView = str_replace([
            '@modelName',
            '@routeName',
            '@thead',
            '@tbody'
        ], [
            $modelName,
            $routeName,
            $thead,
            $tbody
        ], $indexStub);
        file_put_contents($outputDir . '/index.blade.php', $indexView);

        // Create/Edit fields
        $fieldsHtml = '';
        foreach ($scaffold['fields'] as $field) {
            $fieldsHtml .= "        <div class=\"mb-3\">\n            <label for=\"{$field['name']}\" class=\"form-label\">" . ucfirst($field['name']) . "</label>\n            <input type=\"text\" class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" value=\"{{ old('{$field['name']}', isset(\$$modelVar) ? \$$modelVar->{$field['name']} : '') }}\">\n        </div>\n";
        }

        // Create view
        $createStub = file_get_contents($viewStubsDir . '/view_create.stub');
        $createView = str_replace([
            '@modelName',
            '@routeName',
            '@fields'
        ], [
            $modelName,
            $routeName,
            $fieldsHtml
        ], $createStub);
        file_put_contents($outputDir . '/create.blade.php', $createView);

        // Edit view
        $editStub = file_get_contents($viewStubsDir . '/view_edit.stub');
        $editView = str_replace([
            '@modelName',
            '@routeName',
            '@fields',
            '@modelVar'
        ], [
            $modelName,
            $routeName,
            $fieldsHtml,
            $modelVar
        ], $editStub);
        file_put_contents($outputDir . '/edit.blade.php', $editView);

        // Show view
        $showStub = file_get_contents($viewStubsDir . '/view_show.stub');
        $showFields = '';
        foreach ($scaffold['fields'] as $field) {
            $showFields .= "            <tr><th>" . ucfirst($field['name']) . "</th><td>{{ \$$modelVar->{$field['name']} }}</td></tr>\n";
        }
        $showView = str_replace([
            '@modelName',
            '@routeName',
            '@fields',
            '@modelVar'
        ], [
            $modelName,
            $routeName,
            $showFields,
            $modelVar
        ], $showStub);
        file_put_contents($outputDir . '/show.blade.php', $showView);
    }

    public static function generateRequest($scaffold, $stubPath, $outputPath)
    {
        $stub = file_get_contents($stubPath);
        $requestClass = ucfirst($scaffold['module']) . 'Request';
        $requestNamespace = config('anwarcrud.controller_namespace', 'App\\Http\\Controllers') . '\\..\\Requests';
        $requestNamespace = str_replace('\\..\\', '\\', $requestNamespace);

        // Build validation rules
        $rules = '';
        $attributes = '';

        foreach ($scaffold['fields'] as $field) {
            $fieldName = $field['name'];
            $validation = $field['validation'] ?? 'nullable';
            $label = $field['label'] ?? ucwords(str_replace('_', ' ', $fieldName));

            $rules .= "            '{$fieldName}' => '{$validation}',\n";
            $attributes .= "            '{$fieldName}' => '{$label}',\n";
        }

        $stub = str_replace([
            '@requestNamespace',
            '@requestClass',
            '@modelName',
            '@validationRules',
            '@fieldAttributes',
            '@rules', // Legacy support
        ], [
            $requestNamespace,
            $requestClass,
            ucfirst($scaffold['module']),
            $rules,
            $attributes,
            $rules, // Legacy support
        ], $stub);

        file_put_contents($outputPath, $stub);
    }

    public static function generateResource($scaffold, $stubPath, $outputPath)
    {
        $stub = file_get_contents($stubPath);
        $resourceClass = ucfirst($scaffold['module']) . 'Resource';
        $fields = '';
        foreach ($scaffold['fields'] as $field) {
            $fields .= "            '{$field['name']}' => \$this->{$field['name']},\n";
        }
        $stub = str_replace([
            '@resourceClass',
            '@resourceFields',
        ], [
            $resourceClass,
            $fields,
        ], $stub);
        file_put_contents($outputPath, $stub);
    }

    public static function generateController($scaffold, $stubPath, $outputPath)
    {
        $stub = file_get_contents($stubPath);
        $modelName = ucfirst($scaffold['module']);
        $modelVar = lcfirst($modelName);
        $controllerNamespace = config('anwarcrud.controller_namespace', 'App\\Http\\Controllers');
        $namespace = $scaffold['api'] ? $controllerNamespace . '\\Api' : $controllerNamespace;
        $modelNamespace = config('anwarcrud.model_namespace', 'App\\Models');
        $controllerName = $modelName . 'Controller';
        $apiUse = $scaffold['api'] ? "use {$controllerNamespace}\\..\\Resources\\{$modelName}Resource;" : '';
        $apiUse = str_replace('\\..\\', '\\', $apiUse);

        // Use FormRequest for validation
        $requestClass = $scaffold['api']
            ? str_replace('Controllers', 'Requests', $controllerNamespace) . "\\{$modelName}Request"
            : 'Request';

        // Return types for docblocks
        $indexReturnType = $scaffold['api'] ? "\\Illuminate\\Http\\Resources\\Json\\ResourceCollection" : "\\Illuminate\\View\\View";
        $storeReturnType = $scaffold['api'] ? "{$modelName}Resource" : "\\Illuminate\\Http\\RedirectResponse";
        $showReturnType = $scaffold['api'] ? "{$modelName}Resource" : "\\Illuminate\\View\\View";
        $updateReturnType = $scaffold['api'] ? "{$modelName}Resource" : "\\Illuminate\\Http\\RedirectResponse";
        $destroyReturnType = $scaffold['api'] ? "\\Illuminate\\Http\\Response" : "\\Illuminate\\Http\\RedirectResponse";

        // Controller method bodies
        if ($scaffold['api']) {
            $indexReturn = "return {$modelName}Resource::collection({$modelName}::all());";
            $storeReturn = "return new {$modelName}Resource({$modelName}::create(\$request->validated()));";
            $showReturn = "return new {$modelName}Resource(\${$modelVar});";
            $updateReturn = "\${$modelVar}->update(\$request->validated());\n        return new {$modelName}Resource(\${$modelVar});";
            $destroyReturn = "\${$modelVar}->delete();\n        return response()->noContent();";
        } else {
            $indexReturn = "return view('{$scaffold['module']}.index', ['items' => {$modelName}::all()]);";
            $storeReturn = "\$item = {$modelName}::create(\$request->all());\n        return redirect()->route('{$scaffold['module']}.index');";
            $showReturn = "return view('{$scaffold['module']}.show', compact('{$modelVar}'));";
            $updateReturn = "\${$modelVar}->update(\$request->all());\n        return redirect()->route('{$scaffold['module']}.index');";
            $destroyReturn = "\${$modelVar}->delete();\n        return redirect()->route('{$scaffold['module']}.index');";
        }

        $stub = str_replace([
            '@controllerNamespace',
            '@modelNamespace',
            '@modelName',
            '@modelVar',
            '@controllerName',
            '@apiUse',
            '@requestClass',
            '@indexReturn',
            '@storeReturn',
            '@showReturn',
            '@updateReturn',
            '@destroyReturn',
            '@storeReturnType',
            '@showReturnType',
            '@updateReturnType',
            '@destroyReturnType',
            '@indexReturnType',
        ], [
            $namespace,
            $modelNamespace,
            $modelName,
            $modelVar,
            $controllerName,
            $apiUse,
            $requestClass,
            $indexReturn,
            $storeReturn,
            $showReturn,
            $updateReturn,
            $destroyReturn,
            $storeReturnType,
            $showReturnType,
            $updateReturnType,
            $destroyReturnType,
            $indexReturnType,
        ], $stub);
        file_put_contents($outputPath, $stub);
    }
}
