<?php

use AppBuilder\AppSecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);

$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}

$appConfig = new AppSecretObject(null);

$curApp = $builderConfig->getCurrentApplication();
$appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
$configTemplatePath = $activeWorkspace->getDirectory()."/application-template.yml";
if($curApp != null && $activeApplication->getApplicationId() != null)
{
    $appConfigPath = $activeWorkspace->getDirectory()."/".$activeApplication->getApplicationId()."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}