<?php
namespace Lynx;
use Illuminate\Support\ServiceProvider;

//use Phpanonymous\It\Commands\Generate;

class LynxProvider extends ServiceProvider {
	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {

		$this->publishes([__DIR__ .'/lang'    => base_path('resources/lang')]);
		$this->publishes([__DIR__ .'/publish' => base_path('/')]);

	}
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {

		// $this->app->singleton('command.lynx', function ($app) {
		// 		return new Commands\It;
		// 	});

		// $this->commands([
		// 		Commands\Generate::class ,
		// 	]);
		//
	}

	public function provides() {
		return [
			//	'command.lynx',
		];
	}

}
