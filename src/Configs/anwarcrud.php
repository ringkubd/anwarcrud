<?php
return array(
    'datatype' => array (
        'int' => 'number',
        'varchar' => 'text',
        'timestamp' => 'datetime',
        'date' => 'date',
        'time' => 'time',
        'text' => 'textarea',
        'tinyint' => 'radio',
    ),
    'validationrule' => array(
        'text' => 'required|string',
        'number' => 'required|integer',
    ),
    'module_table' => 'anwarcrud_module',
    'except_column' => array('id', 'created_by', 'updated_by', 'created_at', 'updated_at'),
    'inputtype' => array(
        'checkbox',
        'datetime',
        'email',
        'file',
        'hidden',
        'image',
        'password',
        'radio',
        'text',
        'time',
        'select',
        'select2',
        'textarea',
        'date',
        'number'
    )
);
