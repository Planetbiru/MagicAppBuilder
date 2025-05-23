<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(dirname(__DIR__)) . "/inc.app/auth.php";

$inputGet = new InputGet();

// Get application ID, database name, schema name, table, page, and query from input
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$databaseName = $inputGet->getDatabase(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$schemaName = $inputGet->getSchema(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

// Determine application ID and check if it is from the default application
if (empty($applicationId)) {
    $appId = $activeApplication->getApplicationId();
    $applicationId = $appId;
    $fromDefaultApp = true;
} else {
    $appId = $applicationId;
    $fromDefaultApp = false;
}


$dbType = "mysql";
// Load the database configuration
$databaseConfig = new SecretObject();

$appConfigPath = $activeWorkspace->getDirectory() . "/applications/$appId/default.yml";

if (file_exists($appConfigPath)) {
    
    $appConfig->loadYamlFile($appConfigPath, false, true, true);
    $databaseConfig = $appConfig->getDatabase();
    if (!isset($databaseConfig)) {
        exit();
    }
    if (!empty($databaseName)) {
        $databaseConfig->setDatabaseName($databaseName);
    }
    if (empty($schemaName)) {
        $schemaName = $databaseConfig->getDatabaseSchema();
    }
    if(stripos($databaseConfig->getDriver(), 'sqlite') !== false)
    {
        if(!file_exists(dirname($databaseConfig->getDatabaseFilePath())))
        {
            // Create direcory
            mkdir(dirname($databaseConfig->getDatabaseFilePath()), 0755, true);
        }
    }
    $databaseConfig->setCharset('UTF8');
    $database = new PicoDatabase($databaseConfig);

    try {
        // Connect to the database and set schema for PostgreSQL
        $database->connect();
        
        $dbType = $database->getDatabaseType();
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            $database->query("SET search_path TO $schemaName;");
        }
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
            $databaseName = "";
        }
    } catch (Exception $e) {
        require_once __DIR__ . "/error.php";
        exit();
    }
} else {
    exit();
}