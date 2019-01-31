<?php

namespace Gecche\Acl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for Acl class.
 */
class Acl extends Facade
{

    protected static function getFacadeAccessor() { return 'acl'; }

}
