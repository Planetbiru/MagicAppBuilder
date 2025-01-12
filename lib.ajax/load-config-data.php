<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();
$inputGet = new InputGet();

if($inputPost->getConfig() != null)
{
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $config = $inputPost->getConfig();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $activeWorkspace->getDirectory()."/entity/config/$filename";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    error_log($path);
    file_put_contents($path, $config);
    ResponseUtil::sendJSON([]);
}
else
{
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $activeWorkspace->getDirectory()."/entity/config/$filename";
    error_log($path);
    if(!file_exists($path))
    {
        $json = [
            'primaryKeyDataType'=> 'VARCHAR',
            'primaryKeyDataLength'=> 40,
            'defaultDataType'=> 'VARCHAR',
            'defaultDataLength'=> 50
        ];
    }
    else
    {
        $json = file_get_contents($path);
    }
    ResponseUtil::sendJSON($json);
}
