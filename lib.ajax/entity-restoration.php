<?php

use AppBuilder\AppDatabase;
use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$application = new EntityApplication(null, $databaseBuilder);
try
{

    $application->findOneByApplicationId($applicationId);
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    $menuAppConfig = new SecretObject();
    if(file_exists($appConfigPath))
    {
        $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
    }
    
    // Database connection for the application
    $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
    try
    {
        $database->connect();

        $databaseType = $database->getDatabaseType();
        $schemaName = $databaseConfig->getDatabaseSchema();
        $databaseName = $databaseConfig->getDatabaseName();

        $tables = AppDatabase::getTableList($database, $databaseName, $schemaName, false, true);
        
        $primaryTables = array();
        $trashTables = array();
        $primaryTableColumns = array();
        $trashTableColumns = array();
        
        foreach($tables as $tableName=>$table)
        {            
            if(PicoStringUtil::endsWith($tableName, "_trash"))
            {
                $trashTables[] = $tableName;
                $trashTableColumns[$tableName] = AppDatabase::getColumnList($appConfig, $databaseConfig, $database, $tableName);
            }
            else
            {
                $primaryTables[] = $tableName;
                $primaryTableColumns[$tableName] = AppDatabase::getColumnList($appConfig, $databaseConfig, $database, $tableName);
            }
        }
        $validTrashTables = array();
        // Create list that tash table is in primary table list and column in trash table is in primary table
        foreach($trashTables as $trashTable)
        {
            $primaryTableName = substr($trashTable, 0, strlen($trashTable) - 6);
            if(!in_array($primaryTableName, $trashTables))
            {
                $primaryColumns = AppDatabase::getColumName($primaryTableColumns[$primaryTableName]);
                $trashColumns = AppDatabase::getColumName($trashTableColumns[$trashTable]);
                $validTrash = true;
                foreach($primaryColumns as $primaryColumn)
                {
                    if(!in_array($primaryColumn, $trashColumns))
                    {
                        $validTrash = false;
                        break;
                    }
                }
                if($validTrash)
                {
                    $validTrashTables[$primaryTableName] = $trashTable;
                }
            }
        }
        // Return JSON response with primary and trash tables
        ResponseUtil::sendJSON([
            "success" => true,
            "message" => "Valid trash tables retrieved successfully.",
            "pair" => $validTrashTables,
            "primaryTables" => array_keys($validTrashTables),
            "trashTables" => array_values($validTrashTables),
        ]);
        exit();
    
    }
    catch(Exception $e) {
        // Log the error for debugging purposes
        error_log("Error: " . $e->getMessage());
        ResponseUtil::sendJSON([
            "success" => false,
            "message" => "An error occurred while processing your request."
        ]);
        exit();
    }
}
catch(Exception $e)
{
    // Log the error for debugging purposes
    error_log("Error: " . $e->getMessage());
    ResponseUtil::sendJSON([
        "success" => false,
        "message" => "An error occurred while processing your request."
    ]);
    exit();
}  