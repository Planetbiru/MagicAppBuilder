<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
if ($inputGet->getDatabaseName() !== null) {
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    $index = new stdClass;
    try
    {
        $selectedApplication->find($applicationId);

        $indexPath = $selectedApplication->getProjectDirectory()."/__data/graphql-app/index.json";
        $dir = dirname($indexPath);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }
        $rawIndex = file_get_contents("php://input");
        if(!empty($rawIndex))
        {
            file_put_contents($indexPath, $rawIndex);
            $index = json_decode($rawIndex);
        }
        else
        {
            if(file_exists($indexPath))
            {
                $rawIndex = file_get_contents($indexPath);
                $index = json_decode($rawIndex);
            }
            else
            {
                $defaultProfile = new stdClass;
                $defaultProfile->profileName = $applicationId;
                $defaultProfile->profileLabel = $selectedApplication->getName();
                $defaultProfile->hasData = true;
                $defaultProfile->selected = true;
                $index->{$applicationId} = $defaultProfile;
                file_put_contents($indexPath, json_encode($index));
            }
        }
        ResponseUtil::sendJSON($index);
        exit();
    }
    catch(Exception $e)
    {
        // Do noting
    }
}
ResponseUtil::sendJSON(new stdClass);
exit();