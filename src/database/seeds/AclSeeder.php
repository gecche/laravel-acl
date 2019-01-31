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
                'description' => 'Access users of those roles',
                'allowed' => false,
                'resource_id_required' => true,
        ));

        foreach ($models as $model) {
            $modelCamel = studly_case($model);
            $modelUpper = strtoupper($model);

            $acl_model_permission::create(array(
                        'id' => 'CREATE_' . $modelUpper,
                        'description' => 'Create ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'EDIT_' . $modelUpper,
                        'description' => 'Edit ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => true,
            ));
            $acl_model_permission::create(array(
                        'id' => 'DELETE_' . $modelUpper,
                        'description' => 'Delete ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'VIEW_' . $modelUpper,
                        'description' => 'View ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => true,
            ));
            $acl_model_permission::create(array(
                        'id' => 'LIST_' . $modelUpper,
                        'description' => 'View ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => false,
            ));
            $acl_model_permission::create(array(
                        'id' => 'ARCHIVIO_' . $modelUpper,
                        'description' => 'Archive View ' . $modelCamel,
                        'allowed' => false,
                        'resource_id_required' => false,
            ));
        }


        //CREATE ROLES

        $acl_model_role = $acl_models['Role'];

        $acl_model_role::create(array(
            'id' => 'ADMIN',
            'description' => 'Admin',
        ));

        $acl_model_role::create(array(
            'id' => 'OPERATORE',
            'description' => 'Operatore',
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
            'ids' => 'OPERATORE',
        ));


        //ADMIN PERMISSIONS
        $permissions = ['LIST','CREATE','ARCHIVIO','VIEW','EDIT','DELETE'];
        foreach (['log','user','news','pagina','tag'] as $m) {
            foreach ($permissions as $permission) {
                    $acl_model_role_permission::create(array(
                        'role_id' => 'ADMIN',
                        'permission_id' => $permission . '_' . strtoupper($m),
                        'allowed' => true,
                        'ids' => null,
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
                        'ids' => null,
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
                    'ids' => null,
                ));
            }
        }

    }

}




