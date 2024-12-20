<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

if($inputPost->getDatabaseName() !== null)
{
    $applicationId = $inputPost->hetApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $entities = $inputPost->getEntities();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $workspaceDirectory."/entities/$filename";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $entities);
    ResponseUtil::sendJSON([]);
}
else
{
    $applicationId = $inputGet->hetApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $workspaceDirectory."/entities/$filename";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    if(!file_exists($path))
    {
        file_put_contents($path, "[]");
    }
    $json = file_get_contents($path);
    ResponseUtil::sendJSON($json);
}
