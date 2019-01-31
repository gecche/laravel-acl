<?php

namespace Cupparis\Acl\Checkers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Main ACL class for checking does user have some permissions.
 */
class CheckerViewUser extends Checker
{

    public function check($resourceId,$userPermission,$userId) {
        if ($userId == $resourceId) {
            return true;
        }
        $userToCheck = User::find($resourceId);
        $role = $userToCheck->role->lists('id')->all();
        if (empty($role))
            return false;
        //Ho un solo id al momento: un utente, un ruolo

        $userRole = Auth::getRoleId();
        switch ($userRole) {
            case 'PARTECIPANTE_ADMIN':
                $user = User::find($userId);
                $roleId = current($role);
                $allowed_roles = $this->acl->getResourceIds('ACCESS_ROLE');

                return in_array($roleId, $allowed_roles['ids']) && $user->partecipante_id == $userToCheck->partecipante_id;
            default:

                $roleId = current($role);
                $allowed_roles = $this->acl->getResourceIds('ACCESS_ROLE');
                return $allowed_roles['allowed'] || (in_array($roleId, $allowed_roles['ids']));
        }
    }

}
