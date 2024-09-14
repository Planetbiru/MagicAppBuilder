<?php

use AppBuilder\AppSecretObject;
use AppBuilder\EntityApvInfo;
use AppBuilder\EntityInfo;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);

$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}

$workspaceDirectory = $builderConfig->getWorkspaceDirectory();
if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
{
    $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
}

$appListPath = $workspaceDirectory."/application-list.yml";

$appConfig = new AppSecretObject(null);

$curApp = $builderConfig->getCurrentApplication();
$appBaseConfigPath = $workspaceDirectory."/applications";
$configTemplatePath = $workspaceDirectory."/application-template.yml";
if($curApp != null && $curApp->getId() != null)
{
    $appConfigPath = $workspaceDirectory."/applications/".$curApp->getId()."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}

$currentApplication = new AppSecretObject();

$appList = new AppSecretObject();
if(file_exists($appListPath))
{
    $appList->loadYamlFile($appListPath, false, true, true);
    $arr = $appList->valueArray();
    foreach($arr as $app)
    {
        if($curApp != null && $curApp->getId() == $app['id'])
        {
            $currentApplication->loadData($app);
        }
    }
}

$entityInfo = new EntityInfo($appConfig->getEntityInfo());
$entityApvInfo = new EntityApvInfo($appConfig->getEntityApvInfo());