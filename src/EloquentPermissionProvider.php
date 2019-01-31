<?php

namespace Gecche\Acl;

/**
 * Default Eloquent permission provider.
 */
class EloquentPermissionProvider implements \Gecche\Acl\Contracts\PermissionContract
{

    public $models;
    public $allPermissions;

    function __construct($models)
    {
        $this->models = $models;
    }


    /**
     * @see parent description
     */
    public function getUserPermissions($userId)
    {
        $userPermissionModel = $this->models['UserPermission'];
        $userPermissions = $userPermissionModel::where('user_id', '=', $userId)->get()->toArray();

        foreach ($userPermissions as &$permission) {
            $permission = $this->parseUserOrRolePermission($permission);
        }

        return $userPermissions;
    }

    /**
     * @see parent description
     */
    public function getRolePermissions($roleId)
    {
        $rolePermissionModel = $this->models['RolePermission'];
        $rolePermissions = $rolePermissionModel::where('role_id', '=', $roleId)->get()->toArray();

        foreach ($rolePermissions as &$permission) {
            $permission = $this->parseUserOrRolePermission($permission);
        }

        return $rolePermissions;
    }

    /**
     * @see parent description
     */
    public function getUserPermissionsBasedOnRoles($userId)
    {
        $userRoleModel = $this->models['UserRole'];
        $rolePermissionModel = $this->models['RolePermission'];
        $userRole = new $userRoleModel;
        $rolePermission = new $rolePermissionModel;
        $userRolePermissions = $userRoleModel::where('user_id', $userId)
            ->leftJoin($rolePermission->getTable(), $userRole->getTable().'.role_id', '=', $rolePermission->getTable().'.role_id')
            ->get(array($rolePermission->getTable().'.*'))->toArray();

        foreach ($userRolePermissions as &$permission) {
            $permission = $this->parseUserOrRolePermission($permission);
        }

        return $userRolePermissions;
    }

    private function parseUserOrRolePermission(array $permission)
    {
        if (empty($permission)) {
            return $permission;
        }

        $permission['id'] = $permission['permission_id'];
        unset($permission['permission_id']);

        if ($permission['ids'] != null) {
            // create array from string - try to explode by ','
            $permission['ids'] = explode(',', $permission['ids']);
        } else {
			  $permission['ids'] = array();
		}

        return $permission;
    }

    /**
     * @see parent description
     */
    public function getAllPermissions()
    {
        if ($this->allPermissions) {
            return $this->allPermissions;
        }

        $permissionModel = $this->models['Permission'];
        $permissions = $permissionModel::all()->toArray();

        foreach ($permissions as &$permission) {
            $routes = json_decode($permission['route'], true);
            //Sempre un array: se non è un json valido sarà un array vuoto.
            if ($routes !== null) {
                // if route is json encoded string
                if (!is_array($routes)) {
                    $permission['route'] = array($routes);
                } else {
                    $permission['route'] = $routes;
                }
            } else {
                $permission['route'] = array();
            }

            $permission['resource_id_required'] = (bool) $permission['resource_id_required'];
        }

        $this->allPermissions = $permissions;
        return $permissions;
    }

    /**
     * @see parent description
     */
    public function createPermission($id, $allowed, $route, $resourceIdRequired, $name)
    {
        $permissionModel = $this->models['Permission'];
        return $permissionModel::create(array(
            'id' => $id,
            'route' => is_array($route)? json_encode($route) : $route,
            'resource_id_required' => $resourceIdRequired,
            'name' => $name,
        ))->toArray();
    }

    /**
     * @see parent description
     */
    public function removePermission($id)
    {
        $permissionModel = $this->models['Permission'];
        return $permissionModel::destroy($id);
    }

    /**
     * @see parent description
     */
    public function assignUserPermission(
        $userId, $permissionId, $allowed = null, array $ids = null
    ) {
        $userPermissionModel = $this->models['UserPermission'];
        return $userPermissionModel::create(array(
            'permission_id' => $permissionId,
            'user_id' => $userId,
            'allowed' => $allowed,
            'ids' => (!empty($ids))? implode(',', $ids) : null,
        ))->toArray();
    }

    /**
     * @see parent description
     */
    public function assignRolePermission(
        $roleId, $permissionId, $allowed = null, array $ids = null
    ) {
        $rolePermissionModel = $this->models['RolePermission'];
        return $rolePermissionModel::create(array(
            'permission_id' => $permissionId,
            'role_id' => $roleId,
            'allowed' => $allowed,
            'ids' => (!empty($ids))? implode(',', $ids) : null,
        ))->toArray();
    }

