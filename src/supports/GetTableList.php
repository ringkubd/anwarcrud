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
            die('Connection failed: ');
        }
    }

    /**
     * @return $this|false|string
     */

    public function getTableFromDB(){
        $tables =  $this->connection->select("SHOW TABLES");
        $key = 'Tables_in_' .DB::getDatabaseName();

        foreach ($tables as $tab){
            if($tab->{$key} != TABLE_NAME && $tab->{$key} !== 'migrations'){
                $this->tables[] = $tab->{$key};
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function makeOption(): string
    {
        $options = '<option></option>';
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

    public function getCollumns($table): self
    {
        $databaseName = DB::getDatabaseName();
        $columns = DB::select(DB::raw("select * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='$table' and TABLE_SCHEMA='$databaseName'"));
        $exceptColumn = config('anwarcrud.except_column',[]);
        if ($columns){
            foreach ($columns as $clmn){
                if (!in_array($clmn->COLUMN_NAME, $exceptColumn, false)){
                    $this->columns[$clmn->COLUMN_NAME] = $clmn->DATA_TYPE;
                }
            }
        }
        return $this;
    }

    /**
     * @return string
     */

    public function makeTableView(): string
    {
        $columns = $this->columns;
        $tr = '';
        foreach ($columns as $clmns => $datatype){

            $label  = ucwords(str_replace('_', ' ', $clmns));
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
<span class="text-info font-italic font-weight-lighter">Example: left join tbl_trade as tt on tbl_i.trade = tt.id. where tbl_i is first 5 character of parent table</span>
</td>
<td>
<input type="text" name="selectjoin[]" class="form-control">
<span class="text-info">tt.trade,tt.id</span>
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

    public function makeValidationForm(): string
    {
        $columns = $this->columns;
        $tr = '';

        foreach ($columns as $clmns => $datatype){

            $label  = ucwords(str_replace('_', ' ', $clmns));
            $configKey = "anwarcrud.datatype.$datatype";
            $datatyp = config($configKey);
            $inputtype = config('anwarcrud.inputtype',[]);

            $inptyp = '';
            foreach ($inputtype as $intp){
                $optionLabel = ucwords($intp);
                $selected = $datatyp == $intp || $datatype == $intp ? 'selected' : '';
                $inptyp .= "<option $selected  value='$intp'>$optionLabel</option>";
            }
            $validationRule = config("anwarcrud.validationrule.$datatyp", 'required');

            $input = "<input type='text' class='form-control' name='third[name][$clmns]' id='$clmns' value='$clmns'/>";

            $form = <<<EOT
<tr class='$clmns'>
<td>
<input type="text" name="third[label][$clmns]" class="form-control" value="$label">
</td>
<td>
<div class='form-group md-5'>
$input
</div>
</td>
<td>
<select name="third[type][$clmns]" id="$clmns._type" class='form-control'>
$inptyp
</select>
</td>
<td>
<input type='text' name='third[validationrule][$clmns]' class='form-control' value='$validationRule'>
</td>
<td>
<input type="text" value="" class='form-control mb-2' name="third[relation][$clmns]">
<span class="text-info font-italic font-weight-lighter">Example: Users,id,name. Where table =  users, id =  local key , name show on options</span>
</td>
<td>
<input type="text" value="" class='form-control' name="third[onchange][$clmns]">
</td>
</tr>
EOT;

            $tr .= $form;

        }
        return $tr;
    }


}
