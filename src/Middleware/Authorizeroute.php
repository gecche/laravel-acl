<?php namespace Gecche\Acl\Middleware;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Cupparis\Acl\Facades\Acl;

class Authorizeroute { //extends \Cupparis\App\Http\Middleware\Authorizeroute {

    protected $request = null;

    public function handle($request, \Closure $next)
    {

        $this->request = $request;
        $permission = $this->getPermissionFromRouteName();
        //$public_routes = Config::get('app.page_paths',array());
        if (!$permission) {
            return $next($request);
        }



        if (!Acl::check($permission['permission'],$permission['resource_id'])) //!in_array($path,$public_routes) &&
        {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }

        return $next($request);

    }

    protected function getPermissionFromRouteName() {

        $routeName = Route::currentRouteName();

        if (!$routeName) {
            return null;
        }

        $routeNameParts = explode('.',$routeName);

        $prefixName = array_get($routeNameParts,0,null);
        if (!$prefixName) {
            $prefixName = null;
        }

        $methodToCall = 'getPermissionForRouteName' . studly_case($prefixName);

        if (method_exists($this,$methodToCall)) {
            return $this->$methodToCall($routeNameParts);
        }

        return $this->getPermissionFromConfig($routeName);

    }

    protected function getPermissionFromConfig($routeName) {
        $authorizationRoutesArray = Config::get('routes.authorizations',[]);
        $authorization = array_get($authorizationRoutesArray,$routeName,null);

        if (!$authorization) {
            return null;
        }

        $permission = array_get($authorization,'permission','');
        $resourceId = null;
        $resourceIdField = array_get($authorization,'resource_id_field',null);
        if ($resourceIdField) {
            $resourceId = $this->request->route($resourceIdField);
        }


        return [
            'permission' => $permission,
            'resource_id' => $resourceId,
        ];

    }

    protected function getPermissionForRouteNameApijson($routeNameArray = array()) {

        //Per il momento le api json contorllano i permessi dentro i controller
        return null;
    }

    protected function getPermissionForRouteNameModelroute($routeNameArray = array()) {

        $modelName = $this->request->route('model');
        $modelAction = array_get($routeNameArray,1,'');
        $modelPermission = strtoupper($modelAction);

        $permissionToCheck = null;
        $resourceId = $this->request->route('pk');

        switch ($modelAction) {

            case 'itinerario':
            case 'tree':
                break;
            case 'insert':
                $permissionToCheck = 'CREATE' . '_' . strtoupper($modelName);
                break;
            case 'ajaxtab':
                $permissionToCheck = 'TAB' . '_' . strtoupper($modelName);
                break;
            case 'calendar':
                $permissionToCheck = 'LIST' . '_' . strtoupper($modelName);
                break;
            default:
                $permissionToCheck = $modelPermission . '_' . strtoupper($modelName);
                break;

        }

        if ($permissionToCheck) {
            return array(
                'permission' => $permissionToCheck,
                'resource_id' => $resourceId,
            );
        }

        return null;

    }
}
