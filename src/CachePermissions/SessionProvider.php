<?php

namespace Gecche\Acl\CachePermissions;

use Gecche\Acl\Contracts\CachePermissionsContract;
use Illuminate\Contracts\Session\Session;

/**
 * Main ACL class for checking does user have some permissions.
 */
class SessionProvider implements CachePermissionsContract
{

    protected $session = null;


    public function __construct(Session $session)
    {

        $this->session = $session;

    }


    public function getCacheUserPermissions($userId)
    {
        return $this->session->get('acl_permissions_'.$userId,false);
    }

    public function setCacheUserPermissions($userId, $permissions)
    {
        return $this->session->put('acl_permissions_'.$userId,$permissions);
    }



}
