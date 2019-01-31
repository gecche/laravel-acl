<?php

namespace Gecche\Acl\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_groups table.
 * This is used by Eloquent permissions provider.
 */
class Role extends Model
{
    protected $table = 'acl_roles';

    protected $guarded = array();

    public $timestamps = false;

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'acl_roles_permissions', 'user_id', 'role_id');
    }

}
