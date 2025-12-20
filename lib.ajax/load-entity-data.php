<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;
use MagicObject\Util\ClassUtil\PicoObjectParser;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new MagicObject(PicoObjectParser::parseRecursiveObject(json_decode(file_get_contents("php://input"))));
$inputGet = new InputGet();

if ($inputPost->getDatabaseName() !== null) {
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $entities = $inputPost->getEntities();
    $diagrams = $inputPost->getDiagrams();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $hash = md5("$applicationId-$databaseType-$databaseName-$databaseSchema");

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $path = $selectedApplication->getProjectDirectory()."/__data/entity/data/$filename";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $indexPath = $selectedApplication->getProjectDirectory()."/__data/entity/index.json";
        $dir = dirname($indexPath);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            $raw = file_get_contents($path);
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                $data = array('entities' => array(), 'diagrams' => array());
            }
        } else {
            $data = array('entities' => array(), 'diagrams' => array());
        }

        if (!empty($diagrams)) {
            $data['diagrams'] = $diagrams;
            file_put_contents($path, json_encode($data));
        }
        $entityCount = 0;
        if (!empty($entities)) {
            $data['entities'] = $entities;

            // Update entity->creator and entity->modifier
            foreach($data['entities'] as $index => $entity) {
                if (isset($entity->creator) && $entity->creator == '{{userName}}') {
                    $data['entities'][$index]->creator = $entityAdmin->getName();
                }
                if (isset($entity->modifier) && $entity->modifier == '{{userName}}') {
                    $data['entities'][$index]->modifier = $entityAdmin->getName();
                }
                $entityCount++;
            }
            
            file_put_contents($path, json_encode($data));
        }

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
        $index[$hash]['databaseType'] = $databaseType;
        $index[$hash]['databaseName'] = $databaseName;
        $index[$hash]['databaseSchema'] = $databaseSchema;
        $index[$hash]['entities'] = $entityCount;
        $index[$hash]['file'] = "data/$filename";
        $index[$hash]['hash'] = $hash;
        if(empty($index[$hash]['databaseName']))
        {
            $appConfigPath = $selectedApplication->getProjectDirectory()."/default.yml";
            $appConfig = new SecretObject();
            if(file_exists($appConfigPath))
            {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
                $index[$hash]['label'] = basename($appConfig->getDatabase()->getDatabaseFilePath());
            }
        }
        else
        {
            $index[$hash]['label'] = $index[$hash]['databaseName'];
        }

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
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $json = json_encode(array('entities' => array(), 'diagrams' => array()));
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $path = $selectedApplication->getProjectDirectory()."/__data/entity/data/$filename";
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $data = json_decode($json, true);

            $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
            if(file_exists($appConfigPath))
            {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
            }

            $entityInfo = null;
            if(isset($appConfig) && $appConfig->getEntityInfo() != null)
            {
                $entityInfo = $appConfig->entityInfo;
            }
            else
            {
                $entityInfo = new SecretObject(array(
                    'name' => 'name',
                    'sortOrder' => 'sort_order',
                    'adminCreate' => 'admin_create',
                    'adminEdit' => 'admin_edit',
                    'timeCreate' => 'time_create',
                    'timeEdit' => 'time_edit',
                    'ipCreate' => 'ip_create',
                    'ipEdit' => 'ip_edit',
                    'active' => 'active'
                ));
            }

            $data['reservedColumns'] = $entityInfo;
            $data['__magic_signature__'] = 'MAGICAPPBUILDER-DB-DESIGN-V1';
            $json = json_encode($data);
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
