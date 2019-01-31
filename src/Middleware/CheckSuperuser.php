<?php namespace Cupparis\App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CheckSuperuser
{

    public function handle($request, \Closure $next)
    {

        $userId = Auth::id();

        $superusersIds = Config::get('acl.superusers', []);

        if (!in_array($userId, $superusersIds)) {

            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }


        return $next($request);
    }

}
