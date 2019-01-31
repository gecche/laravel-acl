<?php

namespace Cupparis\Acl;

use Cupparis\Acl\Contracts\AclContract;
use Cupparis\Acl\Contracts\PermissionContract;
use Illuminate\Contracts\Auth\Guard;
use Closure;
use Illuminate\Support\Facades\Session;

/**
 * Main ACL class for checking does user have some permissions.
 */
class AclGuard implements AclContract
{

    protected $provider;
    protected $auth;

    protected $superusers;
    protected $guestuser;
    protected $loginrole;

    protected $checkers_namespaces;
    protected $builders_namespaces;

    protected $cache = null;

    private $cached = array();
    //ORM Extension


    public function __construct(PermissionContract $provider, Guard $auth, $superusers, $guestuser, $loginrole, $checkers_namespaces, $builders_namespaces, $cache = null)
    {
        $this->provider = $provider;
        $this->auth = $auth;
        $this->superusers = $superusers;
        $this->guestuser = $guestuser;
        $this->loginrole = $loginrole;

        $this->cache = $cache;

        $this->checkers_namespaces = $checkers_namespaces;
    $this->builders_namespaces = $builders_namespaces;
    }




    /***/

    public function getCurrentUserId() {
        if ($this->auth->user()) {
            return $this->auth->id();
        } else {
            return $this->guestuser;
        }
    }

    /**
     * Detect iF current user is superuser.
     *
     * @return boolean
     */
    public function isSuperuser($userId = null)
    {
        if ($userId === null)
            $userId = $this->getCurrentUserId();

        return in_array($userId, $this->superusers);
    }

    /**
     * Detect if current user is guestuser.
     *
     * @return boolean
     */
    public function isGuestuser($userId = null)
    {
        if ($userId === null)
            $userId = $this->getCurrentUserId();

        return $userId === $this->guestuser;
    }

    /**
     * Return array of superusers IDs
     *
     * @return array
     */
    public function getSuperusers()
    {
        return $this->superusers;
    }

    /**
     * Return ID of guest user
     *
     * @return int
     */
    public function getGuestuser()
    {
        return $this->guestuser;
    }

    /**
     * Return ID of login role
     *
     * @return int
     */
    public function getLoginrole()
    {
        return $this->loginrole;
    }


    protected function getCacheUserPermissions($userId) {
        if ($userId != $this->getCurrentUserId()) {
            return isset($this->cached[$userId]) ? $this->cached[$userId] : false;
        }

        switch ($this->cache) {
            case 'session':
                return Session::get('acl_permissions_'.$userId,false);
            default:
                return isset($this->cached[$userId]) ? $this->cached[$userId] : false;
        }
    }

    protected function setCacheUserPermissions($userId,$permissions) {
        if ($userId != $this->getCurrentUserId()) {
            return $this->cached[$userId] = $permissions;
        }

        switch ($this->cache) {
            case 'session':
                return Session::put('acl_permissions_'.$userId,$permissions);
            default:
                return $this->cached[$userId] = $permissions;
        }
    }
    /**
     * Get user permissions (together with system permissions)
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getUserPermissions($userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

        $userPermissions = $this->getCacheUserPermissions($userId);
        if ($userPermissions === false) {

            // get user permissions
            $userPermissions = $this->provider->getUserPermissions($userId);

            // get user permissions from user roles
            $userPermissionsBasedOnRoles = $this->provider->getUserPermissionsBasedOnRoles($userId);

            $permissions = array();

            // get all permissions
            foreach ($this->provider->getAllPermissions() as $permission) {
                $permission['ids'] = array();
                $permission['allowed'] = false;
                unset($permission['name']);

                $permissions[$permission['id']] = $permission;
            }

            //Aggiungo i permessi assegnati al ruolo login (tutti gli utenti autenticati)
            if (!$this->isGuestuser($userId)) {
                $loginRolePermissions = $this->provider->getRolePermissions($this->getLoginrole());
                foreach ($loginRolePermissions as $loginRolePermission) {
                    if (@$loginRolePermission['id'] === null) {
                        continue;
                    }

                    $permissions[$loginRolePermission['id']] =
                        array_merge($permissions[$loginRolePermission['id']], $loginRolePermission);
                }
            }

            // overwrite system permissions with user permissions from roles
            foreach ($userPermissionsBasedOnRoles as $userRolePermission) {
                if (@$userRolePermission['id'] === null) {
                    continue;
                }

                $permissions[$userRolePermission['id']] =
                    array_merge($permissions[$userRolePermission['id']], $userRolePermission);

            }

            // overwrite system permissions and user permissions from roles with user permissions
            foreach ($userPermissions as $userPermission) {

                $permissions[$userPermission['id']] =
                    array_merge($permissions[$userPermission['id']], $userPermission);
            }

            // set finall permissions for particular user
            $this->setCacheUserPermissions($userId,$permissions);
            $userPermissions = $permissions;
        }

        return $userPermissions;
    }
    /**
     * Get current user roles (linear structure)
     *
     * @return array
     */
    public function getUserRoles($userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

       return $this->provider->getUserRoles($userId);
    }


    /**
     * Check if user has permission.
     *
     * @return boolean
     */
    public function check($permissionId,$resourceId = null,$userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }


        if ($this->isSuperuser()) {
            return true;
        }

        $userPermissions = $this->getUserPermissions($userId);


        $userPermission = @$userPermissions[$permissionId];

        // check if permission exist in list of all permissions
        if ($userPermission == null) {
            $this->throwError('Permission "' . $permissionId . '" does not exist.');
        }

        // is resource ID provided for permissions that expect resource ID
        if ($userPermission['resource_id_required'] && empty($resourceId)) {
            $this->throwError('You must specify resource id for permission "' . $permissionId . '".');
        }

