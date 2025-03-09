<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
$builderConfig = new SecretObject();
if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
$database = new PicoDatabase($builderConfig->getDatabase());
$database->connect();

$appConfig = new SecretObject();