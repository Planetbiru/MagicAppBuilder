<?php

use AppBuilder\Util\Entity\EntityRelationshipDiagram;
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
    $allQueries = [];
    $merged = $inputGet->getMerged();

    if($inputGet->getEntity() != null && $inputGet->countableEntity() && count($inputGet->getEntity()) > 0)
    {
        $inputEntity = $inputGet->getEntity();
        $entities = [];
        $entityNames = [];
        $entities = [];
        
        $entityMarginX = $inputGet->getEntityMarginX();
        $entityMarginY = $inputGet->getEntityMarginY();
        $zoom = $inputGet->getZoom();
        
        if($entityMarginX < 1)
        {
            $entityMarginX = 40;
        }
        if($entityMarginY < 1)
        {
            $entityMarginY = 20;
        }
        if($zoom < 0.25)
        {
            $zoom = 1;
        }
        
        $entityRelationshipDiagram = new EntityRelationshipDiagram($appConfig, 200, $entityMarginX, $entityMarginY);
        $entityRelationshipDiagram->setMarginX($inputGet->getMarginX());
        $entityRelationshipDiagram->setMarginY($inputGet->getMarginY());
        $entityRelationshipDiagram->setMaximumLevel($inputGet->getMaximumLevel());
        $entityRelationshipDiagram->setMaximumColumn($inputGet->getMaximumColumn());
        $entityRelationshipDiagram->setZoom($zoom);
        $entityRelationshipDiagram->setCacheDir($cacheDir);
        
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
        header('Content-Type: text/html');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        echo $entityRelationshipDiagram->getImageMap();
    }
    else
    {
        header('Content-Type: text/html');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}