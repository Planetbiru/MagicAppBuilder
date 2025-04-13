<?php

namespace MagicAdmin;

use MagicApp\AppUserPermission;
use MagicObject\Request\InputGet;

class AppUserPermissionExtended extends AppUserPermission
{
    /**
     * Check if the user has access to the module
     *
     * @param InputGet $inputGet
     * @param InputPost $inputPost
     * @return bool Allowed access
     */
    public function allowedAccess($inputGet, $inputPost)
    {
        return true;
    }
}