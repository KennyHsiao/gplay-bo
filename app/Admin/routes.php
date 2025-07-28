<?php

use App\Admin\Controllers\Platform\MerchantController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    // 'as'            => config('admin.route.prefix'),
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('home');

    # LINE
    $router->get('/', 'HomeController@index')->name('home');
    $router->group([
        'prefix'        => 'line',
        'namespace'     => 'Line',
    ], function($router){
        $router->match(['get', 'post'], 'line-push/send/{id}', 'LINEBotPushController@sendPush')->name('push.send');
        $router->get('line-push/{type}', 'LINEBotPushController@pushCreate')->name('push.create');
        $router->post('line-push/{type}', 'LINEBotPushController@pushStore')->name('push.create');
        $router->get('line-push/{type}/{id}', 'LINEBotPushController@pushEdit')->name('push.edit');
        $router->put('line-push/{type}/{id}', 'LINEBotPushController@pushUpdate')->name('push.edit');
        $router->resource('line-push', LINEBotPushController::class);
        # LINE推播
        $router->post('line-menu/link/{id}', 'LINEBotMenuController@postBulkLinkToUsers')->name('line-menu.link')->middleware('merchant');
        $router->post('line-menu/delete/{id}', 'LINEBotMenuController@postBulkDeleteFromLINE')->name('line-menu.delete')->middleware('merchant');
        $router->post('line-menu/upload/{id}', 'LINEBotMenuController@postUploadToLINE')->name('line-menu.upload')->middleware('merchant');
        $router->get('line-menu-options', 'LINEBotMenuController@menuOptions')->name('line-menu.options');
        $router->resource('line-menu', LINEBotMenuController::class);
        # LINE選單建立
        $router->resource('line-imagemap', LINEBotImageMapController::class);
        # 影像地圖
    });

    # 遊戲管理
    $router->group([
        'prefix'        => 'gm',
        'namespace'     => 'GameManage',
    ], function($router){
        $router->resource('vendors', GameVendorController::class);
        $router->resource('games', GameListController::class);
        $router->resource('tags', GameTagController::class);
        $router->resource('types', GameTypeController::class);
    });

    # 商戶管理
    $router->group([
        'prefix'        => 'mc',
        'namespace'     => 'Merchant',
    ], function($router){
        $router->resource('vendors', GameVendorController::class);
        $router->resource('games', GameListController::class);
        $router->resource('types', GameTypeController::class);
        # 連結
        $router->resource('hyperlinks', HyperlinkController::class);
        # 文章管理
        $router->resource('articles', ArticleController::class);

        $router->resource('banners', BannerController::class);

        $router->resource('marquees', MarqueeController::class);
    });

    # 玩家管理
    $router->group([
        'prefix'        => 'player',
        'namespace'     => 'Player',
        'middleware'    => 'merchant'
    ], function($router){
        $router->resource('members', MemberController::class);
        $router->resource('game-records', GameRecordController::class);
        $router->resource('transactions', TransactionController::class);
    });

    # 財務
    $router->group([
        'namespace'     => 'Finance',
        'prefix'        => 'finance',
        'middleware'    => 'merchant'
    ], function($router){
        $router->resource('player-adjustments', PlayerAdjustmentController::class);
        $router->resource('ton-withdraws', TonWithdrawController::class);
        $router->resource('crypto-withdraws', CryptoWithdrawController::class);
    });

    # 平台設置
    $router->group([
        'prefix'        => 'platform',
        'namespace'     => 'Platform',
    ], function($router){
        $router->resource('companies', CompanyController::class);
        $router->resource('merchants', MerchantController::class);

        # 房型管理
        $router->resource('room-types', RoomTypeController::class);
        # 好友房型
        $router->resource('friend-room-types', FriendRoomTypeController::class);
    });

    # 系統配置
    $router->group([
        'prefix'        => 'system',
        'namespace'     => 'System',
    ], function($router){
        # global params
        $router->resource('global-params', AdminGlobalParamController::class);
        $router->post('database-init', 'DatabaseSettingController@initDatabase')->name('database.init');
        $router->post('database-report-init', 'DatabaseSettingController@initReportDatabase')->name('database.report.init');
        $router->resource('database-settings', DatabaseSettingController::class);
        $router->resource('currencies', CurrencyController::class);
        $router->resource('languages', LanguageController::class);
    });

    # API
    $router->group([
        'prefix'        => 'api',
        'namespace'     => 'Api',
    ], function($router){
        $router->post('switch-merchant', 'EnvParamController@switchMerchant');
        $router->post('switch-timezone', 'EnvParamController@switchTimezone');
        $router->post('switch-lang', 'EnvParamController@switchLang');
        $router->post('get-internalmessage', 'EnvParamController@internalMessage');

        $router->post('deposit', 'APIController@transferIn')->name('api.deposit');
        $router->post('withdraw', 'APIController@transferOut')->name('api.withdraw');
        $router->post('auth-code-reset', 'APIController@authCodeReset')->name('api.auth-code-reset');

        $router->get('get-player', 'APIController@player')->name('api.get-player')->middleware('merchant');

        $router->post('auth-confirm', 'APIController@confirmAuth')->name('api.auth-confirm');
        $router->post('auth-confirm-x', 'APIController@confirmAuthX')->name('api.auth-confirm-x');

        $router->get('get-companyip', 'APIController@companyIP')->name('api.get-companyip');
        $router->get('get-unique-company-code', 'APIController@getUniqueCompanyCode')->name('api.get-unique-company-code');

        $router->get('get-vendor-setting', 'APIController@vendorSetting')->name('api.get-vendor-setting');
    });

    // LINE-Notify
    $router->get('line-notify-cancel', 'AuthController@lineNotifyCancel')->name('my.line-notify.cancel');
    $router->get('line-notify-callback', 'AuthController@lineNotifyCallback')->name('my.line-notify.callback');

    $router->resource('devextreme', DevExtremeController::class);

    // \App\Models\Player\Member::routes($router, ['merchant']);
    // \App\Models\System\Language::routes($router, ['merchant']);
});
