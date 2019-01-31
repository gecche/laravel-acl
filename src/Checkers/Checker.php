<?php

namespace Gecche\Acl\Checkers;

use Gecche\Acl\Contracts\AclContract;

/**
 * Main ACL class for checking does user have some permissions.
 */
class Checker
{

    protected $acl;

    function __construct(AclContract $acl)
    {
        $this->acl = $acl;
    }

    public function check($resourceId,$userPermission,$userId) {
        //Prendo l'allowed base del permesso
        $allowed = $userPermission['allowed'];

        //A questo punto se è true ok
        if ($allowed) {
            return $allowed;
        }

        //Altrimenti controllo le resources ids:

        // Se resourceid è presente è necessario che quell'id sia allowed
        if (in_array($resourceId, $userPermission['ids'])) {
            return true;
        }

        // Se resourceid non è richiesto (ad esempio list_user)
        // basta che ci sia qualche id attivo per dare il permesso
        if (!$userPermission['resource_id_required'] &&
            empty($resourceId) &&
            !empty($userPermission['ids'])
        ) {
            return true;
        }

        return false;
    }

}
