<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

header("Content-type: text/plain");

$dashedLine = "-- --------------------------------------------------------------";

try
{
    if($database->isConnected())
    {
        $applicationName = $appConfig->getApplication()->getName();
        $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
        $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
        $baseEntity = str_replace("\\\\", "\\", $baseEntity);
        $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
        $allQueries = [];

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

        $allQueries[] = $dashedLine;
        $allQueries[] = "-- Application Name : $applicationName";
        $allQueries[] = "-- Description      : Queries for table creation and modification ";
        $allQueries[] = "-- Generator        : MagicAppBuilder";
        $allQueries[] = "-- Database Type    : ".$dbType;
        if($database->getDatabaseConnection() != null)
        {
            $allQueries[] = "-- Database Driver  : ".$database->getDatabaseConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
        $allQueries[] = "-- Time Generated   : ".date('j F Y H:i:s');
        $allQueries[] = "-- Time Zone        : ".date('P'); 
        $allQueries[] = "-- Line Endings     : CRLF";
        $allQueries[] = $dashedLine;
        $allQueries[] = "";

        $merged = $inputPost->getMerged();
        if($merged)
        {
            if($inputPost->getEntity() != null && $inputPost->countableEntity())
            {
                $inputEntity = $inputPost->getEntity();
                $entities = [];
                $entityNames = [];
                foreach($inputEntity as $idx=>$entityName)
                {
                    $className = "\\".$baseEntity."\\".$entityName;
                    $entityName = trim($entityName);
                    $path = $baseDir."/".$entityName.".php";
                    if(file_exists($path))
                    {
                        $return_var = ErrorChecker::errorCheck($databaseBuilder, $path);
                        if($return_var == 0)
                        {
                            include_once $path;                  
                            $entity = new $className(null, $database);
                            $entityInfo = EntityUtil::getTableName($path);
                            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
                            if(!isset($entities[$tableName]))
                            {
                                $entities[$tableName] = [];
                                $entityNames[$tableName] = [];
                            }
                            $entities[$tableName][] = $entity;
                            $entityNames[$tableName][] = $entityName;
                        }
                    }
                }
                foreach($entities as $tableName=>$entity)
                {
                    $entityQueries = [];
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
                        $entNames = array_unique($entityNames[$tableName]);
                        $entityName = implode(", ", $entNames);
                        $allQueries[] = "-- SQL for $entityName begin";
                        $allQueries[] = "\r\n".implode("\r\n", $entityQueries)."\r\n";
                        $allQueries[] = "-- SQL for $entityName end\r\n";
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
                        $return_var = ErrorChecker::errorCheck($databaseBuilder, $path);
                        if($return_var == 0)
                        {         
                            include_once $path;                     
                            $className = "\\".$baseEntity."\\".$entityName;
                            $entity = new $className(null, $database);
                            $dumper = new PicoDatabaseDump();
                
                            $quertArr = $dumper->createAlterTableAdd($entity);
                            $entityQueries = [];
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
                                $allQueries[] = "\r\n".implode("\r\n", $entityQueries)."\r\n";
                                $allQueries[] = "-- SQL for $entityName end\r\n";
                            }
                        }
                    }
                }
            }
        }
        $allQueries[] = $dashedLine;
        $allQueries[] = "-- End of Query";
        $allQueries[] = $dashedLine;
        $sqlQuery = implode("\r\n", $allQueries);

        $lines = explode("\r\n", $sqlQuery);
        $trimmedLines = array_map('rtrim', $lines);
        $sqlQuery = implode("\r\n", $trimmedLines);

        $md5 = hash('md5', $sqlQuery);
        $sha1 = hash('sha1', $sqlQuery);
        echo $sqlQuery;
        echo "\r\n-- MD5 Hash         :  ".$md5;
        echo "\r\n-- SHA1 Hash        :  ".$sha1;
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
