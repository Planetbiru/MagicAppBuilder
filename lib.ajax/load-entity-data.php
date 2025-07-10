<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

if ($inputPost->getDatabaseName() !== null) {
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $entities = $inputPost->getEntities();
    $diagrams = $inputPost->getDiagrams();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/data";

        $path = $basePath . "/$filename";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
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

        if (strlen($diagrams) > 10) {
            $data['diagrams'] = json_decode($diagrams);
            file_put_contents($path, json_encode($data));
        }
        if (strlen($entities) > 10) {

            

            $data['entities'] = json_decode($entities, true);

            // Update entity->creator and entity->modifier
            foreach($data['entities'] as $index => $entity) {
                if (isset($entity['creator']) && $entity['creator'] == '{{userName}}') {
                    $data['entities'][$index]['creator'] = $entityAdmin->getName();
                }
                if (isset($entity['modifier']) && $entity['modifier'] == '{{userName}}') {
                    $data['entities'][$index]['modifier'] = $entityAdmin->getName();
                }
            }
            
            file_put_contents($path, json_encode($data));
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON([]);
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
        $basePath = $selectedApplication->getProjectDirectory()."/__data/entity/data";
        $path = $basePath . "/$filename";
        if (file_exists($path)) {
            $json = file_get_contents($path);
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON($json);
}
