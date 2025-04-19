<?php

require_once __DIR__ . "/inc.app/auth-profile.php";

if(isset($entityAdmin) && $entityAdmin->getAdminLevelId() == "superuser")
{
    require_once __DIR__ . "/git-profile-superuser.php";
}
else
{
    require_once __DIR__ . "/git-profile-user.php";
}