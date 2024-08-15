<?php

use AppBuilder\AppSecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);

$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
$appListPath = dirname(__DIR__) . "/inc.cfg/application-list.yml";
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}

$appConfig = new AppSecretObject(null);

$curApp = $builderConfig->getCurrentApplication();
$appBaseConfigPath = dirname(__DIR__) . "/inc.cfg/applications";
$configTemplatePath = dirname(__DIR__) . "/inc.cfg/application-template.yml";
if($curApp != null && $curApp->getId() != null)
{
    $appConfigPath = dirname(__DIR__) . "/inc.cfg/applications/".$curApp->getId()."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}