<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

if($inputPost->getDatabaseName() !== null)
{
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $entities = $inputPost->getEntities();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $activeWorkspace->getDirectory()."/entity/data/$filename";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $entities);
    ResponseUtil::sendJSON([]);
}
else
{
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $activeWorkspace->getDirectory()."/entity/data/$filename";
    if(!file_exists($path))
    {
        $json = "[]";
    }
    else
    {
        $json = file_get_contents($path);
    }
    ResponseUtil::sendJSON($json);
}
