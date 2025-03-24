<?php

use MagicObject\SecretObject;

$appMenuData = new SecretObject();

$appMenuPath = __DIR__ . "/menu.yml";
if(file_exists($appMenuPath))
{
    $appMenuData->loadYamlFile($appMenuPath, false, true, true);
}
require_once dirname(dirname(__DIR__)) . "/inc.resources/lib.themes/default/header.php";