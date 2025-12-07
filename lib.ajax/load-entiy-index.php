<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

if ($inputPost->getDatabaseName() !== null) {
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $name = $inputPost->getName();
    
    $hash = md5("$applicationId-$databaseType-$databaseName-$databaseSchema");

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $indexPath = $selectedApplication->getProjectDirectory()."/__data/entity/schema-list.json";
        if(!file_exists($indexPath))
        {
            $indexRaw = '{}';
        }
        else
        {
            $indexRaw = file_get_contents($indexPath);
        }
        $index = json_decode($indexRaw, true);
        if(!is_array($index))
        {
            $index = [];
        }
        if(!is_array($index[$hash]))
        {
            $index[$hash] = array();
        }
        $index[$hash]['name'] = $name;

        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT));
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON([]);
    exit();
} else {
    $applicationId = $inputGet->getApplicationId();
    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    $json = new stdClass;
    try
    {
        $selectedApplication->find($applicationId);
        $indexPath = $selectedApplication->getProjectDirectory()."/__data/entity/index.json";
        if(file_exists($indexPath))
        {
            $indexRaw = file_get_contents($indexPath);
            $json = json_decode($indexRaw, true);

            $appConfigPath = $selectedApplication->getProjectDirectory()."/default.yml";
            $appConfig = new SecretObject();
            if(file_exists($appConfigPath))
            {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
                $databaseConfig = $appConfig->getDatabase();
                $databaseType = $databaseConfig->getDriver();
                $databaseName = $databaseConfig->getDatabaseName();
                $databaseSchema = $databaseConfig->getDatabaseSchema();
                $hash = md5("$applicationId-$databaseType-$databaseName-$databaseSchema");
                if(!isset($json[$hash]))
                {
                    // Add to index
                    $json[$hash] = array();
                    $json[$hash]['applicationId'] = $applicationId;
                    $json[$hash]['databaseType'] = $databaseType;
                    $json[$hash]['databaseName'] = $databaseName;
                    $json[$hash]['databaseSchema'] = $databaseSchema;
                    $json[$hash]['entities'] = 0;
                    $json[$hash]['file'] = "";
                    $json[$hash]['hash'] = $hash;
                    if(empty($json[$hash]['databaseName']))
                    {
                        $json[$hash]['label'] = basename($databaseConfig->getDatabaseFilePath());
                    }
                    else
                    {
                        $json[$hash]['label'] = $json[$hash]['databaseName'];
                    }
                }
            }
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON($json);
            
    exit();
}
