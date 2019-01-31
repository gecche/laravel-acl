<?php

namespace Cupparis\Acl\Builders;

use Cupparis\Acl\Contracts\AclContract;

/**
 * Main ACL class for checking does user have some permissions.
 */
class Builder
{

    protected $acl;

    function __construct(AclContract $acl)
    {
        $this->acl = $acl;
    }

    public function query($query,$userPermission,$userId,$primaryKey = 'id',$params = array())
    {
        // get resource IDs
        $ids = $this->acl->getResourceIds($userPermission['id'],$userId);

        if ($ids['allowed'])
            return $query;

        if (empty($ids['ids']))
            return $query->whereIn($primaryKey, array(-1));

        return $query->whereIn($primaryKey, $ids['ids']);

    }

}
