<?php

namespace Gecche\Acl\Checkers;

use App\Models\User;
/**
 * Main ACL class for checking does user have some permissions.
 */
class CheckerViewLog extends Checker
{

    public function check($resourceId,$userPermission,$userId) {
        $userToCheck = User::find(\App\Log::find($resourceId)->user_id);
        $role = $userToCheck->role->lists('id')->all();
        if (empty($role)) {
            return false;
        }
        //Ho un solo id al momento: un utente, un ruolo
        $roleId = current($role);
        $allowed_roles = $this->acl->getResourceIds('ACCESS_ROLE');
        return $allowed_roles['allowed'] || (in_array($roleId, $allowed_roles['ids']));
    }

}
