<?php

namespace Cupparis\Acl\Models;

use Cupparis\Ardent\Ardent;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_users_permissions table.
 * This is used by Eloquent permissions provider.
 */
class UserPermission extends Ardent
{
    protected $table = 'acl_users_permissions';

    protected $fillable = array('permission_id', 'user_id', 'allowed', 'ids');

    public $timestamps = false;


}
