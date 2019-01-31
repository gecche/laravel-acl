<?php

namespace Gecche\Acl;

use Gecche\Acl\CachePermissions\CacheProvider;
use Gecche\Acl\CachePermissions\LocalProvider;
use Gecche\Acl\CachePermissions\SessionProvider;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Main ACL class for checking does user have some permissions.
 */
class AclManager extends Manager
{

    protected $config;

    /**
     * AclManager constructor.
     */
    public function __construct($app)
    {
        $this->app = $app;
        $config = config('acl',[]);

        $this->config = $this->app['config']['acl'] ?: [];
    }


    /**
     * Create an instance of the Eloquent driver.
     *
     * @return \Gecche\Acl\AclGuard
     */
    public function createEloquentDriver()
    {


        $provider = new EloquentPermissionProvider($config);
        $cache = $this->cache();

        return new AclGuard($provider, $this->app['auth.driver'],$cache,$this->config);
    }


    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['acl.driver'];
    }

    /**
     * Call a custom driver creator.
     *
     * @param  string  $driver
     * @return \Gecche\Acl\AclGuard
     */
    protected function callCustomCreator($driver)
    {
        $custom = parent::callCustomCreator($driver);

        if ($custom instanceof AclGuard) return $custom;

        $cache = $this->cache();

        return new AclGuard($custom, $this->app['auth'],$cache,$this->config);
    }

    protected function cache() {

        $models = array_get($this->config,'models');

        $cache = array_get($this->config,'cache_type','local');

        $method = 'create'.Str::studly($cache).'Cache';

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        throw new InvalidArgumentException("Cache [$cache] not supported.");


    }

    protected function createLocalCache() {
        return new LocalProvider();
    }

    protected function createSessionCache() {
        return new SessionProvider($this->app['session']);
    }

    protected function createCacheCache() {
        return new CacheProvider($this->app['cache']);
    }





}
