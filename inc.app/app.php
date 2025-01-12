<?php

use AppBuilder\AppSecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);

$cacheDir = dirname(__DIR__) . "/.cache/";
$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";

if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
