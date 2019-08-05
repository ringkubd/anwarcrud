<?php
return[
    "datatype" => [
        "int"=>"number",
        "varchar"=>"text",
        "timestamp"=>"text",
        "date"=>"date",
        "time"=>"time",
        "text"=>"textarea"
    ],
    "validationrule"=>[
        "text"=>"required|string",
        "number"=>"required|number",
    ],
    "module_table"=>"anwarcrud_module",
    "except_column"=>["id", "created_at", "updated_at"]
];
