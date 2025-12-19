<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new MagicObject(file_get_contents('php://input'));
$inputGet = new InputGet();

if($inputPost->getConfig() != null)
{
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/config";
        $config = $inputPost->getConfig();
        $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
        $path = $basePath."/$filename";
        if(!file_exists(dirname($path)))
        {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $config);
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON([]);
}
else
{
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();

    $json = array(
        'primaryKeyDataType'=> 'VARCHAR',
        'primaryKeyDataLength'=> 40,
        'defaultDataType'=> 'VARCHAR',
        'defaultDataLength'=> 50
    );

    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/config";
        $path = $basePath."/$filename";
        if(file_exists($path))
        {
            $json = file_get_contents($path);
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON($json);
    exit();
}
