<?php namespace Gecche\Acl\Console;

use Gecche\Acl\Seeders\EloquentSeeder;
use Illuminate\Console\Command;

class EloquentPermissions extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'eloquent_permissions';

    protected $models;
    protected $roles;
    protected $modelsPermissionsPrefixes;
    protected $extraPermissions;
    protected $rolesModelsPermissions;
    protected $rolesExtraPermissions;
    protected $guestModelsPermissions;
    protected $guestExtraPermissions;

    protected $stubInitialValues = [
        'models' => [],
        'roles' => [],
        'models_permissions_prefixes' => [],
        'extra_permissions' => [],
        'roles_models_permissions' => [],
        'roles_extra_permissions' => [],
        'guest_models_permissions' => [],
        'guest_extra_permissions' => [],
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera permessi e ruoli iniziali di un applicazione';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->setInitialValues();

//        print_r($this->stubInitialValues);
        $permissionsSeeder = new EloquentSeeder($this->stubInitialValues);

        $permissionsSeeder->savePermissions();

        $this->comment('Permissions saved in database');


    }

    protected function setInitialValues()
    {
        $this->stubInitialValues['models'] = $this->setInitialModels();
        $this->stubInitialValues['roles'] = $this->setInitialRoles();
        $this->stubInitialValues['models_permissions_prefixes'] = $this->setInitialModelsPermissionPrefixes();
        $this->stubInitialValues['extra_permissions'] = $this->setInitialRolesModelsPermissions();
        $this->stubInitialValues['roles_models_permissions'] = $this->setInitialGuestModelsPermissions();
        $this->stubInitialValues['roles_extra_permissions'] = $this->setInitialExtraPermissions();
        $this->stubInitialValues['guest_models_permissions'] = $this->setInitialRolesExtraPermissions();
        $this->stubInitialValues['guest_extra_permissions'] = $this->setInitialGuestExtraPermissions();
    }

    /*
     * Modelli su cui eseguire le permissions
     */
    protected function setInitialModels()
    {
        $this->models = [
            'user',
            'role',
        ];

        return $this->models;

    }


    /*
     * Ruoli disponibili
     */
    protected function setInitialRoles()
    {

        $this->roles = [
            'LOGIN' => 'Login',
            'ADMIN' => 'Admin',
            'OPERATOR' => 'Operator',
        ];

        return $this->roles;
    }

    /*
     * Prefissi delle permissions: ogni modello avrà LIST_<MODEL>, VIEW_<MODEL> ecc...
     */

    protected function setInitialModelsPermissionPrefixes()
    {

        $this->modelsPermissionsPrefixes = [
            'CREATE' => [
                'resource_id_required' => false,
            ],
            'EDIT' => [
                'resource_id_required' => true,
            ],
            'DELETE' => [
                'resource_id_required' => true,
            ],
            'VIEW' => [
                'resource_id_required' => true,
            ],
            'LIST' => [
                'resource_id_required' => false,
            ],
        ];

        return $this->modelsPermissionsPrefixes;
    }

    /*
     * PERMESSI EXTRA AL DI FUORI DEI MODELLI
     */
    protected function setInitialExtraPermissions()
    {

        $this->extraPermissions = [
            'ACCESS_ROLE' => [
                'resource_id_required' => true,
                'description' => 'Roles with granted access'
            ],
        ];

        return $this->extraPermissions;

    }

    /*
     * PERMESSI SUI MODELLI ASSOCIATI AI RUOLI
     */
    protected function setInitialRolesModelsPermissions()
    {

        $this->rolesModelsPermissions = [
            'ADMIN' => [
                'news' => [
                    'CREATE' => null,
                    'EDIT' => null,
                    'DELETE' => null,
                    'VIEW' => null,
                    'LIST' => null,
                ]
            ]
        ];

        return $this->rolesModelsPermissions;
    }


    /*
     * PERMESSI EXTRA ASSOCIATI AI RUOLI
     */
    protected function setInitialRolesExtraPermissions()
    {

        $this->rolesExtraPermissions = [
            'ADMIN' => [
                'ACCESS_ROLE' => 'OPERATORE', //'OPERATORE,CLIENTE'
            ],
        ];

        return $this->rolesExtraPermissions;

    }


    /*
     * PERMESSI SUI MODELLI ASSOCIATI ALL'UTENTE NON REGISTRATO
     */
    protected function setInitialGuestModelsPermissions()
    {

        $this->guestModelsPermissions = [];

        return $this->guestModelsPermissions;
    }

    /*
     * PERMESSI EXTRA ASSOCIATI ALL'UTENTE NON REGISTRATO
     */
    protected function setInitialGuestExtraPermissions()
    {

        $this->guestExtraPermissions = [];

        return $this->guestExtraPermissions;

    }


}
