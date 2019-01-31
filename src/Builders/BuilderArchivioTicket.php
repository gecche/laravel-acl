<?php

namespace Cupparis\Acl\Builders;

/**
 * Main ACL class for checking does user have some permissions.
 */
class BuilderArchivioTicket extends Builder
{

    public function query($query, $userPermission, $userId, $primaryKey = 'id', $params = array()) {

        $ids =  $this->acl->getResourceIds('ARCHIVIO_TICKET',$userId);

        //ACCEDE A TUTTI GLI UTENTI E/O A TUTTI I RUOLI
        if ($ids['allowed'])
            return $query;

        $allowed_ids = $ids['ids'];

        $query = $query->where('user_id', $userId);
        return $query;
    }

}
