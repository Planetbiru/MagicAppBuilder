<?php

use MagicObject\SecretObject;

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


$appConfig->setAssets('/inc.resources/lib.themes/default/assets/');
$menuLoader = new SecretObject();

$appMenuData = $menuLoader->loadYamlFile(dirname(dirname(__DIR__)) . "/magic-admin/inc.app/menu.yml", false, true, true);

