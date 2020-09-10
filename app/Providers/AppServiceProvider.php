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

		// --------------------------------------
		// MySQL5.7.7、またはMariaDB10.2.2より古い場合に必要です。
		Schema::defaultStringLength(191);

		// --------------------------------------
		// 強制的にHTTPS環境にします。
		// `asset()` などに影響します。
		// デフォルトは環境変数から自動的にセットされますが、
		// ↓この行を書くことで強制的に HTTPS 環境として認識させます。
		\URL::forceScheme('https');

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
