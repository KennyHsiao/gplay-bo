<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin name
    |--------------------------------------------------------------------------
    |
    | This value is the name of laravel-admin, This setting is displayed on the
    | login page.
    |
    */
    'name' => env('APP_NAME', 'Laravel-admin'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages. You can also set it as an image by using a
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo' => '<b>G</b>Play',

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin mini logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages when the sidebar menu is collapsed. You can
    | also set it as an image by using a `img` tag, eg
    | '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo-mini' => '<b>GP</b>',

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin bootstrap setting
    |--------------------------------------------------------------------------
    |
    | This value is the path of laravel-admin bootstrap file.
    |
    */
    'bootstrap' => app_path('Admin/bootstrap.php'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin route settings
    |--------------------------------------------------------------------------
    |
    | The routing configuration of the admin page, including the path prefix,
    | the controller namespace, and the default middleware. If you want to
    | access through the root path, just set the prefix to empty string.
    |
    */
    'route' => [

        'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),

        'namespace' => 'App\\Admin\\Controllers',

        'middleware' => ['web', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin install directory
    |--------------------------------------------------------------------------
    |
    | The installation directory of the controller and routing configuration
    | files of the administration page. The default is `app/Admin`, which must
    | be set before running `artisan admin::install` to take effect.
    |
    */
    'directory' => app_path('Admin'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin html title
    |--------------------------------------------------------------------------
    |
    | Html title for all pages.
    |
    */
    'title' => 'Admin',

    /*
    |--------------------------------------------------------------------------
    | Access via `https`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via https, set it to `true`.
    |
    */
    'https' => env('ADMIN_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Access via `ip`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via ip, set it to `ip` split by ','.
    | "false" meaning NOT to USE
    |
    */
    'ip_whitelist' => env('ADMIN_IP', false),
    /*
    |--------------------------------------------------------------------------
    | Access via `ip`
    |--------------------------------------------------------------------------
    |
    | Default Middleware
    |
    */
    'middleware' => [
        'ip_checker' => Xn\Admin\Middleware\AdminIPChecker::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Laravel-admin auth setting
    |--------------------------------------------------------------------------
    |
    | Authentication settings for all admin pages. Include an authentication
    | guard and a user provider setting of authentication driver.
    |
    | You can specify a controller for `login` `logout` and other auth routes.
    |
    */
    'auth' => [

        'controller' => App\Admin\Controllers\AuthController::class,

        'envController' => Xn\Admin\Controllers\XnEnvController::class,

        'controllers' => [
            'user' => App\Admin\Controllers\Admin\UserController::class,
            'role' => App\Admin\Controllers\Admin\RoleController::class,
            'permission' => Xn\Admin\Controllers\PermissionController::class,
            'menu' => Xn\Admin\Controllers\MenuController::class,
            'log' => Xn\Admin\Controllers\LogController::class,
            'locale' => Xn\Admin\Controllers\LocaleSupportController::class,
            'timezone' => Xn\Admin\Controllers\TimeZoneController::class,
            'language' => Xn\Admin\Controllers\LanguageLineController::class
        ],

        'guard' => 'admin',

        'guards' => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],

        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => App\Models\Administrator::class,
            ],
        ],

        // Add "remember me" to login form
        'remember' => false,

        // Redirect to the specified URI when user is not authorized.
        'redirect_to' => 'auth/login',

        // The URIs that should be excluded from authorization.
        'excepts' => [
            'auth/login',
            'auth/logout',
            'auth/refresh_captcha',
            'auth/auth_method',
            'auth/xn/env/*',
            'gpay/*'
        ],

        'captcha_config' => 'math',
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin upload setting
    |--------------------------------------------------------------------------
    |
    | File system configuration for form upload files and images, including
    | disk and upload path.
    |
    */
    'upload' => [

        // Disk in `config/filesystem.php`.
        'disk' => 'admin',

        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin database settings
    |--------------------------------------------------------------------------
    |
    | Here are database settings for laravel-admin builtin model & tables.
    |
    */
    'database' => [

        // Database connection for following tables.
        'connection' => '',

        // User tables and model.
        'users_table' => 'admin_users',
        'users_model' => App\Models\Administrator::class,

        // Role table and model.
        'roles_table' => 'admin_roles',
        'roles_model' => Xn\Admin\Auth\Database\Role::class,

        // Permission table and model.
        'permissions_table' => 'admin_permissions',
        'permissions_model' => Xn\Admin\Auth\Database\Permission::class,

        // Menu table and model.
        'menu_table' => 'admin_menu',
        'menu_model' => Xn\Admin\Auth\Database\Menu::class,

        // Locale and model.
        'locale_table' => 'admin_locales',
        'locale_model' => Xn\Admin\Auth\Database\LocaleSupport::class,

        // Timezone and model.
        'timezone_table' => 'admin_time_zones',
        'timezone_model' => Xn\Admin\Auth\Database\TimeZone::class,

        // LanguageLine and model.
        'languageline_table' => 'language_lines',
        'languageline_model' => Xn\Admin\Auth\Database\LanguageLineEx::class,

        // Session and model.
        'sessions_table' => 'admin_sessions',
        'sessions_model' => Xn\Admin\Auth\Database\DatabaseSession::class,

        // Pivot table for table above.
        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | User operation log setting
    |--------------------------------------------------------------------------
    |
    | By setting this option to open or close operation log in laravel-admin.
    |
    */
    'operation_log' => [

        'enable' => false,

        /*
         * Only logging allowed methods in the list
         */
        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'],

        /*
         * Routes that will not log to database.
         *
         * All method to path like: admin/auth/logs
         * or specific method to path like: get:admin/auth/logs.
         */
        'except' => [
            'admin/auth/logs*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check route permission.
    |--------------------------------------------------------------------------
    */
    'check_route_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check menu roles.
    |--------------------------------------------------------------------------
    */
    'check_menu_roles'       => true,

    /*
    |--------------------------------------------------------------------------
    | User default avatar
    |--------------------------------------------------------------------------
    |
    | Set a default avatar for newly created users.
    |
    */
    'default_avatar' => '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg',

    /*
    |--------------------------------------------------------------------------
    | Admin map field provider
    |--------------------------------------------------------------------------
    |
    | Supported: "tencent", "google", "yandex".
    |
    */
    'map_provider' => 'google',

    /*
    |--------------------------------------------------------------------------
    | Application Skin
    |--------------------------------------------------------------------------
    |
    | This value is the skin of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported:
    |    "skin-blue", "skin-blue-light", "skin-yellow", "skin-yellow-light",
    |    "skin-green", "skin-green-light", "skin-purple", "skin-purple-light",
    |    "skin-red", "skin-red-light", "skin-black", "skin-black-light".
    |
    */
    'skin' => 'skin-black-light',

    /*
    |--------------------------------------------------------------------------
    | Application layout
    |--------------------------------------------------------------------------
    |
    | This value is the layout of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse",
    | "sidebar-mini".
    |
    */
    'layout' => ['sidebar-mini', 'fixed'],

    /*
    |--------------------------------------------------------------------------
    | Login page background image
    |--------------------------------------------------------------------------
    |
    | This value is used to set the background image of login page.
    |
    */
    'login_background_image' => '',

    /*
    |--------------------------------------------------------------------------
    | Show version at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the version number of laravel-admin at the footer of
    | each page
    |
    */
    'show_version' => false,

    /*
    |--------------------------------------------------------------------------
    | Show environment at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the environment at the footer of each page
    |
    */
    'show_environment' => false,

    /*
    |--------------------------------------------------------------------------
    | Menu bind to permission
    |--------------------------------------------------------------------------
    |
    | whether enable menu bind to a permission
    */
    'menu_bind_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable default breadcrumb
    |--------------------------------------------------------------------------
    |
    | Whether enable default breadcrumb for every page content.
    */
    'enable_default_breadcrumb' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable assets minify
    |--------------------------------------------------------------------------
    */
    'minify_assets' => [

        // Assets will not be minified.
        'excepts' => [

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable sidebar menu search
    |--------------------------------------------------------------------------
    */
    'enable_menu_search' => true,

    /*
    |--------------------------------------------------------------------------
    | Alert message that will displayed on top of the page.
    |--------------------------------------------------------------------------
    */
    'top_alert' => '',

    /*
    |--------------------------------------------------------------------------
    | The global Grid action display class.
    |--------------------------------------------------------------------------
    */
    'grid_action_class' => \Xn\Admin\Grid\Displayers\Actions::class,

    /*
    |--------------------------------------------------------------------------
    | Extension Directory
    |--------------------------------------------------------------------------
    |
    | When you use command `php artisan admin:extend` to generate extensions,
    | the extension files will be generated in this directory.
    */
    'extension_dir' => app_path('Admin/Extensions'),

    /*
    |--------------------------------------------------------------------------
    | Settings for extensions.
    |--------------------------------------------------------------------------
    |
    | You can find all available extensions here
    | https://github.com/laravel-admin-extensions.
    |
    */
    'extensions' => [
        'json-editor' => [
            // set to false if you want to disable this extension
            'enable' => true,
            'config' => [
                'mode' => 'code',
                'modes' => ['code', 'form', 'text', 'tree', 'view'], // allowed modes
            ],
        ],
        'grapesjs-editor' => [
            // set to false if you want to disable this extension
            'enable' => true,
            'config' => [
                'styles' => [
                    "//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css",
                ],
                'scripts' => [
                    "//code.jquery.com/jquery-3.6.4.min.js",
                    "//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
                ]
            ],
            'plugins' => [
                'styles' => [
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/css/grapesjs-preset-webpage.min.css',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/css/tooltip.css',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/css/grapesjs-component-code-editor.min.css',
                ],
                'scripts' => [
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-preset-webpage.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/gjs-blocks-basic.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-plugin-forms.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-component-countdown.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-plugin-export.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-tabs.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-custom-code.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-touch.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-parser-postcss.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-tooltip.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-tui-image-editor.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-typed.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-style-bg.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-component-code-editor.js',
                    '//cdn.ckeditor.com/4.20.1/full-all/ckeditor.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-plugin-ckeditor.min.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-rulers.min.js',
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/grapesjs-ui-suggest-classes.min.js',
                    '//unpkg.com/grapesjs-blocks-flexbox@0.1.1/dist/grapesjs-blocks-flexbox.min.js',
                    //
                    'vendor/laravel-admin-ext/grapesjs-editor/grapesjs/plugins/plugins-opts.js',
                ]
            ],
        ],
        'drawflow-editor' => [
            // set to false if you want to disable this extension
            'enable' => true,
            'config' => [
    //
            ],
        ],
        'editor' => [
            'options' => [
                'filebrowserImageBrowseUrl' => '/{$prefix}?type=Images',
                'filebrowserImageUploadUrl' => '/{$prefix}/upload?type=Images&_token=',
                'filebrowserBrowseUrl' => '/{$prefix}?type=Files',
                'filebrowserUploadUrl' => '/{$prefix}/upload?type=Files&_token='
            ],
        ],
    ],

    'powered_by' => [
        'title' => '好想上岸工作室',
        'url' => '#'
    ],

    'notify' => [
        'line' => [
            'client_id' => env('NOTIFY_LINE_CLIENT_ID'),
            'client_secret' => env('NOTIFY_LINE_CLIENT_SECRET'),
        ]
    ],

    /**
     * switch lang from website
     */
    'multi_locale' => false,

    /**
     * switch timezone from website
     */
    'multi_timezone' => true,
];
