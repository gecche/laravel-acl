<?php

namespace Gecche\Acl\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_permissions table.
 * This is used by Eloquent permissions provider.
 */
class Permission extends Model
{
    protected $table = 'acl_permissions';

    protected $guarded = array();

    public $timestamps = false;

}
