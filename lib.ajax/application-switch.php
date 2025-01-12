<?php

use AppBuilder\AppSecretObject;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$currentApplication = trim($inputPost->getCurrentApplication());

$path = $activeWorkspace->getDirectory()."/application-list.yml";
$dir = dirname($path);
if(!file_exists($dir))
{
    mkdir($dir, 0755, true);
}

$builderConfig = new AppSecretObject(null);

$builderConfigPath = $activeWorkspace->getDirectory()."/core.yml";
$appListPath = $activeWorkspace->getDirectory()."/application-list.yml";
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}

if($builderConfig->getCurrentApplication() == null)
{
    $builderConfig->setCurrentApplication(new SecretObject());
}

$builderConfig->getCurrentApplication()->setId($currentApplication);
$existing = new SecretObject();
$existing->loadYamlFile($path);
$existingApplication = $existing->valueArray();

$replaced = false;
if(isset($existingApplication) && is_array($existingApplication))
{
    foreach($existingApplication as $key=>$val)
    {
        $existingApplication[$key]['selected'] = false;
    }
    foreach($existingApplication as $key=>$val)
    {
        if($existingApplication[$key]['id'] == $currentApplication)
        {
            $existingApplication[$key]['selected'] = true; 
        }
    }
}
else
{
    $existingApplication = array($application);
}

file_put_contents($path, (new SecretObject($existingApplication))->dumpYaml());
file_put_contents($builderConfigPath, (new SecretObject($builderConfig))->dumpYaml());
ResponseUtil::sendJSON(new stdClass);