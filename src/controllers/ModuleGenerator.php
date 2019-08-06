<?php

namespace Anwar\CrudGenerator\Controllers;

use Anwar\CrudGenerator\Supports\GetTableList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Anwar\CrudGenerator\Model\AnwarCrud;

class ModuleGenerator extends Controller
{
    /**
     * @var
     */
    protected $stubs;

    protected $class;

    protected $table;

    protected $stubVariable = "";

    protected $namespace = "App\Http\Controllers";

    protected $validationRule = "";

    protected $tableFieldName = "";

    protected $formDisplay = "";

    protected $select;

    protected $join;

    protected $tableAlies;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        $tableObject = new GetTableList();
        $data['tableOption'] = $tableObject->getTableFromDB()->makeOption();
        return view("CRUDGENERATOR::admin.module.index",compact('data'));

    }

    /**
     * @param Request $request
     * @return false|string
     */

    public function getCollumns(Request $request){
        $tableObject = new GetTableList();
        return json_encode($tableObject->getCollumns($request->table)->makeTableView());
    }

    /**
     * @param Request $request
     * @return false|string
     */

    public function getFormView(Request $request){
        $tableObject = new GetTableList();
        return json_encode($tableObject->getCollumns($request->table)->makeValidationForm());
    }

    /**
     * @param Request $request
     * @return array
     */

    public function finalSubmit(Request $request){
        $request->validate([
            "table"=>"required|string",
            "module_name"=>"required|string",
            "clumn"=>"required|array",
            "selectjoin"=>"required_with:join.*"
        ]);
        return dump($request->all());

        $this->table = $request->table;

        $this->class =  str_replace(" ","",ucwords(str_replace("_"," ",$request->module_name)));

        $this->tablealies();
        $alias = substr($this->table,0,5);
        $join = null;
        if (count(array_filter($request->selectjoin)) > 0){
            $join = " , ";
        }
        //return dump(array_filter($request->selectjoin));

        $this->select = $alias.".".implode(",$alias.",$request->clumn).$join.implode(",",array_filter($request->selectjoin));

        $this->tableFieldName = implode(",",$request->clumn).$join.implode(",",array_filter($request->selectjoin));
        $this->join = implode(" ",array_filter($request->join));
        //AnwarCrud::where("controllers",$this->class)->orWhere("")
        DB::table("anwar_crud_generator")->insert([
            "name"=>$this->class,
            "controllers"=>$this->class,
            "uri"=>$this->table,
        ]);
        $final = $this->getStub()->tablealies()->getAllVariabel()->createView()->setNameSpaceAndClassName();

        return dump($final);

    }

    /**
     * @return $this
     */

    private function createView(){
        $stubFile['index'] = ANWAR_CRUD_BASE_PATH."/stubs/view/index.stub";
        $stubFile['form'] = ANWAR_CRUD_BASE_PATH."/stubs/view/index.stub";
        $stubFile['edit'] = ANWAR_CRUD_BASE_PATH."/stubs/view/index.stub";
        $stubFile['create'] = ANWAR_CRUD_BASE_PATH."/stubs/view/index.stub";
        $foldername = $this->class;
        $viewPath = resource_path("views/").$foldername;
        if (!file_exists($viewPath)){
            mkdir($viewPath);
        }
        foreach ($stubFile as $key=>$value){
            $stubContent = file_get_contents($value);
            if (!file_exists($viewPath."//$key.blade.php")){
                touch($viewPath."//$key.blade.php");
            }
            file_put_contents($viewPath."//$key.blade.php",$stubContent);
        }
        return $this;

    }

    /**
     * @return $this
     */

    private function getStub(){
        $stubFile = ANWAR_CRUD_BASE_PATH."/stubs/controllerstubs.stub";
        if (file_exists($stubFile)){
            $this->stubs =  file_get_contents($stubFile);
        }
        return $this;
    }

    /**
     * @return $this
     */

    private function getAllVariabel(){
        $stubFileContent =  $this->stubs;
        //$pattern = '/((?<!\S)@\w+(?!\S))/';
        $pattern = '~(@\w+)~';
        preg_match_all($pattern,$stubFileContent,$this->stubVariable,PREG_PATTERN_ORDER);
        return $this;

    }

    /**
     * @return false|string
     */

    private function setNameSpaceAndClassName(){
        $stubFileContent =  $this->stubs;
        $stubVariable = array_map(function ($arr){
            return str_replace("@","",$arr);
        },$this->stubVariable);

        foreach ($stubVariable[0] as $stubVar){
            if (property_exists($this, $stubVar)){
                $stubFileContent = str_replace("@{$stubVar}",is_array($this->{$stubVar})?http_build_query($this->{$stubVar},"","##"): $this->{$stubVar},$stubFileContent);
            }else{
                $stubFileContent = str_replace("@{$stubVar}","",$stubFileContent);
            }
        }
        if (!file_exists("App/Http/Controllers/{$this->class}.php")){
            touch(app_path("Http/Controllers/").$this->class.".php");
            file_put_contents(app_path("Http/Controllers/").$this->class.".php",$stubFileContent);
        }
        return file_get_contents(app_path("Http/Controllers/").$this->class.".php");
    }

    /**
     * @return $this
     */

    private function tablealies(){
        $this->tableAlies = substr($this->class,"0","2");
        return $this;
    }




}
