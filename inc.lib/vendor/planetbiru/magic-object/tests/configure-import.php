<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

(new PicoDatabaseUtilMySql())->autoConfigureImportData($config);
file_put_contents('import.yml', $config->dumpYaml(0, 2));

