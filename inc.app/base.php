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