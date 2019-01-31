<?php

namespace Cupparis\Acl\Models;

use Cupparis\Ardent\Ardent;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_groups table.
 * This is used by Eloquent permissions provider.
 */
class UserRole extends Ardent
{
    protected $table = 'acl_users_roles';

    protected $fillable = array('user_id', 'role_id');

    public $timestamps = false;

}
