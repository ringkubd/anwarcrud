<?php

namespace Anwar\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;


class CreateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgenerator:make {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var
     */
    protected $getStub;
    /**
     * @var
     *
     */

    protected $defaultnamespace = "App\Http\Controller";

    protected $getconfigurations;

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

    }

    /**
     * @return $this
     */

    private function getStub(){
        $stub = ANWAR_CRUD_BASE_PATH.'/stubs/controllerstubs.stub';
        if (file_exists($stub)){
            $this->getStub = $stub;
        }
        return $this;
    }

    private function getModuleName(){

    }

    /**
     *
     */

    private function getConfigurations(){
        $tabel = MODULE_TABLE;
        $this->getconfigurations = DB::select(DB::raw("select * from $tabel"));
    }




}
