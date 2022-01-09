<?php

namespace Anwar\CrudGenerator\Controllers;

use Anwar\CrudGenerator\Supports\GetTableList;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Anwar\CrudGenerator\Model\AnwarCrud;
use Symfony\Component\Console\Output\BufferedOutput;

class ModuleGenerator extends \App\Http\Controllers\Controller
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

    protected $formDisplay = [];

    protected $select;

    protected $join;

    protected $tableAlies;

    protected $formRelation;

    protected $formOnchange;

    protected $formType;

    protected $use;

    // Model

    protected $modelNameSpace = "App\AnwarCrudGenerator";

    protected $modelName;

    protected $modelUse;

    protected $modeltable;

    protected $modelFillable;

    protected $modelrelation;




    /**
     * @return Factory|\Illuminate\View\View
     */
    public function index(){
        $tableObject = new GetTableList();
        $data['tableOption'] = $tableObject->getTableFromDB()->makeOption();
        return view('CRUDGENERATOR::admin.module.index',compact('data'));

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
            'table' => 'required|string',
            'module_name' => 'required|string',
            'clumn' => 'required|array',
            'selectjoin' => 'required_with:join.*',
            'third.label.*' => 'required',
            'third.name.*' => 'required',
            'third.type.*' => 'required',
        ]);
        if (!file_exists(ANWAR_CRUD_BASE_PATH. '/installed')){
            $output = new BufferedOutput;
            Artisan::call('vendor:publish',['--provider'=>'Anwar\CrudGenerator\AnwarCrudGeneratorProvider'],$output);
            Artisan::call('migrate',['--path'=>'database/migrations/2019_07_31_093754_anwar_crud_generator.php'],$output);
            touch(ANWAR_CRUD_BASE_PATH. '/installed');
        }
        $this->formDisplay = $request->third['label'];
        $this->validationRule = $request->third['validationrule'];
        $this->formRelation = $request->third['relation'];
        $this->formOnchange = $request->third['onchange'];
        $this->formType = $request->third['type'];
        $this->validationRule = implode('#',$this->makeKeyValueString($request->third['validationrule']));
        //return dump($request->all());

        $this->table = $request->table;
        $this->class =  str_replace(' ',"",ucwords(str_replace('_', ' ',$request->module_name)));
        $this->tablealies();
        $alias = substr($this->table,0,5);
        $join = null;
        if (count(array_filter($request->selectjoin)) > 0){
            $join = ' , ';
        }

        $this->select = $alias. '.' .implode(",$alias.",$request->clumn).$join.implode(",",array_filter($request->selectjoin));
        $this->tableFieldName = implode(",",$request->clumn);
        $this->join = implode(' ',array_filter($request->join));
        $this->use = 'use ' . $this->modelNameSpace.'\\'.$this->class. ' as Model;';

        //** Model */
        $this->modelName = $this->class;
        $this->modelFillable = "['".implode("','",$request->clumn)."']";
        $this->modelUse = $this->modelUse();
        $this->modeltable = $this->table;
        $this->modelrelation = null;

        AnwarCrud::updateOrCreate([
            'name' =>$this->class,
            'controllers' =>$this->class
        ], [
            'name' =>$this->class,
            'controllers' =>$this->class,
            'uri' =>$this->table
        ]);

        $final = $this->getStub()->tablealies()->getAllVariabel()->createView()->createModel()->createController();

        return dump($final);
    }

    /**
     * @return $this
     */

    private function createView(): self
    {
        $stubFile['index'] = ANWAR_CRUD_BASE_PATH."/stubs/view/index.stub";
        $stubFile['form'] = ANWAR_CRUD_BASE_PATH."/stubs/view/form.stub";
        $stubFile['edit'] = ANWAR_CRUD_BASE_PATH."/stubs/view/edit.stub";
        $stubFile['create'] = ANWAR_CRUD_BASE_PATH."/stubs/view/create.stub";
        $foldername = $this->class;
        $viewPath = resource_path('views/').$foldername;
        if (!file_exists($viewPath)){
            if (!mkdir($viewPath) && !is_dir($viewPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $viewPath));
            }
        }
        foreach ($stubFile as $key=>$value){
            $stubContent = $key === 'form' ? $this->inputForm() : file_get_contents($value);
            if (!file_exists($viewPath."//$key.blade.php")){
                touch($viewPath."//$key.blade.php");
            }
            file_put_contents($viewPath."//$key.blade.php",$stubContent);
        }
        return $this;
    }

    /**
     * @return mixed
     * @desc generate dynamic form.
     */

    private function inputForm(){
        $formStub = ANWAR_CRUD_BASE_PATH."/stubs/view/form.stub";
        $validationRule = $this->validationRule;
        $formInput = $this->formDisplay;
        $formType = $this->formType;
        $formRelation = $this->formRelation;
        $model = '$model';
        $div = "";
        foreach ($formInput as $key => $value){
            $div .= "<div class='form-group'>";
            $label = ucwords(str_replace("_"," ",$key));
            $div .= "<label for='$key'>$label</label>\n";

            if (array_key_exists($key,$formRelation) && $formRelation[$key] != null && $this->relationalField() != ""){
                $div .= $this->relationalField();
                unset($formType[$key]);
            }else if($formType[$key] === 'textarea'){
                $div .= "<textarea class='form-control' name='$key'>{{@$model->$key}}</textarea>\n";
            }else{
                $div .= "<input type='$formType[$key]' class='form-control' name='$key' value='{{@$model->$key}}'>\n";
            }
            $div .= "</div>\n";
        }
        $div .= "<div class='form-group'>\n<input type='submit' class='btn btn-success' value='Submit'>\n</div>";
        if (!file_exists($formStub)){
            touch($formStub);
        }
        $stubFileContent = file_get_contents($formStub);
        return str_replace("@form",$div,$stubFileContent);
    }

    /**
     * @return $this
     * @desc Create Model.
     */

    public function createModel(): self
    {
        $model = ANWAR_CRUD_STUBS_PATH. '/model.stub';
        if (file_exists($model)){
            $modelDefaultContent = file_get_contents($model);
            $getAllVariable = [];
            $pattern = '~(@\w+)~';
            preg_match_all($pattern,$modelDefaultContent,$getAllVariable,PREG_PATTERN_ORDER);
            $getAllVariable =  array_map(static function ($arr){
                return str_replace('@', '',$arr);
            },$getAllVariable[0]);

            foreach ($getAllVariable as $var){
                if (property_exists($this, $var)){
                    $modelDefaultContent = str_replace("@{$var}",is_array($this->{$var}) ? http_build_query($this->{$var},"","##") : $this->{$var},$modelDefaultContent);
                }else{
                    $modelDefaultContent = str_replace("@{$var}","",$modelDefaultContent);
                }
            }
            //dd($modelDefaultContent);
            if (!file_exists(app_path('AnwarCrudGenerator')) && !mkdir($concurrentDirectory = app_path('AnwarCrudGenerator')) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $file = app_path('AnwarCrudGenerator/').$this->class.".php";
            if (!file_exists($file)){
                touch($file);
            }
            if (false == @file_put_contents($file,$modelDefaultContent)){
                throw new \RuntimeException(sprintf('File "%s" was not updated', $file));
            }
            ;
        }
        return $this;
    }

    /**
     * @return string
     */

    private function relationalField(): string
    {
        $relation = $this->formRelation;
        $relationalOption = "";
        
        foreach ($relation as $key=>$value){
            if ($value !== null && $value !== ""){
                $relationInfo = explode(",",$value);
                $relationalOption .= "<select class='form-control' id='$key' name='$key'>";
                $table = $relationInfo[0];
                unset($relationInfo[0]);
                $select = $relationInfo;
                $relationalOption .= $this->makeOption($table, $select);
                $relationalOption .= '</select>';
            }
        }
        return $relationalOption;
    }

    /**
     * @param $table
     * @param array $select
     * @return string
     */

    private function makeOption($table,$select = []): string
    {
        $db = DB::table($table)->get($select);

        $option = '<option></option>';
        $id = $select[1];
        $second = $select[2];
        foreach ($db as $v){
            //dd($v->$id);
            $option .= "<option value='{$v->$id}'>{$v->$second}</option>";
        }
        return $option;
    }

    /**
     * @return $this
     */

    private function getStub(): self
    {
        $stubFile = ANWAR_CRUD_BASE_PATH. '/stubs/controllerstubs.stub';
        if (file_exists($stubFile)){
            $this->stubs =  @file_get_contents($stubFile);
        }
        return $this;
    }

    /**
     * @return $this
     */

    private function getAllVariabel(): self
    {
        $stubFileContent =  $this->stubs;
        $pattern = '~(@\w+)~';
        preg_match_all($pattern,$stubFileContent,$this->stubVariable,PREG_PATTERN_ORDER);
        return $this;
    }

    /**
     * @return false|string
     */

    private function createController(){
        $stubFileContent =  $this->stubs;
        $stubVariable = array_map(static function ($arr){
            return str_replace('@', '',$arr);
        }, (array)$this->stubVariable);

        foreach ($stubVariable[0] as $stubVar){
            if (property_exists($this, $stubVar)){
                $stubFileContent = str_replace("@{$stubVar}",is_array($this->{$stubVar})?http_build_query($this->{$stubVar}, '', '##'): $this->{$stubVar},$stubFileContent);
            }else{
                $stubFileContent = str_replace("@{$stubVar}","",$stubFileContent);
            }
        }
        if (!file_exists("App/Http/Controllers/{$this->class}.php")){
            touch(app_path('Http/Controllers/').$this->class.".php");
            file_put_contents(app_path("Http/Controllers/").$this->class.".php",$stubFileContent);
        }
        return file_get_contents(app_path('Http/Controllers/').$this->class. '.php');
    }

    /**
     * @return $this
     */

    private function tablealies(): self
    {
        $this->tableAlies = substr($this->class,"0","2");
        return $this;
    }

    /**
     * @param array $validationrule
     * @return array
     */

    private function makeKeyValueString(array $validationrule = []): ?array
    {
        $generate = array_walk($validationrule,function (&$a,$b){
            $a = "$b => $a";
        });
        if ($generate){
            return $validationrule;
        }

        return [];
    }

    /**
     * @return string
     */

    private function modelUse()
    {
        return '';
    }




}
