<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $activeApplication->getApplicationId();
}

if($applicationId != null)
{
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$activeApplication->getApplicationId()."/default.yml";
    
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}
$menuPath = $appConfig->getApplication()->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
if(!file_exists($menuPath))
{
    if(!file_exists(dirname($menuPath)))
    {
        mkdir(dirname($menuPath), 0755, true);
    }
    file_put_contents($menuPath, "");
}

$menus = new SecretObject();
$menus->loadYamlFile($menuPath, false, true, true);
ResponseUtil::sendJSON($menus, false, true);