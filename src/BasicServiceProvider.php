<?php

namespace Abs\Basic;

use Illuminate\Support\ServiceProvider;

class BasicServiceProvider extends ServiceProvider {
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
			__DIR__ . '/views' => base_path('resources/views/abs/basic'),
		]);
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->app->make('Abs\Basic\EntityController');
		$this->app->make('Abs\Basic\API\AuthController');
	}
}
