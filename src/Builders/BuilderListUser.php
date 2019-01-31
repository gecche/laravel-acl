<?php

namespace Cupparis\Acl\Builders;

/**
 * Main ACL class for checking does user have some permissions.
 */
class BuilderListUser extends Builder
{

    public function query($query, $userPermission, $userId, $primaryKey = 'id', $params = array()) {

        $ids =  $this->acl->getResourceIds('LIST_USER',$userId);
        $roles_ids =  $this->acl->getResourceIds('ACCESS_ROLE',$userId);

        //ACCEDE A TUTTI GLI UTENTI E/O A TUTTI I RUOLI
        if ($ids['allowed'] || $roles_ids['allowed'])
            return $query;

        $allowed_ids = $ids['ids'];
        $allowed_roles_ids = $roles_ids['ids'];

        $query = $query->leftJoin('acl_users_roles','users.id', '=', 'acl_users_roles.user_id');
        $query = $query->where(function ($sq) use  ($allowed_roles_ids,$allowed_ids,$ids,$userId) {
            $sq = $sq->where('users.id', $userId);
            if (!empty($allowed_roles_ids)) {
                $sq = $sq->orWhere(function ($q) use ($allowed_roles_ids) {
                    $q->whereIn('acl_users_roles.role_id', $allowed_roles_ids);
                });
            }
            if (!empty($allowed_ids)) {
                $sq = $sq->orWhere('users.id', 'IN', $ids['ids']);
            }
        });
        return $query;
    }

}
