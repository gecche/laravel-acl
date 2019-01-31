<?php

namespace Gecche\Acl\CachePermissions;

use Gecche\Acl\Contracts\CachePermissionsContract;

/**
 * Main ACL class for checking does user have some permissions.
 */
class LocalProvider implements CachePermissionsContract
{

    protected $cached = array();

    //ORM Extension


    public function getCacheUserPermissions($userId)
    {
        return array_get($this->cached,$userId,false);
    }

    public function setCacheUserPermissions($userId, $permissions)
    {
        $this->cached[$userId] = $permissions;
    }




}
