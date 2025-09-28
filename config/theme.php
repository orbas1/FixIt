<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asset url path
    |--------------------------------------------------------------------------
    |
    | The path to asset, this config can be cdn host.
    | eg. http://cdn.domain.com
    |
    */

    'assetUrl' => env('APP_ASSET_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Theme Default
    |--------------------------------------------------------------------------
    |
    | If you don't set a theme when using a "Theme" class
    | the default theme will replace automatically.
    |
    */

    'themeDefault' => env('APP_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Layout Default
    |--------------------------------------------------------------------------
    |
    | If you don't set a layout when using a "Theme" class
    | the default layout will replace automatically.
    |
    */

    'layoutDefault' => env('APP_THEME_LAYOUT', 'layout'),

    /*
    |--------------------------------------------------------------------------
    | Path to lookup theme
    |--------------------------------------------------------------------------
    |
    | The root path contains themes collections.
    |
    */

    'themeDir' => env('APP_THEME_DIR', 'themes'),

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | Class namespace.
    |
    */

    'namespaces' => [
        'widget' => 'App\Widgets',
    ],

    /*
    |--------------------------------------------------------------------------
    | View Fallback path
    |--------------------------------------------------------------------------
    |
    | You can define a view fallback path that will be appended when the theme
    | doesn't have its view file. This is useful if you want to have a base
    | theme in different folder.
    |
    */

    'view_fallback' => '',

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    |
    | You can hook a theme when event fired on activities this is cool
    | feature to set up a title, meta, default styles and scripts.
    |
    */

    'events' => [

        // Before all event, this event will effect for global.
        'before' => [],

        // This event will fire as a global you can add any assets you want here.
        'asset' => [],

    ],

];
