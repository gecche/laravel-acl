<?php

namespace Gecche\Acl;

/**
 * Default Eloquent permission provider.
 */
class EloquentPermissionProvider implements \Gecche\Acl\Contracts\PermissionContract
{

    public $config;
    public $models;
    protected $idsSeparator;
    public $allPermissions;

    function __construct($config)
    {
        $this->config;
        $this->models = array_get($config,'models',[]);
        $this->idsSeparator = array_get($config,'ids_separator',',');
    }


    /**
     * @see parent description
     */
    public function getUserPermissions($userId)
    {
        $userPermissionModel = array_get($this->models,'UserPermission');
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
        $rolePermissionModel = array_get($this->models,'RolePermission');
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
        $userRoleModel = array_get($this->models,'UserRole');
        $rolePermissionModel = array_get($this->models,'RolePermission');
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

        $permissionModel = array_get($this->models,'Permission');
        $permissions = $permissionModel::all()->toArray();

        foreach ($permissions as &$permission) {
            $permission['resource_id_required'] = (bool) $permission['resource_id_required'];
        }

        $this->allPermissions = $permissions;
        return $permissions;
    }


    /**
     * @see parent description
     */
    public function getUserPermission($userId, $permissionId)
    {
        $userPermissionModel = array_get($this->models,'UserPermission');
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
        $rolePermissionModel = array_get($this->models,'RolePermission');
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
        $userRoleModel = array_get($this->models,'UserRole');
        return $userRoleModel::where('user_id', $userId)->lists('role_id');
    }

}
