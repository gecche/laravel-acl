<?php

namespace Gecche\Acl;

use Gecche\Acl\Contracts\AclContract;
use Gecche\Acl\Contracts\CachePermissionsContract;
use Gecche\Acl\Contracts\PermissionContract;
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

    protected $superUsers;
    protected $guestUser;
    protected $loginRole;

    protected $checkersNamespaces;
    protected $buildersNamespaces;

    protected $cache = null;
    protected $cached = array();

    //ORM Extension


    public function __construct(PermissionContract $provider, Guard $auth, CachePermissionsContract $cache)
    {

        $this->provider = $provider;
        $this->auth = $auth;
        $this->cache = $cache;

        $config = config('acl',[]);

        $this->superUsers = array_get($config,'superusers',[]);
        $this->guestUser = array_get($config,'guestuser',0);
        $this->loginRole = array_get($config,'guestuser','LOGIN');

        $this->checkersNamespaces = array_get($config,'checkers_namespaces',[]);
        $this->buildersNamespaces = array_get($config,'builders_namespaces',[]);
    }

    /**
     * @return PermissionContract
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return Guard
     */
    public function getAuth()
    {
        return $this->auth;
    }


    /**
     * Detect iF current user is superuser.
     *
     * @return boolean
     */
    public function isSuperUser($userId = null)
    {
        if ($userId === null)
            $userId = $this->getCurrentUserId();

        return in_array($userId, $this->superUsers);
    }

    /**
     * Detect if current user is guestuser.
     *
     * @return boolean
     */
    public function isGuestUser($userId = null)
    {
        if ($userId === null)
            $userId = $this->getCurrentUserId();

        return $userId === $this->guestUser;
    }

    /**
     * Return array of superusers IDs
     *
     * @return array
     */
    public function getSuperUsers()
    {
        return $this->superUsers;
    }

    /**
     * Return ID of guest user
     *
     * @return int
     */
    public function getGuestUser()
    {
        return $this->guestUser;
    }

    /**
     * Return ID of login role
     *
     * @return int
     */
    public function getLoginRole()
    {
        return $this->loginRole;
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

        $userPermissions = $this->cache->getCacheUserPermissions($userId);
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
            if (!$this->isGuestUser($userId)) {
                $loginRolePermissions = $this->provider->getRolePermissions($this->getLoginRole());
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
            $this->cache->setCacheUserPermissions($userId, $permissions);
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
     * Get resource ids that user can (or not) access.
     * Multiple permissions: and logic (intersection)
     *
     * @return array
     */
    public function getResourceIds($permissionId, $userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

        if ($this->isSuperUser($userId)) {
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
            $permission = array_merge($ids, $permission);
        }

        return $permission;
    }

    /**
     * Check if user has permission.
     *
     * @return boolean
     */
    public function check($permissionId, $resourceId = null, $userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }


        if ($this->isSuperUser()) {
            return true;
        }

        $userPermissions = $this->getUserPermissions($userId);


        $userPermission = @$userPermissions[$permissionId];

        // check if permission exist in list of all permissions
        if ($userPermission == null) {
            throw new \InvalidArgumentException('Permission "' . $permissionId . '" does not exist.');
        }

        // is resource ID provided for permissions that expect resource ID
        if ($userPermission['resource_id_required'] && empty($resourceId)) {
            throw new \InvalidArgumentException('You must specify resource id for permission "' . $permissionId . '".');
        }

        $checkMethodName = 'checkPermission' . studly_case(strtolower($permissionId));
        $allowed = $this->$checkMethodName($resourceId, $userPermission, $userId);


        return $allowed;
    }

    /*
     * METODI PER I FILTRI
     */

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


        if ($this->isSuperUser()) {
            return $query;
        }

        $userPermissions = $this->getUserPermissions($userId);


        $userPermission = @$userPermissions[$permissionId];

        // check if permission exist in list of all permissions
        if ($userPermission == null) {
            throw new \InvalidArgumentException('Permission "' . $permissionId . '" does not exist.');
        }

        $buildMethodName = 'buildQuery' . studly_case(strtolower($permissionId));
        $query = $this->$buildMethodName($query, $userPermission, $userId, $primaryKey, $params);

        return $query;

    }




    public function getCurrentUserId()
    {
        if ($this->auth->user()) {
            return $this->auth->id();
        } else {
            return $this->guestUser;
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
    public function getUserPermission($permissionId, $userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }

        return $this->provider->getUserPermission($userId, $permissionId);
    }



    /**
     * Dynamically call the default driver instance.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (starts_with($method, 'checkPermission')) {

            $permission = substr($method, 15);
            foreach ($this->checkersNamespaces as $checker_namespace) {
                $checkerClassName = $checker_namespace . "\\Checker" . $permission;
                if (class_exists($checkerClassName)) {
                    $checker = new $checkerClassName($this);
                    return call_user_func_array(array($checker, 'check'), $parameters);
                }
            }

            foreach ($this->checkersNamespaces as $checker_namespace) {
                $checkerClassName = $checker_namespace . "\\Checker";
                if (class_exists($checkerClassName)) {
                    $checker = new $checkerClassName($this);
                    return call_user_func_array(array($checker, 'check'), $parameters);
                }
            }
        }

        if (starts_with($method, 'buildQuery')) {

            $permission = substr($method, 10);
            foreach ($this->buildersNamespaces as $builder_namespace) {
                $builderClassName = $builder_namespace . "\\Builder" . $permission;
                if (class_exists($builderClassName)) {
                    $builder = new $builderClassName($this);
                    return call_user_func_array(array($builder, 'query'), $parameters);
                }
            }

            foreach ($this->buildersNamespaces as $builder_namespace) {
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
