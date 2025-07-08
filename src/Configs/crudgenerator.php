<?php

return [
    // Middleware stack for admin routes
    'admin_middleware' => ['web', 'auth', 'admin'],

    // Role or permission required for admin access (optional)
    'admin_role' => null, // e.g. 'superadmin' or ['admin', 'manager']
    'admin_permission' => null, // e.g. 'manage-crud'

    // Admin layout view (Blade file)
    'admin_layout' => 'layouts.admin',

    // Helper for admin menu (can be replaced by user)
    'admin_menu' => [
        [
            'label' => 'Dashboard',
            'icon' => 'fa fa-list',
            'url' => '/admin/controller_list',
            'active' => true,
        ],
        [
            'label' => 'Home',
            'icon' => 'fa fa-home',
            'url' => '/admin',
        ],
        // Users can add more menu items here
    ],
];
