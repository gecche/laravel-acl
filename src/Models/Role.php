<?php

namespace Gecche\Acl\Models;

use Gecche\Ardent\Ardent;
use Illuminate\Support\Facades\Config;

/**
 * Eloquent model for acl_groups table.
 * This is used by Eloquent permissions provider.
 */
class Role extends Ardent
{
    protected $table = 'acl_roles';

    protected $guarded = array();

    public $timestamps = false;

    public static $relationsData = array(
        //'address' => array(self::HAS_ONE, 'Address'),
        //'orders'  => array(self::HAS_MANY, 'Order'),
        'permissions' => array(self::BELONGS_TO_MANY, 'App\Models\Permission', 'table' => 'acl_roles_permissions'),
        //'fotos' => array(self::BELONGS_TO_MANY, 'App\Models\Foto', 'table' => 'users_fotos','pivotKeys' => array("ordine")),
        //'roles' => array(self::BELONGS_TO_MANY, 'App\Models\Role', 'table' => 'acl_users_roles'),
    );

    public static function getForSelectList($columns = null, $separator = null, $params = array())
    {
        if (array_get($params,'filters',false) !== []) {
            $params['filters'] = array(array(
                    'field' => 'id',
                    'operator' => '!=',
                    'value' => 'LOGIN',
            ));
        }
        return parent::getForSelectList($columns, $separator, $params);
    }


}
