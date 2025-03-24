<?php

require_once __DIR__ . "/inc.app/auth.php";

if(isset($currentUser) && $currentUser->getAdminLevelId() == "superuser")
{
    require_once __DIR__ . "/profile-superuser.php";
}
else
{
    require_once __DIR__ . "/profile-user.php";
}