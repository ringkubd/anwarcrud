<?php

namespace Anwar\CrudGenerator\Commands;

use Illuminate\Console\Command;

class CrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anwar:crudgenerator';

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
        $this->info("Laravel crud generator installation process is starting");
        $this->info("");
        $this->info("##########################################################");
        $this->info("$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$");
        $this->info("##########################################################");
        $this->info("                     =                                    ");
        $this->info("                    = =                                   ");
        $this->info("                   =   =                                  ");
        $this->info("                  =     =                                 ");
        $this->info("                 =  ===  =                                ");
        $this->info("                =         =                               ");
        $this->info("               =           =                              ");
        $this->info("##########################################################");
        $this->info("$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$");
        $this->info("##########################################################");
        $this->info("");

        $this->info("Run php artisan migrate............");
        $this->info("...................................");
        $this->info("");

        //$this->call("migrate");
        $this->runMigrate();

        $this->info("");
        $this->info("...................................");
        $this->info("Complete migration");
    }

    private function runMigrate(){
        foreach (new \DirectoryIterator(ANWAR_CRUD_BASE_PATH."/migrations") as $directoryIterator){
            if ($directoryIterator->isFile()){
                $this->call("migrate",["--path"=>"vendor/anwar/crud-generator/src/migrations/{$directoryIterator->getBasename()}"]);
            }

        }
    }
}
