<?php

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
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    $tableName = $inputPost->getTableName();
    $entityName = $inputPost->getEntityName();
    if(!empty($tableName) && !empty($entityName))
    {
        $gen = new PicoEntityGenerator($database, $baseDirectory, $tableName, $baseEntity, $entityName);
        $gen->generate();
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);