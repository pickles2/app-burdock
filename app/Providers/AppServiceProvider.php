<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
		// NOTE: MySQL5.7.7、またはMariaDB10.2.2より古い場合に必要です。
		\Illuminate\Support\Facades\Schema::defaultStringLength(191);

		// --------------------------------------
		// NOTE: 強制的にHTTPS環境にします。
		// `asset()` などに影響します。
		// デフォルトは環境変数から自動的にセットされますが、
		// ↓この行を書くことで強制的に HTTPS 環境として認識させます。
		if (env('APP_ENV') === 'production'){
			// ローカルの開発環境では、 https ではないほうがよい場合が多いので、
			// 本番(production)環境でのみ強制設定する。
			\URL::forceScheme('https');
		}

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
