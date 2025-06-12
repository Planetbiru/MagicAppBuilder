<?php

use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile(dirname(dirname(__DIR__)).'/jenglot.yml', true, true, true);

echo $config;