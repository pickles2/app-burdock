<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// MySQL5.7.7、またはMariaDB10.2.2より古い場合に必要です。
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // MySQL5.7.7、またはMariaDB10.2.2より古い場合に必要です。
        Schema::defaultStringLength(191);

        // グローバル変数
        // 管理者のID番号を1とする
        // 参照: https://stackoverflow.com/questions/28356193/
        // config(['admin_id' => 1]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
