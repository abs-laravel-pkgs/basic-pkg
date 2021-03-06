<?php

namespace Abs\BasicPkg;

use Illuminate\Support\ServiceProvider;

class BasicPkgServiceProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
		$this->loadRoutesFrom(__DIR__ . '/routes/api.php');
		$this->loadMigrationsFrom(__DIR__ . '/migrations');
		$this->loadViewsFrom(__DIR__ . '/views', 'basic-pkg');
		$this->publishes([
			__DIR__ . '/public' => base_path('public'),
			__DIR__ . '/config/config.php' => config_path('gigo-pkg.php'),
		]);
		$this->app->register(
			'Abs\BasicPkg\Providers\RelationshipValidationServiceProvider'
		);
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
	}
}
