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
     *      'ids' => null|2|array(1,2,3),
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
     *      'ids' => null|2|array(1,2,3),
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
