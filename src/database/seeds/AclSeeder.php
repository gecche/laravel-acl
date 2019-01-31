<?php

use \Illuminate\Support\Facades\Config;








class AclSeeder extends Seeder {

    public function run() {

        $models = array(
            'user',
            'role',
            'log',
            'menu',
            'menu_item',
            'news',
            'pagina',
            'tag',
        );

        $acl_models = Config::get('acl.models');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach($acl_models as $acl_model) {
            $acl_model::truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');


        //CREATE PERMISSIONS

        $acl_model_permission = $acl_models['Permission'];

        $acl_model_permission::create(array(
                'id' => 'ACCESS_ROLE',
                'name' => 'Access users of those roles',
                'allowed' => false,
                'route' => array(),
                'resource_id_required' => true,
        ));

        foreach ($models as $model) {
            $modelCamel = studly_case($model);
            $modelUpper = strtoupper($model);

            $acl_model_permission::create(array(
                        'id' => 'CREATE_' . $modelUpper,
                        'name' => 'Create ' . $modelCamel,
                        'allowed' => false,
                        'route' => array('GET:/' . $model . '/create', 'POST:/' . $model),
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'EDIT_' . $modelUpper,
                        'name' => 'Edit ' . $modelCamel,
                        'allowed' => false,
                        'route' => array(
                            'GET:/' . $model . '/(\d+)/edit',
                            'PUT:/' . $model . '/(\d+)',
                        ),
                        'resource_id_required' => true,
            ));
            $acl_model_permission::create(array(
                        'id' => 'DELETE_' . $modelUpper,
                        'name' => 'Delete ' . $modelCamel,
                        'allowed' => false,
                        'route' => array(
                            'DELETE:/' . $model . '/(\d+)',
                            'POST:/' . $model . '/deleteall'
                        ),
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'VIEW_' . $modelUpper,
                        'name' => 'View ' . $modelCamel,
                        'allowed' => false,
                        'route' => array('GET:/' . $model . '/(\d+)'),
                        'resource_id_required' => true,
            ));
            $acl_model_permission::create(array(
                        'id' => 'LIST_' . $modelUpper,
                        'name' => 'View ' . $modelCamel,
                        'allowed' => false,
                        'route' => array('GET:/' . $model,'GET:/tab/' . $model),
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'ARCHIVIO_' . $modelUpper,
                        'name' => 'Archive View ' . $modelCamel,
                        'allowed' => false,
                        'route' => array('GET:/archivio/' . $model),
                        'resource_id_required' => false,
            ));
        }


        //CREATE ROLES

        $acl_model_role = $acl_models['Role'];

        $acl_model_role::create(array(
            'id' => 'ADMIN',
            'name' => 'Admin',
        ));

        $acl_model_role::create(array(
            'id' => 'OPERATORE',
            'name' => 'Operatore',
        ));


        //CREATE USER ROLES

        $acl_model_user_role = $acl_models['UserRole'];

        $acl_model_user_role::create([
            'user_id' => 3,
            'role_id' => 'ADMIN',
        ]);
        $acl_model_user_role::create([
            'user_id' => 4,
            'role_id' => 'OPERATORE',
        ]);

        //CREATE ROLE PERMISSIONS

        $acl_model_role_permission = $acl_models['RolePermission'];
        $acl_model_role_permission::create(array(
            'role_id' => 'ADMIN',
            'permission_id' => ACCESS_ROLE,
            'allowed' => null,
            'allowed_ids' => 'OPERATORE',
            'excluded_ids' => null,
        ));


        //ADMIN PERMISSIONS
        $permissions = ['LIST','CREATE','ARCHIVIO','VIEW','EDIT','DELETE'];
        foreach (['log','user','news','pagina','tag'] as $m) {
            foreach ($permissions as $permission) {
                    $acl_model_role_permission::create(array(
                        'role_id' => 'ADMIN',
                        'permission_id' => $permission . '_' . strtoupper($m),
                        'allowed' => true,
                        'allowed_ids' => null,
                        'excluded_ids' => null,
                    ));
            }
        }

        //OPERATORE PERMISSIONS
        $permissions = ['LIST','CREATE','ARCHIVIO','VIEW','EDIT','DELETE'];
        foreach (['user','news','pagina','tag'] as $m) {
            foreach ($permissions as $permission) {
                    $acl_model_role_permission::create(array(
                        'role_id' => 'OPERATORE',
                        'permission_id' => $permission . '_' . strtoupper($m),
                        'allowed' => true,
                        'allowed_ids' => null,
                        'excluded_ids' => null,
                    ));
            }
        }


        //GUEST PERMISSIONS
        $model_permissions = [
          'news' =>  [
              'ARCHIVIO', 'VIEW',
          ]
        ];
        $acl_model_user_permission = $acl_models['UserPermission'];
        foreach ($model_permissions as $m => $permissions) {
            foreach ($permissions as $permission) {
                $acl_model_user_permission::create(array(
                    'user_id' => 0,
                    'permission_id' => $permission . '_' . strtoupper($m),
                    'allowed' => true,
                    'allowed_ids' => null,
                    'excluded_ids' => null,
                ));
            }
        }

    }

}