        $checkMethodName = 'checkPermission'.studly_case(strtolower($permissionId));
        $allowed = $this->$checkMethodName($resourceId,$userPermission,$userId);


        return $allowed;
    }

    /**
     * Clean up then throw and exception.
     *
     * @param string $message
     */
    private function throwError($message)
    {
        throw new \InvalidArgumentException($message);
    }


    /*
     * METODI PER I FILTRI
     */

    /**
     * Get resource ids that user can (or not) access.
     * Multiple permissions: and logic (intersection)
     *
     * @return array
     */
    public function getResourceIds($permissionId,$userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

        if ($this->isSuperuser($userId)) {
            return array(
                'allowed' => true,
                'ids' => array(),
            );
        }

        $ids = array(
            'allowed' => false,
            'ids' => array(),
        );

        $userPermissions = $this->getUserPermissions($userId);

        $permission = @$userPermissions[$permissionId];

        if ($permission === null) {
            $permission = array(
                'allowed' => false,
                'ids' => array(),
            );
        } else {
            $permission = array_merge($ids,$permission);
        }

        return $permission;
    }
    /**
     * Append to the query additional where statements if needed.
     *
     * @param \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Query\Builder $query
     * @param string $primaryKey
     *
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Query\Builder
     */
    public function query($query, $permissionId, $primaryKey = 'id', $userId = null, $params = array())
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }


        if ($this->isSuperuser()) {
            return $query;
        }

        $userPermissions = $this->getUserPermissions($userId);


        $userPermission = @$userPermissions[$permissionId];

        // check if permission exist in list of all permissions
        if ($userPermission == null) {
            $this->throwError('Permission "' . $permissionId . '" does not exist.');
        }

        $buildMethodName = 'buildQuery'.studly_case(strtolower($permissionId));
        $query = $this->$buildMethodName($query,$userPermission,$userId,$primaryKey,$params);

        return $query;

    }





    /*
     *
     * PROVIDER METHODS REPARAMETERIZED
     *
     */


    /**
     * Update user permissions (user permissions needs to exist).
     *
     * @param integer $userId
     * @param array $permissions
     */
    public function updateUserPermissions($userId, array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->updateUserPermission(
                $userId, $permission['id'], @$permission['allowed'], @$permission['ids']
            );
        }
    }

    /**
     * Update role permissions (role permissions needs to exist).
     *
     * @param integer $roleId
     * @param array $permissions
     */
    public function updateRolePermissions($roleId, array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->updateRolePermission(
                $roleId, $permission['id'], @$permission['allowed'], @$permission['ids']
            );
        }
    }


    /**
     * Get specific user permission
     *
     * @param integer $userId
     * @param string $permissionId
     *
     * @return array
     */
    public function getUserPermission($permissionId,$userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

        return $this->provider->getUserPermission($userId, $permissionId);
    }

    /**
     * Set user permission. If permission exist update, otherwise create.
     *
     * @param integer $userId
     * @param string $permissionId
     * @param boolean $allowed
     * @param array $allowedIds
     * @param array $excludedIds
     */
    public function setUserPermission(
        $userId,
        $permissionId,
        $allowed = null,
        array $allowedIds = null
    ) {
        $permission = $this->getUserPermission($userId, $permissionId);
        if (empty($permission)) {
            return $this->provider->assignUserPermission($userId, $permissionId, $allowed, $allowedIds);
        } else {
            return $this->provider->updateUserPermission($userId, $permissionId, $allowed, $allowedIds);
        }
    }

    /**
     * Set role permission. If permission exist update, otherwise create.
     *
     * @param integer $roleId
     * @param string $permissionId
     * @param boolean $allowed
     * @param array $allowedIds
     * @param array $excludedIds
     */
    public function setRolePermission(
        $roleId,
        $permissionId,
        $allowed = null,
        array $allowedIds = null
    ) {
        $permission = $this->getRolePermission($roleId, $permissionId);
        if (empty($permission)) {
            return $this->provider->assignRolePermission($roleId, $permissionId, $allowed, $allowedIds);
        } else {
            return $this->provider->updateRolePermission($roleId, $permissionId, $allowed, $allowedIds);
        }
    }



    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (starts_with($method,'checkPermission')) {

            $permission = substr($method,15);
            foreach ($this->checkers_namespaces as $checker_namespace) {
                $checkerClassName = $checker_namespace . "\\Checker". $permission;
                if (class_exists($checkerClassName)) {
                    $checker = new $checkerClassName($this);
                    return call_user_func_array(array($checker, 'check'), $parameters);
                }
            }

            foreach ($this->checkers_namespaces as $checker_namespace) {
                $checkerClassName = $checker_namespace . "\\Checker";
                if (class_exists($checkerClassName)) {
                    $checker = new $checkerClassName($this);
                    return call_user_func_array(array($checker, 'check'), $parameters);
                }
            }
        }

        if (starts_with($method,'buildQuery')) {

            $permission = substr($method,10);
            foreach ($this->builders_namespaces as $builder_namespace) {
                $builderClassName = $builder_namespace . "\\Builder". $permission;
                if (class_exists($builderClassName)) {
                    $builder = new $builderClassName($this);
                    return call_user_func_array(array($builder, 'query'), $parameters);
                }
            }

            foreach ($this->builders_namespaces as $builder_namespace) {
                $builderClassName = $builder_namespace . "\\Builder";
                if (class_exists($builderClassName)) {
                    $builder = new $builderClassName($this);
                    return call_user_func_array(array($builder, 'query'), $parameters);
                }
            }
        }

        return call_user_func_array(array($this->provider, $method), $parameters);
    }




}
