<?php

namespace App\Providers;

use App\Providers\Session\AdminDatabaseSessionHandler;
use Flat3\Lodata\Facades\Lodata;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 確保自定義類型已被加載
        // if (!Type::hasType('citext')) {
        //     Type::addType('citext', 'App\DoctrineExtensions\DBAL\Types\Citext');
        // }

        // 獲取 Doctrine 的平台配置並註冊類型映射
        // $platform = DB::getDoctrineConnection()->getDatabasePlatform();
        // $platform->registerDoctrineTypeMapping('citext', 'citext');
        // Lodata::discover(\App\Models\System\Language::class);
        // Lodata::discover(\App\Models\Player\Transaction::class);
    }
}
