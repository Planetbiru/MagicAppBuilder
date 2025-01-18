<?php

require_once __DIR__."/auth-core.php";

if(!$userLoggedIn)
{
    require_once __DIR__ . "/login-form.php";
    exit();
}
if(isset($entityAdmin) && $entityAdmin->getAdminLevelId() != "superuser")
{
    require_once __DIR__ . "/not-superuser.php";
    exit();
}
