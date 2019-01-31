<?php namespace Gecche\Acl\Console;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Permissions extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permissions';

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
        $permissionsService = new \App\Services\Permissions($this->stubInitialValues);

        $permissionsService->savePermissions();

        $this->comment('File di configurazione permissions.php creato');


    }

    protected function setInitialValues() {
        $this->setInitialModels();
        $this->setInitialRoles();
        $this->setInitialModelsPermissionPrefixes();

        $this->setInitialRolesModelsPermissions();
        $this->setInitialGuestModelsPermissions();

        $this->setInitialExtraPermissions();
        $this->setInitialRolesExtraPermissions();
        $this->setInitialGuestExtraPermissions();

        $this->stubInitialValues['models'] = $this->models;
        $this->stubInitialValues['roles'] = $this->roles;
        $this->stubInitialValues['models_permissions_prefixes'] = $this->modelsPermissionsPrefixes;
        $this->stubInitialValues['extra_permissions'] = $this->extraPermissions;
        $this->stubInitialValues['roles_models_permissions'] = $this->rolesModelsPermissions;
        $this->stubInitialValues['roles_extra_permissions'] = $this->rolesExtraPermissions;
        $this->stubInitialValues['guest_models_permissions'] = $this->guestModelsPermissions;
        $this->stubInitialValues['guest_extra_permissions'] = $this->guestExtraPermissions;


    }

    /*
     * Modelli su cui eseguire le permissions
     */
    protected function setInitialModels() {
          $this->models = [
            'user',
            'role',
            'log',
            'news',
            'comune_istat',
            'pagina',
            'newsletter',
            'newsletter_email',
            'activityqueue',
            'table_test',
        ];

    }


    /*
     * Ruoli disponibili
     */
    protected function setInitialRoles() {

        $this->roles = [
            'LOGIN' => 'Login',
            'ADMIN' => 'Admin',
            'OPERATORE' => 'Operatore',
        ];


    }

    /*
     * Prefissi delle permissions: ogni modello avrà LIST_<MODEL>, VIEW_<MODEL> ecc...
     */

    protected function setInitialModelsPermissionPrefixes() {

        $this->modelsPermissionsPrefixes = [
            'CREATE' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/create/<MODEL>',
                    'GET:/<MODEL>/create',
                    'POST:/<MODEL>',
                ]
            ],
            'EDIT' => [
                'resource_id_required' => true,
                'routes' => [
                    'GET:/<MODEL>/(\d+)/edit',
                    'GET:/edit/<MODEL>/(\d+)',
                    'PUT:/<MODEL>/(\d+)',
                ]
            ],
            'DELETE' => [
                'resource_id_required' => true,
                'routes' => [
                    'DELETE:/<MODEL>/(\d+)',
                    'POST:/<MODEL>/deleteall',
                ]
            ],
            'VIEW' => [
                'resource_id_required' => true,
                'routes' => [
                    'GET:/<MODEL>/(\d+)'
                ]
            ],
            'LIST' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/<MODEL>',
                    'GET:/list/<MODEL>',
                ]
            ],
            'TAB' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/tab/<MODEL>',
                ]
            ],
            'ARCHIVIO' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/archivio/<MODEL>',
                ]
            ],
            'CSV' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/csv/<MODEL>',
                ]
            ],
            'DATAFILE' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/datafile/<MODEL>',
                ]
            ],
        ];


    }

    /*
     * PERMESSI EXTRA AL DI FUORI DEI MODELLI
     */
    protected function setInitialExtraPermissions() {

        $this->extraPermissions = [
            'ACCESS_ROLE' => [
                'resource_id_required' => true,
                'routes' => [

                ],
            ],
            'ADMIN_LANG' => [
                'resource_id_required' => false,
                'routes' => [
                    'GET:/adminlang',
                    'POST:/adminlang/save',
                ]
            ],
        ];


    }

    /*
     * PERMESSI SUI MODELLI ASSOCIATI AI RUOLI
     */
    protected function setInitialRolesModelsPermissions() {

        $this->rolesModelsPermissions = [
            'ADMIN' => [
                'news' => [
                    'CREATE' => null,
                    'EDIT' => null,
                    'DELETE' => null,
                    'VIEW' => null,
                    'LIST' => null,
                    'TAB' => null,
                    'ARCHIVIO' => null,
                ]
            ]
        ];


    }


    /*
     * PERMESSI EXTRA ASSOCIATI AI RUOLI
     */
    protected function setInitialRolesExtraPermissions() {

        $this->rolesExtraPermissions = [
            'ADMIN' => [
                'ACCESS_ROLE' => 'OPERATORE', //'OPERATORE,CLIENTE'
                'ADMIN_LANG' => null,
            ],
        ];

    }


    /*
     * PERMESSI SUI MODELLI ASSOCIATI ALL'UTENTE NON REGISTRATO
     */
    protected function setInitialGuestModelsPermissions() {

        $this->guestModelsPermissions = [
            'news' => [
                'VIEW' => null,
                'ARCHIVIO' => null,
            ]
        ];

    }

    /*
     * PERMESSI EXTRA ASSOCIATI ALL'UTENTE NON REGISTRATO
     */
    protected function setInitialGuestExtraPermissions() {

        $this->guestExtraPermissions = [];

    }

    protected function getArguments()
    {
        return array();
    }

}
