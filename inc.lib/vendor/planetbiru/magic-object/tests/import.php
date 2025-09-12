<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$fp = fopen(__DIR__.'/db.sql', 'w');
fclose($fp);
$tool = new PicoDatabaseUtilMySql();

$tableName = "transaction";
$limit = 200;
$offset = 400;

$sql = $tool->importData($config, function($sql, $tableNameSource, $tableNameTarget, $databaseSource, $databaseTarget){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
}, $tableName = null, $limit = null, $offset = null);
