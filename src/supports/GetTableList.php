<?php


namespace Anwar\CrudGenerator\Supports;


use Illuminate\Support\Facades\DB;

class GetTableList
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    private $tables = [];

    private $columns = [];

    /**
     * GetTableList constructor.
     */
    public function __construct()
    {
        $this->connection = DB::connection();
        // Check connection
        if (!$this->connection) {
            die("Connection failed: ");
        }
    }

    /**
     * @return $this|false|string
     */

    public function getTableFromDB(){
        $tables =  $this->connection->select("SHOW TABLES");
        $key = "Tables_in_".DB::getDatabaseName();

        foreach ($tables as $tab){
            if($tab->{$key} != TABLE_NAME && $tab->{$key} != "migrations"){
                $this->tables[] = $tab->{$key};
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function makeOption(){
        $options = "<option></option>";
        $tables = $this->tables;

        foreach ($tables as $table){
            $tableOptionName = ucwords(str_replace("_"," ",$table));
            $options .="<option value='{$table}'>{$tableOptionName}</option>";
        }
        return $options;
    }

    /**
     * @param $table
     * @return $this
     */

    public function getCollumns($table){
        $columns = DB::select(DB::raw("select * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='$table'"));
        $exceptColumn = config("anwarcrud.except_column",[]);
        if ($columns){
            foreach ($columns as $clmn){
                if (!in_array($clmn->COLUMN_NAME, $exceptColumn)){
                    $this->columns[$clmn->COLUMN_NAME] = $clmn->DATA_TYPE;
                }
            }
        }
        return $this;
    }

    /**
     * @return string
     */

    public function makeTableView(){
        $columns = $this->columns;
        $tr = "";
        foreach ($columns as $clmns => $datatype){

            $label  = ucwords(str_replace('_'," ", $clmns));
            $configKey = "anwarcrud.datatype.$datatype";
            $datatyp = config($configKey);
            $validationRule = config("anwarcrud.validationrule.$datatyp");

            $input = "<input type='text' class='form-control' name='clumn[]' id='$clmns' value='$clmns'/>";

            $form = <<<EOT
<tr class='$clmns'>
<td>$label</td>
<td>
<div class='form-group md-5'>
$input
</div>
</td>
<td>
<input type='text' name='join[]' class='form-control' value=''>
</td>
<td>
<input type="text" name="selectjoin[]" class="form-control">
</td>

</tr>
EOT;
            $tr .= $form;

        }
        return $tr;
    }

    /**
     * @return string
     */

    public function makeValidationForm(){
        $columns = $this->columns;

        $tr = "";

        foreach ($columns as $clmns => $datatype){

            $label  = ucwords(str_replace('_'," ", $clmns));
            $configKey = "anwarcrud.datatype.$datatype";
            $datatyp = config($configKey);
            $validationRule = config("anwarcrud.validationrule.$datatyp");

            $input = "<input type='text' class='form-control' name='name[]' id='$clmns' value='$clmns'/>";

            $form = <<<EOT
<tr class='$clmns'><td>$label</td><td><div class='form-group md-5'>
$input
</div>
</td>
<td>
<input type='text' name='validationrule[]' class='form-control' value='$validationRule'>
</td>
</tr>
EOT;


            $tr .= $form;

        }
        return $tr;
    }


}
