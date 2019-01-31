<?php

namespace Gecche\Acl\Models;

use Gecche\Ardent\Ardent;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_groups table.
 * This is used by Eloquent permissions provider.
 */
class RolePermission extends Ardent
{
    protected $table = 'acl_roles_permissions';

    //protected $fillable = array('permission_id', 'role_id', 'allowed', 'ids');

    public $timestamps = false;

}
