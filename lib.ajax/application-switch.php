<?php

use AppBuilder\AppSecretObject;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();

$currentApplication = trim($inputPost->getCurrentApplication());

$path = dirname(__DIR__)."/inc.cfg/application-list.yml";
$dir = dirname($path);
if(!file_exists($dir))
{
    mkdir($dir, 0755, true);
}

$builderConfig = new AppSecretObject(null);

$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
$appListPath = dirname(__DIR__) . "/inc.cfg/application-list.yml";
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}

$builderConfig->getCurrentApplication()->setId($currentApplication);

$existing = new MagicObject();
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
file_put_contents($path, (new MagicObject($existingApplication))->dumpYaml());
file_put_contents($builderConfigPath, (new MagicObject($builderConfig))->dumpYaml());
header('Content-type: application/json');
echo '{}';