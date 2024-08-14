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

    if($inputGet->getEntity() != null && $inputGet->countableEntity())
    {
        $inputEntity = $inputGet->getEntity();
        $entities = array();
        $entityNames = array();
        
        
        $entities = array();
        
        $entityRelationshipDiagram = new EntityRelationshipDiagram($appConfig, 170, null, 30);
        
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

        echo $entityRelationshipDiagram;
        
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
