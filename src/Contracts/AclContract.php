<?php

namespace Gecche\Acl\Contracts;

/**
 * Contact for acl guard
 *
 */
interface AclContract
{


    /**
     * Detect iF current user is superuser.
     *
     * @return boolean
     */
    public function isSuperuser($userId = null);

    /**
     * Detect if current user is guestuser.
     *
     * @return boolean
     */
    public function isGuestuser($userId = null);


    /**
     * Return array of superusers IDs
     *
     * @return array
     */
    public function getSuperusers();

    /**
     * Return ID of guest user
     *
     * @return int
     */
    public function getGuestuser();

    /**
     * Return ID of login role
     *
     * @return int
     */
    public function getLoginrole();

    /**
     * Get user permissions (together with system permissions)
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getUserPermissions($userId = null);

    /**
     * Get current user roles (linear structure)
     *
     * @return array
     */
    public function getUserRoles($userId = null);

    /**
     * Get resource ids that user can (or not) access.
     * Multiple permissions: and logic (intersection)
     *
     * @return array
     */
    public function getResourceIds($permissionId, $userId = null);

    /**
     * Check if user has permission.
     *
     * @return boolean
     */
    public function check($permissionId, $resourceId = null, $userId = null);

    /**
     * Append to the query additional where statements if needed.
     *
     * @param \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Query\Builder $query
     * @param string $primaryKey
     *
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Query\Builder
     */
    public function query($query, $permissionId, $primaryKey = 'id', $userId = null, $params = array());
}
