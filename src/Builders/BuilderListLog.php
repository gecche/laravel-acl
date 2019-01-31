<?php

namespace Cupparis\Acl\Builders;

/**
 * Main ACL class for checking does user have some permissions.
 */
class BuilderListLog extends Builder
{

    public function query($query, $userPermission, $userId, $primaryKey = 'id', $params = array()) {

        $roles_ids =  $this->acl->getResourceIds('ACCESS_ROLE',$userId);

        //ACCEDE A TUTTI GLI UTENTI E/O A TUTTI I RUOLI
        if ($roles_ids['allowed'])
            return $query;

        $allowed_roles_ids = $roles_ids['ids'];

        $query = $query->join('users' ,'logs.user_id','=','users.id')
            ->leftJoin('acl_users_roles','users.id', '=', 'acl_users_roles.user_id');

        $query = $query->where('users.id',$userId);
        if (!empty($allowed_roles_ids)) {
            $query = $query->orWhere(function ($q) use ($allowed_roles_ids) {
                $q->whereIn('acl_users_roles.role_id',$allowed_roles_ids);
            });
        }
        return $query;
    }

}
