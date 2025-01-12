<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/sqlite-detector.php";
require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$fromDefaultApp = false;
$applicationId = ""; 

$databaseConfig = $builderConfig->getDatabase();
$databaseConfig->setCharset('UTF8');
$database = new PicoDatabase($databaseConfig);


try {
    // Connect to the database and set schema for PostgreSQL
    $database->connect();
    
    $dbType = $database->getDatabaseType();
    if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
        $database->query("SET search_path TO $schemaName;");
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
require_once __DIR__ . "/database-explorer.php";
