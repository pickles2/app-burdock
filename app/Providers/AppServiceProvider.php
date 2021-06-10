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
		// MySQL5.7.7、またはMariaDB10.2.2より古い場合に必要です。
		\Illuminate\Support\Facades\Schema::defaultStringLength(191);


		// --------------------------------------
		// 強制的に HTTPS (または HTTP) 環境にします。
		$forceScheme = config('burdock.url_scheme');
		if ( strlen( $forceScheme ) ){
			// `url()` や `asset()` などに影響します。
			// .env の `BD_FORCE_SCHEME` に設定したスキームに従います。
			// デフォルトは `強制しない` (=環境変数から得た値と同じ) です。

			// ↓この行を書くことで強制的に HTTPS (または HTTP) 環境として認識させます。
			\URL::forceScheme( $forceScheme );
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
