<?php

namespace MagicAdmin;

use MagicApp\AppUserPermission;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

/**
 * Class AppUserPermissionExtended
 *
 * Extends the AppUserPermission class to customize or override
 * user permission behavior within the MagicAdmin module.
 */
class AppUserPermissionExtended extends AppUserPermission
{
    /**
     * Determine if the user has access to a specific module.
     *
     * This method always grants access by default. You can override
     * this behavior to implement custom access logic based on request data.
     *
     * @param InputGet $inputGet Incoming GET request data
     * @param InputPost $inputPost Incoming POST request data
     * @return bool True if access is allowed, otherwise false
     */
    public function allowedAccess($inputGet, $inputPost)
    {
        return true;
    }
}
