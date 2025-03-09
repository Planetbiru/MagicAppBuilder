<?php

use MagicObject\Database\PicoDatabase;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

try
{
    $database = new PicoDatabase($databaseConfig);
    $databaseName = $databaseConfig->getDatabaseName();
    $schemaName = $databaseConfig->getDatabaseSchema();
    $database->connect();
    $databaseType = $database->getDatabaseType();
}
catch(Exception $e)
{
    // do nothing
    error_log($e->getMessage());
}
