<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputPost = new InputPost();

header("Content-type: text/plain");

$dashedLine = "-- -----------------------------------------------------------------";

function implodeWithAnd($array) {
    if (count($array) > 1) {
        $lastElement = array_pop($array);
        return implode(', ', $array) . ' and ' . $lastElement;
    }
    return $array[0];
}
try
{
    if($database->isConnected())
    {
        $applicationName = $appConfig->getApplication()->getName();
        $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
        $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
        $baseEntity = str_replace("\\\\", "\\", $baseEntity);
        $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
        $allQueries = array();
        
        $allEntities = array();
        $allTables = array();

        $dbType = "";
        if($database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_PGSQL)
        {
            $dbType = "PostgreSQL";
        }
        else if($database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $dbType = "SQLite";
        }
        else
        {
            $dbType = "MySQL";
        }
        $createNewTable = $inputPost->getCreateNew() == 1;
        if($createNewTable)
        {
            $description = "Queries for table creation";
        }
        else
        {
            $description = "Queries for table creation and modification";
        }
        $allQueries[] = $dashedLine;
        $allQueries[] = "-- Application Name   : $applicationName";
        $allQueries[] = "-- Description        : $description";
        $allQueries[] = "-- Generator          : MagicAppBuilder";
        $allQueries[] = "-- Database Type      : ".$dbType;
        if($database->getDatabaseConnection() != null)
        {
            $allQueries[] = "-- Database Driver    : ".$database->getDatabaseConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
        $allQueries[] = "-- Time Generated     : ".date('j F Y H:i:s');
        $allQueries[] = "-- Time Zone          : ".date('P'); 
        $allQueries[] = "-- Line Endings       : CRLF";
        $allQueries[] = $dashedLine;
        $allQueries[] = "";

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
                        $returnVar = ErrorChecker::errorCheck($databaseBuilder, $path);
                        if($returnVar == 0)
                        {
                            include_once $path;                  
                            $entity = new $className(null, $database);
                            $entityInfo = EntityUtil::getTableName($path);
                            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
                            if(!isset($entities[$tableName]))
                            {
                                $entities[$tableName] = array();
                                $entityNames[$tableName] = array();
                            }
                            $entities[$tableName][] = $entity;
                            $entityNames[$tableName][] = $entityName;
                            $allEntities[] = $className;
                        }
                    }
                }
                foreach($entities as $tableName=>$entity)
                {
                    $entityQueries = array();
                    $dumper = new PicoDatabaseDump();   
                    $queryArr = $dumper->createAlterTableAddFromEntities($entity, $tableName, $database, true, false, $createNewTable);
                    foreach($queryArr as $sql)
                    {
                        if(!empty($sql))
                        {
                            $entityQueries[] = $sql."";
                        }
                    }
                    if(!empty($entityQueries))
                    {
                        $entNames = array_unique($entityNames[$tableName]);
                        $entityName = implodeWithAnd($entNames);
                        $entityName = str_replace("\\", ".", $entityName);
                        $allQueries[] = "-- SQL for $entityName begin";
                        $allQueries[] = "\r\n".implode("\r\n", $entityQueries)."\r\n";
                        $allQueries[] = "-- SQL for $entityName end\r\n";
                        
                        
                        $allTables[] = $tableName;
                    }           
                }
            }
        }
        else
        {
            if($inputPost->getEntity() != null && $inputPost->countableEntity())
            {
                $allQueries[] = "-- Important Notes:";
                $allQueries[] = "-- Queries must be executed one by one manually,";
                $allQueries[] = "-- as there may be duplicate columns from different entities";
                $allQueries[] = "-- in the same table.";
                $allQueries[] = "-- Consider merging queries by table to avoid duplication.";
                $allQueries[] = "";
                $inputEntity = $inputPost->getEntity();
                foreach($inputEntity as $entityName)
                {
                    $entityName = trim($entityName);
                    $path = $baseDir."/".$entityName.".php"; 
                    if(file_exists($path))
                    {
                        $returnVar = ErrorChecker::errorCheck($databaseBuilder, $path);
                        if($returnVar == 0)
                        {         
                            include_once $path;                     
                            $className = "\\".$baseEntity."\\".$entityName;
                            $entity = new $className(null, $database);
                            $dumper = new PicoDatabaseDump();
                
                            $queryArr = $dumper->createAlterTableAdd($entity);
                            $entityQueries = array();
                            foreach($queryArr as $sql)
                            {
                                if(!empty($sql))
                                {
                                    $entityQueries[] = $sql."";
                                }
                            }
                            if(!empty($entityQueries))
                            {
                                $entityName = str_replace("\\", ".", $entityName);
                                $allQueries[] = "-- SQL for $entityName begin";
                                $allQueries[] = "\r\n".implode("\r\n", $entityQueries)."\r\n";
                                $allQueries[] = "-- SQL for $entityName end\r\n";

                                $tableInfo = $entity->tableInfo();
                            
                                $allEntities[] = $className;
                                $allTables[] = $tableInfo->getTableName();
                            }
                            
                        }
                    }
                }
            }
        }        
        
        $allQueries[] = $dashedLine;
        $allQueries[] = "-- End of Query";
        $allQueries[] = $dashedLine;
        
        $numberOfEntities = count(array_unique($allEntities));
        $numberOfTables = count(array_unique($allTables));
        $allQueries[] = "-- Number of Entities : ".$numberOfEntities;
        $allQueries[] = "-- Number of Tables   : ".$numberOfTables;      
        
        $sqlQuery = implode("\r\n", $allQueries);

        $lines = explode("\r\n", $sqlQuery);
        $trimmedLines = array_map('rtrim', $lines);
        $sqlQuery = implode("\r\n", $trimmedLines);

        $md5 = hash('md5', $sqlQuery);
        $sha1 = hash('sha1', $sqlQuery);
        echo $sqlQuery;
        echo "\r\n-- MD5 Hash           : ".$md5;
        echo "\r\n-- SHA1 Hash          : ".$sha1;
    }
    else
    {
        echo "-- No database connection.";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
