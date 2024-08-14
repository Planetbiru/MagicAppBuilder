<?php

use AppBuilder\Generator\EntityRelationshipDiagram;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputGet = new InputGet();
try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
    $allQueries = array();
    $merged = $inputGet->getMerged();

    if($inputGet->getEntity() != null && $inputGet->countableEntity() && count($inputGet->getEntity()) > 0)
    {
        $inputEntity = $inputGet->getEntity();
        $entities = array();
        $entityNames = array();
        $entities = array();
        $entityRelationshipDiagram = new EntityRelationshipDiagram($appConfig, 150, 20);
        $entityRelationshipDiagram->setMarginX(0);
        $entityRelationshipDiagram->setMarginY(0);
        $entityRelationshipDiagram->setMaximumLevel(0);
        foreach($inputEntity as $idx=>$entityName)
        {
            $className = "\\".$baseEntity."\\".$entityName;
            $entityName = trim($entityName);
            $path = $baseDir."/".$entityName.".php";
            if(file_exists($path))
            {
                include_once $path;                  
                $entity = new $className(null);
                $entityRelationshipDiagram->addEntity($entity);
            }
        }
        header('Content-Type: image/svg+xml');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        echo $entityRelationshipDiagram;
    }
    else
    {
        header('Content-Type: image/svg+xml');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        echo '<?xml version="1.0" encoding="utf-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1" height="1"></svg>';
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}