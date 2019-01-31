<?php

namespace Cupparis\Acl\Checkers;

use App\Models\User;

/**
 * Main ACL class for checking does user have some permissions.
 */
class CheckerCreateUser extends Checker
{

    public function check($resourceId,$userPermission,$userId) {
        $allowed_roles = $this->acl->getResourceIds('ACCESS_ROLE');
        return $allowed_roles['allowed'] || !empty($allowed_roles['ids']);
    }

}
