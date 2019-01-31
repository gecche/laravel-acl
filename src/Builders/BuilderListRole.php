<?php

namespace Gecche\Acl\Builders;

/**
 * Main ACL class for checking does user have some permissions.
 */
class BuilderListRole extends Builder
{

    public function query($query, $userPermission, $userId, $primaryKey = 'id', $params = array()) {

        $ids =  $this->acl->getResourceIds('ACCESS_ROLE',$userId);

        //ACCEDE A TUTTI GLI UTENTI E/O A TUTTI I RUOLI
        if ($ids['allowed'])
            return $query;

        $allowed_ids = $ids['ids'];

        if (!empty($allowed_ids)) {
            $query = $query->whereIn('acl_roles.id', $ids['ids']);
        }
        return $query;
    }

}
