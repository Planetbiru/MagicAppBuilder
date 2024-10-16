<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__)) . "/test.yml", false, true, true);
$databaseCredential->getDatabase()->setDatabaseName("sipro");
$database = new PicoDatabase($databaseCredential->getDatabase(), null, function($sql){
    echo $sql.";\r\n\r\n";
});
$database->connect();

