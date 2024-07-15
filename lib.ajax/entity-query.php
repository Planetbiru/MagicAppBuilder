<?php

use AppBuilder\Util\EntityUtil;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();
try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    
    $allQueries = array();

    $merged = $inputPost->getMerged();

    if($merged)
    {
        if($inputPost->getEntity() != null && $inputPost->countableEntity())
        {
            $inputEntity = $inputPost->getEntity();

            $entities = array();

            $entityNames = array();

            foreach($inputEntity as $idx=>$entityName)
            {
                $className = "\\".$baseEntity."\\".$entityName;
                $entityName = trim($entityName);
                $path = $baseDir."/".$entityName.".php";

                if(file_exists($path))
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
        if($inputPost->getEntity() != null && $inputPost->countableEntity())
        {
            $allQueries[] = '-- Important to understand!'."\r\n".'-- Queries must be run one by one manually because there may be duplicate columns from different entities for the same table.';
            $inputEntity = $inputPost->getEntity();
            foreach($inputEntity as $entityName)
            {
                $entityName = trim($entityName);
                $path = $baseDir."/".$entityName.".php";
                
                exec("php -l $path 2>&1", $output, $return_var);
                if ($return_var === 0)
                {         
                    $entityQueries = array();
                    if(file_exists($path))
                    {
                        include_once $path;
                        
                        $className = "\\".$baseEntity."\\".$entityName;
                        $entity = new $className(null, $database);
                        $dumper = new PicoDatabaseDump();
            
                        $quertArr = $dumper->createAlterTableAdd($entity);
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
