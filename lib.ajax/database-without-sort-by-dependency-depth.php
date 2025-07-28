<?php

use AppBuilder\AppDatabase;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

try {
    $databaseType = $database->getDatabaseType();
    $schemaName = $databaseConfig->getDatabaseSchema();
    $databaseName = $databaseConfig->getDatabaseName();

    $tables = AppDatabase::getTableList($database, $databaseName, $schemaName);

    // Send the JSON response
    ResponseUtil::sendJSON($tables);

} catch (Exception $e) {
    // Log the error for debugging purposes
    error_log("Error: " . $e->getMessage());
    ResponseUtil::sendJSON(["error" => "An error occurred while processing your request."]);
}