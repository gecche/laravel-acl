<?php


return [
    /*
      |--------------------------------------------------------------------------
      | Default Permission Provider
      |--------------------------------------------------------------------------
      |
      | This option controls what provider will ACL use.
      | Currently there is only one provider "eloquent".
      |
      | Supported: "eloquent"
      |
     */
    'driver' => 'eloquent',

    'models' => [
        'Permission' => 'App\Models\Permission',
        'Role' => 'App\Models\Role',
        'UserPermission' => 'App\Models\UserPermission',
        'RolePermission' => 'App\Models\RolePermission',
        'UserRole' => 'App\Models\UserRole',
    ],

    'checkers_namespaces' => [
        '\App\Acl\Checkers',
        '\Cupparis\Acl\Checkers',
    ],

    'builders_namespaces' => [
        '\App\Acl\Builders',
        '\Cupparis\Acl\Builders',
    ],

    /*
      |--------------------------------------------------------------------------
      | Super users array
      |--------------------------------------------------------------------------
      |
      | Put here user IDs that will have superuser rights.
      |
     */
    'superusers' => array(1, 2),
    /*
      |--------------------------------------------------------------------------
      | Guest users ID
      |--------------------------------------------------------------------------
      |
      | Put here ID that will used for setting permissions to guest users.
      |
     */
    'guestuser' => 0,
    /*
      |--------------------------------------------------------------------------
      | Basic login role
      |--------------------------------------------------------------------------
      |
      | Put here ID that will used for setting permissions to login users.
      |
     */
    'loginrole' => 'LOGIN',

    'cache_type' => 'local', //session,cache,local
    'cache_minutes' => 60, //living minutes, valid only with cache_type = cache


];

