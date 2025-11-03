<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
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
        if(!empty($data))
        {
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $data);
            ResponseUtil::sendJSON([]);
        }
        else
        {
            if(file_exists($path))
            {
                $data = file_get_contents($path);
                ResponseUtil::sendJSON($data);
            }
            else
            {
                ResponseUtil::sendJSON([]);
            }
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    exit();
} 