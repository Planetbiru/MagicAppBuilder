<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/platform-check.php";
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
        // Set schema for PostgreSQL
        $database->query("SET search_path TO $schemaName;");
    }    
    if ($dbType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
        // SQLite does not have schema
        $databaseName = "";
    }
} catch (Exception $e) {
    // Database connection error
    require_once dirname(__DIR__) . "/inc.app/database-error.php";
    exit();
}
$accessedFrom = "magic-database";
require_once dirname(__DIR__) . "/database-explorer/backend.php";
require_once dirname(__DIR__) . "/database-explorer/database-explorer.php";
