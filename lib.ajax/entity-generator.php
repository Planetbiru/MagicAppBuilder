<?php

use AppBuilder\AppField;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputPost = new InputPost();

try
{
    $tableName = $inputPost->getTableName();
    $entityName = $inputPost->getEntityName();
    $baseEntityName = basename($entityName);
    $entityName = str_replace("\\\\", "\\", $entityName);
    $entityName = str_replace("/", "\\", $entityName);
    $arr = explode("\\", $entityName);
    if(count($arr) > 1)
    {
        $dir = implode("\\", array_slice($arr, 0, -1))."\\";
    }
    else
    {
        $dir = "";
    }

    
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    
    if(stripos($dir, "App\\") !== false)
    {
        $baseEntity = $appConfig->getApplication()->getBaseEntityAppNamespace();
    }
    else
    {
        $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    }
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));

    $entityInfo = $appConfig->getEntityInfo();
    if(!empty($tableName) && !empty($entityName))
    {
        $gen = new PicoEntityGenerator($database, $baseDirectory, $tableName, $baseEntity, $baseEntityName);
        $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
        $gen->generate($nonupdatables);
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);