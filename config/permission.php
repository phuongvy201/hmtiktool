<?php

return [

    'models' => [

        'permission' => Spatie\Permission\Models\Permission::class,

        'role' => Spatie\Permission\Models\Role::class,

    ],

    'table_names' => [

        'roles' => 'roles',

        'permissions' => 'permissions',

        'model_has_permissions' => 'model_has_permissions',

        'model_has_roles' => 'model_has_roles',

        'role_has_permissions' => 'role_has_permissions',

    ],

    'column_names' => [
        'role_pivot_key' => null, //default 'role_id',
        'permission_pivot_key' => null, //default 'permission_id',

        'model_morph_key' => 'model_id',

        'team_foreign_key' => 'team_id',

    ],

    'register_permission_check_method' => true,

    'teams' => false, // Tắt teams để tránh lỗi

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'cache' => [

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        'store' => 'default',

    ],

    'enable_permission_models' => true,

    'permission_models' => [
        'permission' => \Spatie\Permission\Models\Permission::class,
    ],

    'super_admin_role' => 'system-admin',

    'super_admin_permissions' => [
        'view-users',
        'create-users',
        'edit-users',
        'delete-users',
        'view-teams',
        'create-teams',
        'edit-teams',
        'delete-teams',
        'view-roles',
        'create-roles',
        'edit-roles',
        'delete-roles',
        'view-system-settings',
        'edit-system-settings',
    ],

];
