<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorizable model class
    |--------------------------------------------------------------------------
    |
    | This config specify which model class is authorizable.
    |
    */

    'authorizable' => \App\User::class,

    /*
    |--------------------------------------------------------------------------
    | Available scopes
    |--------------------------------------------------------------------------
    |
    | This config is a list of all available in application scopes.
    |
    */

    'scopes'   => [
        'users', 'settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models mapping
    |--------------------------------------------------------------------------
    |
    | This config allows you to map all application models to specific scopes.
    |
    | Example:      \App\Model::class => 'scope'
    |
    */

    'mapping'   => [
        \App\Users::class => 'users'
    ],

];
