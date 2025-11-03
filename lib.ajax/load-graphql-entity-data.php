<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$data = file_get_contents('php://input');
$inputGet = new InputGet();

if ($inputGet->getDatabaseName() !== null) {
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();



    $filename = sprintf("%s-%s-%s-%s-data-graphql.json", $applicationId, $databaseType, $databaseName, $databaseSchema);

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/data";

        $path = $basePath . "/$filename";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $data);
        
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON([]);
    exit();
} else {
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $json = json_encode(array('entities' => array(), 'diagrams' => array()));
    $filename = sprintf("%s-%s-%s-%s-data-graphql.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/data";
        $path = $basePath . "/$filename";
        if (file_exists($path)) {
            $json = file_get_contents($path);
        } else {
            $json = '{}';
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON($json);
    exit();
}
