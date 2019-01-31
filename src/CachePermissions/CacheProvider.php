<?php

namespace Gecche\Acl\CachePermissions;

use Gecche\Acl\Contracts\CachePermissionsContract;
use Illuminate\Cache\Repository;

/**
 * Main ACL class for checking does user have some permissions.
 */
class CacheProvider implements CachePermissionsContract
{

    /**
     * The cache store implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache = null;
    protected $minutes = null;

    public function __construct(Repository $cache, $minutes = null)
    {

        $this->cache = $cache;
        $this->minutes = is_int($minutes) ? $minutes : config('acl.cache_minutes',60);
    }


    public function getCacheUserPermissions($userId)
    {
        if ($this->cache->has('acl_permissions_'.$userId)) {
            return $this->cache->get('acl_permissions_'.$userId);
        }
        return false;
    }

    public function setCacheUserPermissions($userId, $permissions)
    {
        $this->cache->put('acl_permissions_'.$userId,$permissions,$this->minutes);
    }



}
