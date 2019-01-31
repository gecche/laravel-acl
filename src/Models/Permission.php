<?php

namespace Cupparis\Acl\Models;

use Cupparis\Ardent\Ardent;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for acl_permissions table.
 * This is used by Eloquent permissions provider.
 */
class Permission extends Ardent
{
    protected $table = 'acl_permissions';

    protected $guarded = array();

    public $timestamps = false;

}
