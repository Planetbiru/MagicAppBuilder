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
    $profile = $inputGet->getProfile();

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);

        if($profile == '')
        {
            $indexPath2 = $selectedApplication->getProjectDirectory()."/__data/graphql-app/index.json";
            if(file_exists($indexPath2))
            {
                $index2 = json_decode(file_get_contents($indexPath2), true);
                foreach($index2 as $key=>$value)
                {
                    if($value['selected'])
                    {
                        $profile = $key;
                    }
                }
            }
        }

        if(empty($profile))
        {
            ResponseUtil::sendJSON([]);
            exit();
        }


        $filename = sprintf("%s.json", $profile);
        $hash = $profile;

        $path = $selectedApplication->getProjectDirectory()."/__data/graphql-app/data/$filename";
        $dir = dirname($path);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }
        $indexPath = $selectedApplication->getProjectDirectory()."/__data/graphql-app/index.json";

        if(!empty($data))
        {
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $data);
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
            if(!isset($index[$hash]) || !is_array($index[$hash]))
            {
                $index[$hash] = array();
            }
            $index[$hash]['applicationId'] = $applicationId;
            $index[$hash]['hash'] = $hash;
            
            file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT));

            ResponseUtil::sendJSON([]);
            exit();
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
                ResponseUtil::sendJSON([
                    "custom" => true,
                    "system" => false,
                    "entities" => [

                    ],
                    "entitySelector" => [
                    ]
                ]);
            }
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    exit();
}