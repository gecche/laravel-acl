<?php namespace Gecche\Seeders;

use App\Models\User;
use Cupparis\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class EloquentSeeder
{

    protected $configValues = [];

    protected $configFilePath = 'config/permissions.php';

    protected $aclModels = [];

    protected $files = null;

    protected $stub = 'stubs/config/permissions.stub';

    /**
     * Permissions constructor.
     * @param string $configFilePath
     * @param array $aclModels
     * @param array $$this->configValues
     */
    public function __construct($configValues = [], $files = null)
    {
        $this->configValues = $configValues;

        $this->aclModels = Config::get('acl.models');

        if (is_null($files))
            $this->files = new Filesystem();
        else
            $this->files = $files;
    }

    protected function getStub()
    {
        return base_path($this->stub);
        // TODO: Implement getStub() method.
    }

    public function savePermissions()
    {

        $this->aclModels = Config::get('acl.models');

        $stub = $this->files->get($this->getStub());


        foreach ($this->configValues as $key => $value) {

            $stub = str_replace(
                '{{$' . $key . '}}', var_export($value, true), $stub
            );

        }

        $this->seed();


        $this->files->put($this->getConfigFile(), $stub);


    }

    protected function getConfigFile()
    {

        return base_path($this->configFilePath);
    }


    protected function seed()
    {


        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($this->aclModels as $aclModelKey => $acl_model) {
            if (in_array($aclModelKey,array('UserRole','UserPermission')))
                continue;
            $acl_model::truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->seedRoles();
        $this->seedPermissions();
//
        $this->seedRolesPermissions();
        $this->seedGuestPermissions();


//
//        foreach ($this->$this->configValues as $key => $value) {
//            $methodName = 'seed'.studly_case($key);
//
//            if (method_exists($this,$methodName)) {
//                $this->$methodName($value);
//            }
//        }

    }

    public function seedPermissions()
    {

        $models = array_get($this->configValues, 'models', []);
        $models_permissions_prefixes = array_get($this->configValues, 'models_permissions_prefixes', []);
        $extra_permissions = array_get($this->configValues, 'extra_permissions', []);

        $acl_model_permission = array_get($this->aclModels, 'Permission', null);

        if (!$acl_model_permission) {
            throw new \Exception('Acl model permission undefined');
        }

        foreach ($models as $model) {
            $modelCamel = studly_case($model);
            $modelUpper = strtoupper(snake_case($model));

            foreach ($models_permissions_prefixes as $prefixKey => $prefixValue) {

                $routes = array_get($prefixValue, 'routes', []);

                $routes = array_map(function ($val) use ($model) {
                    return str_replace('<MODEL>', $model, $val);
                }, $routes);

                $routes = json_encode($routes);

                $acl_model_permissionObject =  $acl_model_permission::find($prefixKey . '_' . $modelUpper);
                if ($acl_model_permissionObject && $acl_model_permissionObject->getKey()) {
                    continue;
                }

                $acl_model_permission::create(array(
                    'id' => $prefixKey . '_' . $modelUpper,
                    'name' => $prefixKey . ' ' . $modelCamel,
                    'route' => $routes,
                    'resource_id_required' => array_get($prefixValue, 'resource_id_required', false),
                ));

            }

        }

        foreach ($extra_permissions as $permissionKey => $permissionValue) {

            $routes = array_get($permissionValue, 'routes', []);

            $routes = json_encode($routes);

            $acl_model_permission::create(array(
                'id' => $permissionKey,
                'name' => $permissionKey,
                'route' => $routes,
                'resource_id_required' => array_get($permissionValue, 'resource_id_required', false),
            ));


        }

    }

    protected function seedRoles()
    {

        $roles = array_get($this->configValues, 'roles', []);

        $acl_model_role = array_get($this->aclModels, 'Role', null);

        if (!$acl_model_role) {
            throw new \Exception('Acl model role undefined');
        }

        foreach ($roles as $roleKey => $roleValue) {

            $acl_model_role::create(array(
                'id' => $roleKey,
                'name' => $roleValue,
            ));

        }

    }


    protected function seedRolesPermissions()
    {

        $roles_models_permissions = array_get($this->configValues, 'roles_models_permissions', []);
        $roles_extra_permissions = array_get($this->configValues, 'roles_extra_permissions', []);

        $roles = array_get($this->configValues, 'roles', []);

        $acl_model_role_permission = array_get($this->aclModels, 'RolePermission', null);

        if (!$acl_model_role_permission) {
            throw new \Exception('Acl model role permission undefined');
        }


        foreach ($roles as $roleKey => $roleValue) {
            $rolePermissions = array_get($roles_models_permissions, $roleKey, []);
            foreach ($rolePermissions as $m => $rolePermissionsModel) {

                foreach ($rolePermissionsModel as $prefixKey => $ids) {
                    if (is_string($ids)) {
                        $allowed = false;
                    } else {
                        $allowed = true;
                        $ids = null;
                    }
                    $acl_model_role_permission::create(array(
                        'role_id' => $roleKey,
                        'permission_id' => $prefixKey . '_' . strtoupper($m),
                        'allowed' => $allowed,
                        'ids' => $ids,
                    ));
                }
            }
        }


        foreach ($roles as $roleKey => $roleValue) {
            $roleExtraPermissions = array_get($roles_extra_permissions,$roleKey,[]);
            foreach ($roleExtraPermissions as $permissionName => $ids) {

                if (is_string($ids)) {
                    $allowed = false;
                } else {
                    $allowed = true;
                    $ids = null;
                }
                $acl_model_role_permission::create(array(
                    'role_id' => $roleKey,
                    'permission_id' => $permissionName,
                    'allowed' => $allowed,
                    'ids' => $ids,
                ));
            }
        }

    }

    protected function seedGuestPermissions()
    {

        $guest_models_permissions = array_get($this->configValues, 'guest_models_permissions', []);
        $guest_extra_permissions = array_get($this->configValues, 'guest_extra_permissions', []);

        $acl_model_user_permission = array_get($this->aclModels, 'UserPermission', null);

        if (!$acl_model_user_permission) {
            throw new \Exception('Acl model user permission undefined');
        }

        $userId = Config::get('acl.guestuser', null);
        if (is_null($userId)) {
            throw new \Exception('Guest user undefined');
        }


        foreach ($guest_models_permissions as $m => $guestPermissionsModel) {

            foreach ($guestPermissionsModel as $prefixKey => $ids) {
                if (is_array($ids)) {
                    $allowed = false;
                } else {
                    $allowed = true;
                    $ids = null;
                }
                $acl_model_user_permission::create(array(
                    'user_id' => $userId,
                    'permission_id' => $prefixKey . '_' . strtoupper($m),
                    'allowed' => $allowed,
                    'ids' => $ids,
                ));
            }
        }

        foreach ($guest_extra_permissions as $permissionName => $ids) {
            if (is_array($ids)) {
                $allowed = false;
            } else {
                $allowed = true;
                $ids = null;
            }
            $acl_model_user_permission::create(array(
                'role_id' => $userId,
                'permission_id' => $permissionName,
                'allowed' => $allowed,
                'ids' => $ids,
            ));
        }
    }

}
