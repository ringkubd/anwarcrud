<?php
return [
    // Data type mappings for form inputs
    'datatype' => [
        'int' => 'number',
        'varchar' => 'text',
        'timestamp' => 'datetime',
        'date' => 'date',
        'time' => 'time',
        'text' => 'textarea',
        'tinyint' => 'radio',
    ],

    // Default validation rules
    'validationrule' => [
        'text' => 'required|string',
        'number' => 'required|integer',
    ],

    // Database configuration
    'module_table' => 'anwarcrud_module',
    'except_column' => ['id', 'created_by', 'updated_by', 'created_at', 'updated_at'],

    // Available input types
    'inputtype' => [
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
    ],

    // Package-specific settings
    'admin_middleware' => ['web'], // Default middleware, can be overridden
    'admin_permission' => null, // Optional permission check

    // File generation paths (relative to Laravel app)
    'model_namespace' => 'App\\Models',
    'controller_namespace' => 'App\\Http\\Controllers',
    'view_path' => 'resources/views',

    // Features
    'features' => [
        'api_generation' => true,
        'test_generation' => true,
        'documentation_generation' => true,
        'soft_deletes' => true,
        'timestamps' => true,
    ],

    // UI Configuration
    'ui' => [
        'theme' => 'bootstrap4',
        'show_preview' => true,
        'enable_live_preview' => true,
    ],
];
