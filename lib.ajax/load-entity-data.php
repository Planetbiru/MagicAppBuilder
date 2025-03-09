<?php

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
    $path = $activeWorkspace->getDirectory() . "/entity/data/$filename";
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
        $data['entities'] = json_decode($entities);
        file_put_contents($path, json_encode($data));
    }

    ResponseUtil::sendJSON([]);
} else {
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $activeWorkspace->getDirectory() . "/entity/data/$filename";
    if (!file_exists($path)) {
        $json = json_encode(array('entities' => array(), 'diagrams' => array()));
    } else {
        $json = file_get_contents($path);
    }
    ResponseUtil::sendJSON($json);
}
