<?php

namespace Gecche\Acl;

use Illuminate\Support\Manager;

/**
 * Main ACL class for checking does user have some permissions.
 */
class AclManager extends Manager
{

    /**
     * Create an instance of the Eloquent driver.
     *
     * @return \Illuminate\Auth\Guard
     */
    public function createEloquentDriver()
    {

        $models = $this->app['config']['acl.models'];
        $provider = new EloquentPermissionProvider($models);

        $superusers = $this->app['config']['acl.superusers'];
        $guestuser = $this->app['config']['acl.guestuser'];
        $loginrole = $this->app['config']['acl.loginrole'];
        $checkers_namespaces = $this->app['config']['acl.checkers_namespaces'];
        $builders_namespaces = $this->app['config']['acl.builders_namespaces'];

        $cache = $this->app['config']['acl.cache'] ? $this->app['config']['acl.cache'] : null;
        return new AclGuard($provider, $this->app['auth.driver'],$superusers,$guestuser,$loginrole,$checkers_namespaces,$builders_namespaces,$cache);
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
     * @return \Illuminate\Auth\Guard
     */
    protected function callCustomCreator($driver)
    {
        $custom = parent::callCustomCreator($driver);

        if ($custom instanceof AclGuard) return $custom;

        $superusers = $this->app['config']['acl.superusers'];
        $guestuser = $this->app['config']['acl.guestuser'];
        $loginrole = $this->app['config']['acl.loginrole'];
        $checkers_namespaces = $this->app['config']['acl.checkers_namespaces'];
        $builders_namespaces = $this->app['config']['acl.builders_namespaces'];

        return new AclGuard($custom, $this->app['auth'],$superusers,$guestuser,$loginrole,$checkers_namespaces, $builders_namespaces);
    }

}
