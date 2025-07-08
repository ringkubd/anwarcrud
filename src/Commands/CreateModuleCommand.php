<?php

namespace Anwar\CrudGenerator\Commands;

use Illuminate\Console\Command;

class CreateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anwar:module {name : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module structure';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->info("Creating module: {$name}");

        // Create module directories
        $basePath = app_path($name);

        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
            $this->info("Created directory: {$basePath}");
        }

        // Create sub-directories
        $directories = [
            'Controllers',
            'Models',
            'Requests',
            'Resources',
            'Services'
        ];

        foreach ($directories as $dir) {
            $dirPath = $basePath . '/' . $dir;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
                $this->info("Created directory: {$dirPath}");
            }
        }

        $this->info("Module '{$name}' created successfully!");

        return 0;
    }
}