    /**
     * @see parent description
     */
    public function removeUserPermission($userId, $permissionId)
    {
        $userPermissionModel = $this->models['UserPermission'];
        $q = $userPermissionModel::where('permission_id', '=', $permissionId);

        if ($userId !== null) {
            $q->where('user_id', '=', $userId);
        }

        return $q->delete();
    }

    /**
     * @see parent description
     */
    public function removeUserPermissions($userId)
    {
        $userPermissionModel = $this->models['UserPermission'];
        return $userPermissionModel::where('user_id', '=', $userId)->delete();
    }

    /**
     * @see parent description
     */
    public function removeRolePermission($roleId, $permissionId)
    {
        $rolePermissionModel = $this->models['RolePermission'];
        $q = $rolePermissionModel::where('permission_id', '=', $permissionId);

        if ($roleId !== null) {
            $q->where('role_id', '=', $roleId);
        }

        return $q->delete();
    }

    /**
     * @see parent description
     */
    public function removeRolePermissions($roleId)
    {
        $rolePermissionModel = $this->models['RolePermission'];
        return $rolePermissionModel::where('role_id', '=', $roleId)->delete();
    }

    /**
     * @see parent description
     */
    public function updateUserPermission(
        $userId, $permissionId, $allowed = null, array $ids = null
    ) {
        $userPermissionModel = $this->models['UserPermission'];
        return $userPermissionModel::where('user_id', '=', $userId)
                            ->where('permission_id', '=', $permissionId)
                            ->update(array(
                                'allowed' => $allowed,
                                'ids' => (!empty($ids))? implode(',', $ids) : null,
                            ));
    }

    /**
     * @see parent description
     */
    public function updateRolePermission(
        $roleId, $permissionId, $allowed = null, array $ids = null
    ) {
        $rolePermissionModel = $this->models['RolePermission'];
        return $rolePermissionModel::where('role_id', '=', $roleId)
                            ->where('permission_id', '=', $permissionId)
                            ->update(array(
                                'allowed' => $allowed,
                                'ids' => (!empty($ids))? implode(',', $ids) : null,
                            ));
    }

    /**
     * @see parent description
     */
    public function deleteAllPermissions()
    {
        $permissionModel = $this->models['Permission'];
        return $permissionModel::truncate();
    }

    /**
     * @see parent description
     */
    public function deleteAllUsersPermissions()
    {
        $userPermissionModel = $this->models['UserPermission'];
        return $userPermissionModel::truncate();
    }

    /**
     * @see parent description
     */
    public function deleteAllRolesPermissions()
    {
        $rolePermissionModel = $this->models['RolePermission'];
        return $rolePermissionModel::truncate();
    }

     /**
     * @see parent description
     */
    public function insertRole($id, $name, $parentId = null)
    {
        $roleModel = $this->models['Role'];
        return $roleModel::create(array(
                'id' => $id,
                'name' => $name,
                'parent_id' => $parentId
        ))->toArray();
    }

    /**
     * @see parent description
     */
    public function deleteAllRoles()
    {
        $roleModel = $this->models['Role'];
        return $roleModel::truncate();
    }

    /**
     * @see parent description
     */
    public function getUserPermission($userId, $permissionId)
    {
        $userPermissionModel = $this->models['UserPermission'];
        if ($userId === null) {
            // if user is not specified then return all user permissions with specific permission_id
            $permissions = $userPermissionModel::where('permission_id', '=', $permissionId)->get()->toArray();
            foreach ($permissions as &$permission) {
                $permission = $this->parseUserOrRolePermission($permission);
            }

            return $permissions;
        } else {
            $permission = $userPermissionModel::where('user_id', '=', $userId)
                                ->where('permission_id', '=', $permissionId)
                                ->first();

            if ($permission) {
                return $this->parseUserOrRolePermission($permission->toArray());
            }
        }

        return null;
    }


    /**
     * @see parent description
     */
    public function getRolePermission($roleId, $permissionId)
    {
        $rolePermissionModel = $this->models['RolePermission'];
        if ($roleId === null) {
            // if role is not specified then return all role permissions with specific permission_id
            $permissions = $rolePermissionModel::where('permission_id', '=', $permissionId)->get()->toArray();
            foreach ($permissions as &$permission) {
                $permission = $this->parseUserOrRolePermission($permission);
            }

            return $permissions;
        } else {
            $permission = $rolePermissionModel::where('role_id', '=', $roleId)
                                ->where('permission_id', '=', $permissionId)
                                ->first();

            if ($permission) {
                return $this->parseUserOrRolePermission($permission->toArray());
            }
        }

        return null;
    }
    
    /**
     * @see parent description
     */
    public function getUserRoles($userId)
    {
        $userRoleModel = $this->models['UserRole'];
        return $userRoleModel::where('user_id', $userId)->lists('role_id');
    }

}
