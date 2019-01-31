<?php

namespace Gecche\Acl\Contracts;

/**
 * Abstract class for getting permissions.
 *
 * Acl\Checker works with this class so it can retrieve
 * global and users permissions.
 */
interface PermissionContract
{

    /**
     * Needs to return array of user permissions with following structure:
     *
     * array(
     *  array(
     *      'id' => 'PERMISSION_ID',
     *      'allowed' => null|true|false,
     *      'allowed_ids' => null|2|array(1,2,3),
     *      'excluded_ids' => null|2|array(1,2,3)
     *  ),...
     * )
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getUserPermissions($userId);

    /**
     * Gets all permissions a role has.
     * Needs to return array of role permissions with following structure:
     *
     * array(
     *  array(
     *      'id' => 'PERMISSION_ID',
     *      'allowed' => null|true|false,
     *      'allowed_ids' => null|2|array(1,2,3),
     *      'excluded_ids' => null|2|array(1,2,3)
     *  ),...
     * )
     *
     * @param string $roleId
     *
     * @return array
     */
    public function getRolePermissions($roleId);

    /**
     * Gets all permissions user has based on assigned roles
     * Needs to return array of role permissions with following structure:
     *
     * array(
     *  array(
     *      'id' => 'PERMISSION_ID',
     *      'allowed' => null|true|false,
     *      'allowed_ids' => null|2|array(1,2,3),
     *      'excluded_ids' => null|2|array(1,2,3)
     *  ),...
     * )
     *
     * @param string $userId
     *
     * @return array
     */
    public function getUserPermissionsBasedOnRoles($userId);

    /**
     * Needs to return array of all system permissions with following structure:
     *
     * array(
     *  array(
     *      'id' => 'PERMISSION_ID',
     *      'allowed' => true|false,
     *      'route' => 'GET:/resource$'|array('GET:/resource$','POST:/resource$'),
     *      'resource_id_required' => true|false
     *  ),...
     * )
     *
     * @return array
     */
    public function getAllPermissions();

    /**
     * Delete all system wide permissions
     */
    public function deleteAllPermissions();

    /**
     * Delete all user permissions
     */
    public function deleteAllUsersPermissions();

    /**
     * Delete all role permissions
     */
    public function deleteAllRolesPermissions();

    /**
     * Crate new system permission
     *
     * @param string $id
     * @param bool $allowed
     * @param string|array $route
     * @param bool $resourceIdRequired
     * @param string $name
     *
     * @return array
     */
    public function createPermission($id, $allowed, $route, $resourceIdRequired, $name);

    /**
     * Remove permission by ID
     *
     * @param string $id
     */
    public function removePermission($id);

    /**
     * Assign permission to the user with specfic options
     *
     * @param integer $userId
     * @param string $permissionId
     * @param boolean $allowed
     * @param array $allowedIds
     * @param array $excludedIds
     */
    public function assignUserPermission(
        $userId, $permissionId, $allowed = null, array $ids = null
    );

    /**
     * Assign permission to the role with specfic options
     *
     * @param integer $roleId
     * @param string $permissionId
     * @param boolean $allowed
     * @param array $allowedIds
     * @param array $excludedIds
     */
    public function assignRolePermission(
        $roleId, $permissionId, $allowed = null, array $ids = null
    );

    /**
     * Remove specific user permission.
     * If $userId can be null.
     *
     * @param integer $userId
     * @param string $permissionId
     */
    public function removeUserPermission($userId, $permissionId);

    /**
     * Remove all user's permissions.
     *
     * @param integer $userId
     */
    public function removeUserPermissions($userId);

     /**
     * Insert new role
     *
     * @param string $id
     * @param string $name
     * @param array|string $permissionIds
     * @param type $parentId
     *
     * @return type
     */
    public function insertRole($id, $name, $parentId = null);

     /**
     * Delete all roles.
     */
    public function deleteAllRoles();

    /**
     * Get specific user permission
     *
     * @param integer $userId
     * @param string $permissionId
     *
     * @return array
     */
    public function getUserPermission($userId, $permissionId);


    /**
     * Get specific role permission
     *
     * @param integer $roleId
     * @param string $permissionId
     *
     * @return array
     */
    public function getRolePermission($roleId, $permissionId);
    
    /**
     * Get user roles
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getUserRoles($userId);

}
