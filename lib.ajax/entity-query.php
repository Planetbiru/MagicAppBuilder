<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputGet = new InputGet();

header("Content-type: text/plain");

try
{
    
    
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
    $allQueries = array();
    $merged = $inputGet->getMerged();
    if($merged)
    {
        if($inputGet->getEntity() != null && $inputGet->countableEntity())
        {
            $inputEntity = $inputGet->getEntity();
            $entities = array();
            $entityNames = array();
            foreach($inputEntity as $idx=>$entityName)
            {
                $className = "\\".$baseEntity."\\".$entityName;
                $entityName = trim($entityName);
                $path = $baseDir."/".$entityName.".php";
                if(file_exists($path))
                {
                    $return_var = ErrorChecker::errorCheck($cacheDir, $path);
                    if($return_var == 0)
                    {
                        include_once $path;                  
                        $entity = new $className(null, $database);
                        $tableInfo = EntityUtil::getTableName($path);
                        $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
                        if(!isset($entities[$tableName]))
                        {
                            $entities[$tableName] = array();
                            $entityNames[$tableName] = array();
                        }
                        $entities[$tableName][] = $entity;
                        $entityNames[$tableName][] = $entityName;
                    }
                }
            }
            foreach($entities as $tableName=>$entity)
            {
                $entityQueries = array();
                $dumper = new PicoDatabaseDump();   
                $quertArr = $dumper->createAlterTableAddFromEntities($entity, $tableName, $database);
                foreach($quertArr as $sql)
                {
                    if(!empty($sql))
                    {
                        $entityQueries[] = $sql."";
                    }
                }
                if(!empty($entityQueries))
                {
                    $entityName = implode(", ", $entityNames[$tableName]);
                    $allQueries[] = "-- SQL for $entityName begin";
                    $allQueries[] = implode("\r\n", $entityQueries);
                    $allQueries[] = "-- SQL for $entityName end\r\n";
                }           
            }
        }
        echo implode("\r\n\r\n", $allQueries);
    }
    else
    {
        if($inputGet->getEntity() != null && $inputGet->countableEntity())
        {
            $allQueries[] = "-- Important to understand!\r\n-- Queries must be run one by one manually because there may be duplicate columns from different entities for the same table.";
            $inputEntity = $inputGet->getEntity();
            foreach($inputEntity as $entityName)
            {
                $entityName = trim($entityName);
                $path = $baseDir."/".$entityName.".php";               
                if(file_exists($path))
                {
                    $return_var = ErrorChecker::errorCheck($cacheDir, $path);
                    if($return_var == 0)
                    {         
                        include_once $path;                     
                        $className = "\\".$baseEntity."\\".$entityName;
                        $entity = new $className(null, $database);
                        $dumper = new PicoDatabaseDump();
            
                        $quertArr = $dumper->createAlterTableAdd($entity);
                        $entityQueries = array();
                        foreach($quertArr as $sql)
                        {
                            if(!empty($sql))
                            {
                                $entityQueries[] = $sql."";
                            }
                        }
                        if(!empty($entityQueries))
                        {
                            $allQueries[] = "-- SQL for $entityName begin";
                            $allQueries[] = implode("\r\n", $entityQueries);
                            $allQueries[] = "-- SQL for $entityName end\r\n";
                        }
                    }
                }
            }
        }
        echo implode("\r\n\r\n", $allQueries);
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
