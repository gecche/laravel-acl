<?php

namespace Gecche\Acl\Contracts;

/**
 * Abstract class for getting permissions.
 *
 * Acl\Checker works with this class so it can retrieve
 * global and users permissions.
 */
interface CachePermissionsContract
{

    public function getCacheUserPermissions($userId);


    public function setCacheUserPermissions($userId, $permissions);

}
