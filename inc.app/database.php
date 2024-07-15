<?php

use MagicObject\Database\PicoDatabase;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$databaseConfig = $appConfig->getDatabase();

$database = new PicoDatabase($databaseConfig);
try
{
    $database->connect();
}
catch(Exception $e)
{
    // do nothing
}