<?php
return[
    "datatype" => [
        "int"=>"number",
        "varchar"=>"text",
        "timestamp"=>"datetime",
        "date"=>"date",
        "time"=>"time",
        "text"=>"textarea",
        "tinyint"=>"radio",
    ],
    "validationrule"=>[
        "text"=>"required|string",
        "number"=>"required|integer",
    ],
    "module_table"=>"anwarcrud_module",
    "except_column"=>["id", "created_by", "updated_by","created_at", "updated_at"],
    "inputtype"=>[
        "checkbox",
        "datetime",
        "email",
        "file",
        "hidden",
        "image",
        "password",
        "radio",
        "text",
        "time",
        "select",
        "select2",
        "textarea",
        "date",
        "number"
    ]
];
