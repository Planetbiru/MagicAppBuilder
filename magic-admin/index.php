<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\PicoModule;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "index", $appLanguage->getHome());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);
if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

require_once $appInclude->mainAppHeader(__DIR__);

require_once $appInclude->mainAppFooter(__DIR__);

