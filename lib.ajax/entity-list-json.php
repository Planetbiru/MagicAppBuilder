<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;

require_once dirname(__DIR__) . "/inc.app/auth.php";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $applicationId = $appConfig->getApplication()->getId();
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    
    $baseEntityData = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntityData = str_replace("\\\\", "\\", $baseEntityData);
    $baseDirData = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntityData, "\\/"));
    $listData = glob($baseDirData . "/*.php");

    $entities = ["Data" => [], "App" => []];

    foreach ($listData as $file) {
        $entityName = basename($file, '.php');
        $filetime = date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $returnVar = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);

        $className = "\\".$baseEntityData."\\".$entityName;
        $path = $baseDirData."/".$entityName.".php";
        
        $tooltip = EntityUtil::getEntityTooltipFromFileAsJson($path, $className, $filetime);

        $entityData = [
            'name' => "$dir\\$entityName",
            'filetime' => $filetime,
            'entityName' => $entityName,
            'className' => $className,
            'path' => $path,
            'error' => $returnVar !== 0,
            'tooltip' => $tooltip
        ];

        $entities["Data"][] = $entityData;
    }

    $baseEntityApp = $appConfig->getApplication()->getBaseEntityAppNamespace();
    $baseEntityApp = str_replace("\\\\", "\\", $baseEntityApp);
    $baseDirApp = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntityApp, "\\/"));
    $listApp = glob($baseDirApp . "/*.php");

    foreach ($listApp as $file) {
        $entityName = basename($file, '.php');
        $filetime = date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $returnVar = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);

        $className = "\\".$baseEntityApp."\\".$entityName;
        $path = $baseDirApp."/".$entityName.".php";
        
        $tooltip = EntityUtil::getEntityTooltipFromFileAsJson($path, $className, $filetime);
        $entityData = [
            'name' => "$dir\\$entityName",
            'filetime' => $filetime,
            'entityName' => $entityName,
            'className' => $className,
            'path' => $path,
            'error' => $returnVar !== 0,
            'tooltip' => $tooltip
        ];

        $entities["App"][] = $entityData;
    }

    header('Content-Type: application/json');
    echo json_encode($entities, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["error" => "An error occurred."]);
}
