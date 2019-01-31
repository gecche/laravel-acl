<?php

namespace Cupparis\Acl;

use Illuminate\Support\ServiceProvider;

class AclServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->singleton('acl', function($app)
        {
            return new AclManager($app);
        });
        $this->app->singleton('acl.driver', function($app)
        {
            return $app['acl']->driver();
        });

	}

//	private function getDriverClass()
//	{
//		$provider = Config::get('acl::driver');
//		return 'Cupparis\Acl\PermissionDrivers\\' . ucfirst($provider) . 'Driver';
//	}

}
