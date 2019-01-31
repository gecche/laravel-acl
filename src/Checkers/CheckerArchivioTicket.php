<?php

namespace Gecche\Acl\Checkers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Main ACL class for checking does user have some permissions.
 */
class CheckerArchivioTicket extends Checker
{

    public function check($resourceId,$userPermission,$userId) {
        $allowed_tickets = $this->acl->getResourceIds('ARCHIVIO_TICKET');
        if ($allowed_tickets['allowed']  || !empty($allowed_tickets['ids']))
            return true;

        $count = DB::table('tickets')->where('user_id',$userId)->count();
        return $count > 0;
    }

}
