<?php

use MagicObject\MagicObject;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();
$path = dirname(__DIR__)."/inc.cfg/application-list.yml";
$dir = dirname($path);
if(!file_exists($dir))
{
    mkdir($dir, 0755, true);
}

$newAppId = trim($inputPost->getId());

$application = array(
    'id'=> $newAppId,
    'name'=> trim($inputPost->getName()),
    'documentRoot'=> trim($inputPost->getDirectory()),
    'author'=> trim($inputPost->getAuthor()),
    'selected'=> false
);

$existing = new MagicObject();
$existing->loadYamlFile($path);

$existingApplication = $existing->valueArray();

$replaced = false;
if(isset($existingApplication) && is_array($existingApplication))
{
    foreach($existingApplication as $key=>$val)
    {
        if($val['id'] == $newAppId)
        {
            $existingApplication[$key] = $application;
            $replaced = true;
        }
    }
    if(!$replaced)
    {
        $existingApplication[] = $application;
    }
}
else
{
    $existingApplication = array($application);
}

file_put_contents($path, (new MagicObject($existingApplication))->dumpYaml());

error_log($path);