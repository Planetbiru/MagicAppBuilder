<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/sqlite-detector.php";
require_once dirname(__DIR__) . "/inc.app/auth-core.php";

if(!isset($entityAdmin) || $entityAdmin->getAdminLevelId() != "superuser")
{
    exit(); // Bye non superuser
}

$inputGet = new InputGet();
$inputPost = new InputPost();

$fromDefaultApp = false;
$applicationId = ""; 

$databaseConfig = $builderConfig->getDatabase();
$databaseConfig->setCharset('UTF8');
$database = new PicoDatabase($databaseConfig);
$schemaName = "";
try {
    // Connect to the database and set schema for PostgreSQL
    $database->connect();
    
    $dbType = $database->getDatabaseType();
    if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
        $schemaName = $databaseConfig->getDatabaseSchema();
        $database->query("SET search_path TO $schemaName;");
    }    
    if ($dbType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
        $databaseName = "";
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
require_once dirname(__DIR__) . "/database-explorer/database-explorer.php";
